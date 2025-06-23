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
 * Main page for bulk certificate download
 *
 * @package    local_bulkcertdownload
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('lib.php');

// Check if IOMAD is installed
if (!file_exists($CFG->dirroot . '/local/iomad/lib/iomad.php')) {
    print_error('noiomad', 'local_bulkcertdownload');
}

require_once($CFG->dirroot . '/local/iomad/lib/iomad.php');

// Require login and check capabilities
require_login();
$context = context_system::instance();

// Check if user has company management capabilities
$companyid = optional_param('companyid', 0, PARAM_INT);
if (!empty($companyid)) {
    require_capability('local/bulkcertdownload:downloadcompany', $context);
} else {
    require_capability('local/bulkcertdownload:download', $context);
}

// Set up the page
$PAGE->set_url('/local/bulkcertdownload/index.php', array('companyid' => $companyid));
$PAGE->set_context($context);
$PAGE->set_title(get_string('downloadcompanycertificates', 'local_bulkcertdownload'));
$PAGE->set_heading(get_string('downloadcompanycertificates', 'local_bulkcertdownload'));
$PAGE->set_pagelayout('admin');

// Start output
echo $OUTPUT->header();

// Get companies that user can access
$companies = local_bulkcertdownload_get_user_companies();

if (empty($companies)) {
    echo $OUTPUT->notification(get_string('nocompanies', 'local_bulkcertdownload'), 'notifyerror');
    echo $OUTPUT->footer();
    exit;
}

// Company selection form
echo html_writer::start_tag('div', array('class' => 'alert alert-info'));
echo html_writer::tag('h4', get_string('filteroptions', 'local_bulkcertdownload'));

echo html_writer::start_tag('form', array('method' => 'get', 'action' => 'index.php', 'class' => 'form-inline'));
echo html_writer::tag('label', get_string('selectcompany', 'local_bulkcertdownload') . ': ', array('for' => 'companyid', 'class' => 'mr-2'));

$companyoptions = array(0 => get_string('allcompanies', 'local_bulkcertdownload'));
foreach ($companies as $company) {
    $companyoptions[$company->id] = $company->name;
}

echo html_writer::select($companyoptions, 'companyid', $companyid, false, array('class' => 'form-control mr-2', 'onchange' => 'this.form.submit()'));
echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => 'Filter', 'class' => 'btn btn-secondary'));
echo html_writer::end_tag('form');
echo html_writer::end_tag('div');

// Show selected company info
if (!empty($companyid)) {
    $selectedcompany = local_bulkcertdownload_get_company($companyid);
    if ($selectedcompany) {
        echo html_writer::tag('h3', get_string('companyname', 'local_bulkcertdownload', $selectedcompany->name));
        $usercount = local_bulkcertdownload_get_company_user_count($companyid);
        echo html_writer::tag('p', get_string('companyusers', 'local_bulkcertdownload', $usercount));
    }
}

// Get certificate count for selected company
$certificatecount = local_bulkcertdownload_get_certificate_count($companyid);

if ($certificatecount == 0) {
    echo $OUTPUT->notification(get_string('certificatesnotfound', 'local_bulkcertdownload'), 'notifyerror');
} else {
    echo html_writer::tag('p', get_string('certificatesfound', 'local_bulkcertdownload', $certificatecount));
    
    // Create download form
    echo html_writer::start_tag('form', array('method' => 'post', 'action' => 'download.php'));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'companyid', 'value' => $companyid));
    echo html_writer::empty_tag('input', array(
        'type' => 'submit',
        'value' => get_string('downloadcompanycertificates', 'local_bulkcertdownload'),
        'class' => 'btn btn-primary btn-lg'
    ));
    echo html_writer::end_tag('form');
    
    // Add information about what will be downloaded
    echo html_writer::start_tag('div', array('class' => 'alert alert-info', 'style' => 'margin-top: 20px;'));
    echo html_writer::tag('h4', get_string('certificatearchive', 'local_bulkcertdownload'));
    echo html_writer::tag('p', 'This will create a ZIP archive containing certificates for users from the selected company:');
    echo html_writer::start_tag('ul');
    
    // Check which certificate modules are available
    $modules = local_bulkcertdownload_get_available_modules();
    foreach ($modules as $module) {
        echo html_writer::tag('li', $module);
    }
    
    if (empty($modules)) {
        echo html_writer::tag('li', get_string('nomodcertfound', 'local_bulkcertdownload'), array('class' => 'text-danger'));
    }
    
    echo html_writer::end_tag('ul');
    echo html_writer::end_tag('div');
}

echo $OUTPUT->footer();
