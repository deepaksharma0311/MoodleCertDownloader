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
 * Certificate download handler
 *
 * @package    local_bulkcertdownload
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');

// Check if IOMAD is installed
if (!file_exists($CFG->dirroot . '/local/iomad/lib/iomad.php')) {
    print_error('noiomad', 'local_bulkcertdownload');
}

require_once($CFG->dirroot . '/local/iomad/lib/iomad.php');

// Require login and check capabilities
require_login();
$context = context_system::instance();

// Get company ID from form
$companyid = optional_param('companyid', 0, PARAM_INT);

// Check capabilities based on company selection
if (!empty($companyid)) {
    require_capability('local/bulkcertdownload:downloadcompany', $context);
} else {
    require_capability('local/bulkcertdownload:download', $context);
}

// Check sesskey
require_sesskey();

// Set time limit for large downloads
set_time_limit(0);

try {
    // Create temporary directory for the zip file
    $tempdir = make_temp_directory('bulkcertdownload');
    $zipfilename = 'certificates_' . date('Y-m-d_H-i-s') . '.zip';
    $zippath = $tempdir . '/' . $zipfilename;
    
    // Create zip archive
    $zip = new ZipArchive();
    if ($zip->open($zippath, ZipArchive::CREATE) !== TRUE) {
        throw new moodle_exception('errorzipfailed', 'local_bulkcertdownload');
    }
    
    // Get all certificate files and add them to zip (filtered by company)
    $certificates = local_bulkcertdownload_get_all_certificates($companyid);
    $addedcount = 0;
    
    foreach ($certificates as $cert) {
        if (local_bulkcertdownload_add_certificate_to_zip($zip, $cert)) {
            $addedcount++;
        }
    }
    
    // Close the zip file
    $zip->close();
    
    if ($addedcount == 0) {
        // Clean up and show error if no certificates were added
        unlink($zippath);
        redirect(new moodle_url('/local/bulkcertdownload/index.php'), 
                get_string('certificatesnotfound', 'local_bulkcertdownload'), 
                null, \core\output\notification::NOTIFY_ERROR);
    }
    
    // Send the file to the browser
    $filesize = filesize($zippath);
    
    // Set headers for download
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipfilename . '"');
    header('Content-Length: ' . $filesize);
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');
    
    // Output the file
    readfile($zippath);
    
    // Clean up temporary file
    unlink($zippath);
    
    exit;
    
} catch (Exception $e) {
    // Handle errors
    error_log('Bulk certificate download error: ' . $e->getMessage());
    redirect(new moodle_url('/local/bulkcertdownload/index.php'), 
            get_string('errorzipfailed', 'local_bulkcertdownload'), 
            null, \core\output\notification::NOTIFY_ERROR);
}
