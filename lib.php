<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library functions for local_bulkcertdownload plugin
 *
 * @package    local_bulkcertdownload
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Get the count of available certificates, optionally filtered by company
 *
 * @param int $companyid Company ID to filter by (0 for all companies)
 * @return int Number of certificates found
 */
function local_bulkcertdownload_get_certificate_count($companyid = 0) {
    global $DB;
    
    $count = 0;
    
    // Build company user filter if needed
    $companyuserids = array();
    if (!empty($companyid)) {
        $companyuserids = local_bulkcertdownload_get_company_user_ids($companyid);
        if (empty($companyuserids)) {
            return 0; // No users in company
        }
    }
    
    // Check for mod_certificate
    if ($DB->get_manager()->table_exists('certificate_issues')) {
        if (!empty($companyuserids)) {
            list($insql, $params) = $DB->get_in_or_equal($companyuserids, SQL_PARAMS_NAMED);
            $count += $DB->count_records_select('certificate_issues', "userid $insql", $params);
        } else {
            $count += $DB->count_records('certificate_issues');
        }
    }
    
    // Check for mod_customcert
    if ($DB->get_manager()->table_exists('customcert_issues')) {
        if (!empty($companyuserids)) {
            list($insql, $params) = $DB->get_in_or_equal($companyuserids, SQL_PARAMS_NAMED);
            $count += $DB->count_records_select('customcert_issues', "userid $insql", $params);
        } else {
            $count += $DB->count_records('customcert_issues');
        }
    }
    
    return $count;
}

/**
 * Get available certificate modules
 *
 * @return array Array of available certificate module names
 */
function local_bulkcertdownload_get_available_modules() {
    global $DB;
    
    $modules = array();
    
    // Check for mod_certificate
    if ($DB->get_manager()->table_exists('certificate_issues')) {
        $modules[] = 'Certificate (mod_certificate)';
    }
    
    // Check for mod_customcert
    if ($DB->get_manager()->table_exists('customcert_issues')) {
        $modules[] = 'Custom Certificate (mod_customcert)';
    }
    
    return $modules;
}

/**
 * Get all certificate records from available modules, optionally filtered by company
 *
 * @param int $companyid Company ID to filter by (0 for all companies)
 * @return array Array of certificate records
 */
function local_bulkcertdownload_get_all_certificates($companyid = 0) {
    global $DB;
    
    $certificates = array();
    
    // Build company user filter if needed
    $companyuserids = array();
    $companyjoin = '';
    $companywhere = '';
    $companyparams = array();
    
    if (!empty($companyid)) {
        $companyuserids = local_bulkcertdownload_get_company_user_ids($companyid);
        if (empty($companyuserids)) {
            return array(); // No users in company
        }
        list($insql, $companyparams) = $DB->get_in_or_equal($companyuserids, SQL_PARAMS_NAMED);
        $companywhere = "AND u.id $insql";
    }
    
    // Get certificates from mod_certificate
    if ($DB->get_manager()->table_exists('certificate_issues')) {
        $sql = "SELECT ci.id, ci.userid, ci.certificateid, ci.code, ci.timecreated,
                       c.name as certificatename, u.firstname, u.lastname, u.email,
                       comp.name as companyname, comp.id as companyid,
                       'certificate' as moduletype
                FROM {certificate_issues} ci
                JOIN {certificate} c ON c.id = ci.certificateid
                JOIN {user} u ON u.id = ci.userid
                LEFT JOIN {company_users} cu ON cu.userid = u.id
                LEFT JOIN {company} comp ON comp.id = cu.companyid
                WHERE u.deleted = 0 $companywhere
                ORDER BY comp.name, u.lastname, u.firstname";
        
        $certrecords = $DB->get_records_sql($sql, $companyparams);
        $certificates = array_merge($certificates, $certrecords);
    }
    
    // Get certificates from mod_customcert
    if ($DB->get_manager()->table_exists('customcert_issues')) {
        $sql = "SELECT ci.id, ci.userid, ci.customcertid as certificateid, ci.code, ci.timecreated,
                       c.name as certificatename, u.firstname, u.lastname, u.email,
                       comp.name as companyname, comp.id as companyid,
                       'customcert' as moduletype
                FROM {customcert_issues} ci
                JOIN {customcert} c ON c.id = ci.customcertid
                JOIN {user} u ON u.id = ci.userid
                LEFT JOIN {company_users} cu ON cu.userid = u.id
                LEFT JOIN {company} comp ON comp.id = cu.companyid
                WHERE u.deleted = 0 $companywhere
                ORDER BY comp.name, u.lastname, u.firstname";
        
        $customcertrecords = $DB->get_records_sql($sql, $companyparams);
        $certificates = array_merge($certificates, $customcertrecords);
    }
    
    return $certificates;
}

