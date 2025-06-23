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

// Require login and check capabilities
require_login();
$context = context_system::instance();
require_capability('local/bulkcertdownload:download', $context);

// Set up the page
$PAGE->set_url('/local/bulkcertdownload/index.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('downloadcertificates', 'local_bulkcertdownload'));
$PAGE->set_heading(get_string('downloadcertificates', 'local_bulkcertdownload'));
$PAGE->set_pagelayout('admin');

// Start output
echo $OUTPUT->header();

// Get certificate count
$certificatecount = local_bulkcertdownload_get_certificate_count();

if ($certificatecount == 0) {
    echo $OUTPUT->notification(get_string('certificatesnotfound', 'local_bulkcertdownload'), 'notifyerror');
} else {
    echo html_writer::tag('p', get_string('certificatesfound', 'local_bulkcertdownload', $certificatecount));
    
    // Create download form
    echo html_writer::start_tag('form', array('method' => 'post', 'action' => 'download.php'));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
    echo html_writer::empty_tag('input', array(
        'type' => 'submit',
        'value' => get_string('downloadcertificates', 'local_bulkcertdownload'),
        'class' => 'btn btn-primary'
    ));
    echo html_writer::end_tag('form');
    
    // Add some information about what will be downloaded
    echo html_writer::start_tag('div', array('class' => 'alert alert-info', 'style' => 'margin-top: 20px;'));
    echo html_writer::tag('h4', get_string('certificatearchive', 'local_bulkcertdownload'));
    echo html_writer::tag('p', 'This will create a ZIP archive containing all user certificates from the following modules:');
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
