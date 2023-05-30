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
 * Mediasite plugin for Moodle.
 *
 * @package mod_mediasite
 * @copyright Sonic Foundry 2017  {@link http://sonicfoundry.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once("$CFG->dirroot/mod/mediasite/site/mod_course_settings_form.php");

global $OUTPUT, $CFG, $PAGE, $DB;

$id = required_param('id', PARAM_INT);
$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

$context = context_course::instance($id);
$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot . '/mod/mediasite/site/course_settings.php?id='.$id);

require_login($course, true);

require_capability('mod/mediasite:overridedefaults', $context);

$PAGE->set_pagelayout('incourse');
$pagetitle = strip_tags($course->shortname.': '.get_string('course_settings', 'mediasite'));
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);


$mform  = new Sonicfoundry\mod_course_settings_form(strval($course->id));

if ($mform->get_data()) {
    $data = $mform->get_data();
    $record = new stdClass();
    $isupdate = $data->mediasite_course_config_id != '0';
    if ($isupdate) {
        $record->id = $data->mediasite_course_config_id;
    }
    $record->course = $data->id;
    $record->mediasite_site = $data->mediasite_site;
    $siteshowintegrationcatalog = $DB->get_field(
        'mediasite_sites',
        'show_integration_catalog',
        array('id' => $data->mediasite_site)
    );
    $record->mediasite_courses_enabled = mediasite_resolve_site_course_setting($siteshowintegrationcatalog,
        $data->mediasite_courses_enabled);

    $siteshowassignmentsubmission = $DB->get_field(
        'mediasite_sites',
        'show_assignment_submission',
        array('id' => $data->mediasite_site)
    );
    $record->assignment_submission_enabled = mediasite_resolve_site_course_setting($siteshowassignmentsubmission,
        $data->assignment_submission_enabled);

    if ($isupdate) {
        $DB->update_record('mediasite_course_config', $record);
    } else {
        $DB->insert_record('mediasite_course_config', $record);
    }
    redirect(new \moodle_url('/course/view.php', array('id' => $id)));
}

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();

function mediasite_resolve_site_course_setting($sitesetting, $coursesetting) {
    if ($sitesetting == 3) {
        return 1;
    } else if ($sitesetting == 0) {
        return 0;
    } else {
        return $coursesetting;
    }
}