/**
 * Add a certificate to the zip archive
 *
 * @param ZipArchive $zip The zip archive object
 * @param stdClass $cert Certificate record
 * @return bool True if certificate was added successfully
 */
function local_bulkcertdownload_add_certificate_to_zip($zip, $cert) {
    global $CFG;
    
    try {
        $filename = clean_filename($cert->lastname . '_' . $cert->firstname . '_' . 
                                 $cert->certificatename . '_' . $cert->code . '.pdf');
        
        // Generate certificate content based on module type
        if ($cert->moduletype == 'certificate') {
            $certificatecontent = local_bulkcertdownload_generate_certificate_pdf($cert);
        } else if ($cert->moduletype == 'customcert') {
            $certificatecontent = local_bulkcertdownload_generate_customcert_pdf($cert);
        } else {
            return false;
        }
        
        if ($certificatecontent !== false) {
            // Create folder structure in zip: ModuleType/UserName/
            $folder = $cert->moduletype . '/' . clean_filename($cert->lastname . '_' . $cert->firstname) . '/';
            $zip->addFromString($folder . $filename, $certificatecontent);
            return true;
        }
        
    } catch (Exception $e) {
        error_log('Error adding certificate to zip: ' . $e->getMessage());
    }
    
    return false;
}

/**
 * Generate PDF content for mod_certificate
 *
 * @param stdClass $cert Certificate record
 * @return string|false PDF content or false on failure
 */
function local_bulkcertdownload_generate_certificate_pdf($cert) {
    global $CFG, $DB;
    
    // This function would need to integrate with mod_certificate's PDF generation
    // For now, we'll create a basic implementation that checks if the certificate module exists
    
    if (!file_exists($CFG->dirroot . '/mod/certificate/lib.php')) {
        return false;
    }
    
    require_once($CFG->dirroot . '/mod/certificate/lib.php');
    
    try {
        // Get certificate instance
        $certificate = $DB->get_record('certificate', array('id' => $cert->certificateid));
        if (!$certificate) {
            return false;
        }
        
        // Get course module
        $cm = get_coursemodule_from_instance('certificate', $certificate->id);
        if (!$cm) {
            return false;
        }
        
        // Generate certificate - this would typically involve calling the certificate module's functions
        // Since each certificate module may have different methods, we provide a hook here
        if (function_exists('certificate_get_issue_pdf')) {
            return certificate_get_issue_pdf($certificate, $cert, $cm);
        }
        
        // Fallback: create a simple text-based certificate info
        return local_bulkcertdownload_create_fallback_certificate($cert);
        
    } catch (Exception $e) {
        error_log('Error generating certificate PDF: ' . $e->getMessage());
        return false;
    }
}

/**
 * Generate PDF content for mod_customcert
 *
 * @param stdClass $cert Certificate record
 * @return string|false PDF content or false on failure
 */
function local_bulkcertdownload_generate_customcert_pdf($cert) {
    global $CFG, $DB;
    
    if (!file_exists($CFG->dirroot . '/mod/customcert/lib.php')) {
        return false;
    }
    
    require_once($CFG->dirroot . '/mod/customcert/lib.php');
    
    try {
        // Get customcert instance
        $customcert = $DB->get_record('customcert', array('id' => $cert->certificateid));
        if (!$customcert) {
            return false;
        }
        
        // Get course module
        $cm = get_coursemodule_from_instance('customcert', $customcert->id);
        if (!$cm) {
            return false;
        }
        
        // Generate custom certificate - this would call customcert functions
        if (class_exists('\mod_customcert\certificate')) {
            $certificate = new \mod_customcert\certificate($cm);
            return $certificate->generate_pdf(false, $cert->userid);
        }
        
        // Fallback
        return local_bulkcertdownload_create_fallback_certificate($cert);
        
    } catch (Exception $e) {
        error_log('Error generating custom certificate PDF: ' . $e->getMessage());
        return false;
    }
}

/**
 * Create a fallback certificate when PDF generation is not available
 *
 * @param stdClass $cert Certificate record
 * @return string Simple text-based certificate content
 */
function local_bulkcertdownload_create_fallback_certificate($cert) {
    $content = "CERTIFICATE OF COMPLETION\n\n";
    $content .= "This is to certify that\n\n";
    $content .= strtoupper($cert->firstname . ' ' . $cert->lastname) . "\n\n";
    $content .= "has successfully completed\n\n";
    $content .= strtoupper($cert->certificatename) . "\n\n";
    $content .= "Certificate Code: " . $cert->code . "\n";
    $content .= "Date Issued: " . date('F j, Y', $cert->timecreated) . "\n";
    $content .= "Email: " . $cert->email . "\n";
    
    return $content;
}

/**
 * Get companies that the current user can access
 *
 * @return array Array of company objects
 */
function local_bulkcertdownload_get_user_companies() {
    global $DB, $USER;
    
    // Check if IOMAD company tables exist
    if (!$DB->get_manager()->table_exists('company')) {
        return array();
    }
    
    // If user is site admin, return all companies
    if (is_siteadmin()) {
        return $DB->get_records('company', null, 'name ASC');
    }
    
    // Get companies where user is a company manager
    $sql = "SELECT DISTINCT c.*
            FROM {company} c
            JOIN {company_users} cu ON cu.companyid = c.id
            WHERE cu.userid = :userid
            AND cu.managertype != 0
            ORDER BY c.name ASC";
    
    return $DB->get_records_sql($sql, array('userid' => $USER->id));
}

/**
 * Get company information by ID
 *
 * @param int $companyid Company ID
 * @return stdClass|false Company object or false if not found
 */
function local_bulkcertdownload_get_company($companyid) {
    global $DB;
    
    if (!$DB->get_manager()->table_exists('company')) {
        return false;
    }
    
    return $DB->get_record('company', array('id' => $companyid));
}

/**
 * Get count of users in a company
 *
 * @param int $companyid Company ID
 * @return int Number of users in the company
 */
function local_bulkcertdownload_get_company_user_count($companyid) {
    global $DB;
    
    if (!$DB->get_manager()->table_exists('company_users')) {
        return 0;
    }
    
    return $DB->count_records_sql(
        "SELECT COUNT(DISTINCT cu.userid)
         FROM {company_users} cu
         JOIN {user} u ON u.id = cu.userid
         WHERE cu.companyid = :companyid
         AND u.deleted = 0",
        array('companyid' => $companyid)
    );
}

/**
 * Get user IDs for a specific company
 *
 * @param int $companyid Company ID
 * @return array Array of user IDs
 */
function local_bulkcertdownload_get_company_user_ids($companyid) {
    global $DB;
    
    if (!$DB->get_manager()->table_exists('company_users')) {
        return array();
    }
    
    $sql = "SELECT DISTINCT cu.userid
            FROM {company_users} cu
            JOIN {user} u ON u.id = cu.userid
            WHERE cu.companyid = :companyid
            AND u.deleted = 0";
    
    $records = $DB->get_records_sql($sql, array('companyid' => $companyid));
    return array_keys($records);
}

/**
 * Clean filename for use in zip archive
 *
 * @param string $filename Original filename
 * @return string Cleaned filename
 */
function clean_filename($filename) {
    // Remove or replace characters that might cause issues in filenames
    $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);
    $filename = preg_replace('/_{2,}/', '_', $filename); // Replace multiple underscores with single
    return $filename;
}
