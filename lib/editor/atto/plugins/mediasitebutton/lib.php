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
 * Library for atto_mediasitebutton
 *
 * @package atto
 * @subpackage   atto_mediasitebutton
 * @copyright Sonic Foundry 2017  {@link http://sonicfoundry.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Initialize the Mediasite plugin
 */
function atto_mediasitebutton_strings_for_js() {
    global $PAGE;
    $PAGE->requires->strings_for_js(
        array('mediasite', 'mediasitebutton', 'atto_mediasitebutton'
        , 'dialogtitle', 'insert', 'grade_presentation', 'record_date'
        , 'presenter', 'presenters', 'tag', 'tags', 'upload_date'
        , 'submission_instructions', 'continue', 'editor_instructions', 'editor_insert'), 'atto_mediasitebutton');
}

/**
 * Initialize the javascript parameters required by atto
 *
 * @return array of additional params to pass to javascript init funciton for this module
 */
function atto_mediasitebutton_params_for_js() {
    global $COURSE;

    $id = optional_param('id', 0, PARAM_INT);
    $update = optional_param('update', 0, PARAM_INT);

    if ($id == 0 && $update > 0) {
        $id = $update;
    }

    $coursecontext = context_course::instance($COURSE->id);

    // $isassignment = atto_mediasitebutton_is_assignment();
    $isassignment = false;
    $mediasitesite = atto_mediasitebutton_get_mediasite_site($COURSE->id);
    
    $enabled = has_capability('atto/mediasitebutton:editor', $coursecontext);
    
    $launchurl = base64_encode(new moodle_url('/mod/mediasite/upload_launch.php', array('id' => $id, 'isassignment' => $isassignment, 'siteid' => $mediasitesite->id)));
    $assignmentlaunchurl = base64_encode(new moodle_url('/mod/mediasite/assignment_launch.php', array('id' => $id, 'coursecontext' => $coursecontext->id, 'course' => $COURSE->id, 'siteid' => $mediasitesite->id, 'resourceid' => '##ID##')));
    $presentationlaunchurl = base64_encode(new moodle_url('/mod/mediasite/content_launch.php', array('siteid' => $mediasitesite->id, 'course' => $COURSE->id, 'coverplay' => '1', 'resourceid' => '##ID##')));
    
    $embedlaunchurl = $isassignment ? $assignmentlaunchurl : $presentationlaunchurl;

    return array('launch_url' => $launchurl, 
                 'site_id' => $mediasitesite->id, 
                 'course_id' => $COURSE->id, 
                 'toolconsumerkey' => $mediasitesite->lti_consumer_key,
                 'newpage' => '',
                 'extcontentreturnurl' => '',
                 'assignmentlaunchurl' => $embedlaunchurl,
                 'enabled' => $enabled,
                 'isassignment' => $isassignment);
}

/**
 * Get the Mediasite server for the course
 *
 * @param int $courseid
 */
function atto_mediasitebutton_get_mediasite_site($courseid) {
    global $DB;

    $courseconfig = $DB->get_record('mediasite_course_config', array('course' => $courseid), '*', $strictness=IGNORE_MISSING);
    $siteid = null;
    if (!$courseconfig) {
        // Need to find the default site instead.
        $siteconfig = $DB->get_record_sql('SELECT * FROM {mediasite_config} WHERE id = (SELECT MAX(id) FROM {mediasite_config})');
        if (!$siteconfig) {
            // Throw an error because there isn't a default site configured.
            throw new moodle_exception('generalexceptionmessage', 'error', '', 'atto_mediasitebutton_get_mediasite_site was unable to determine which Mediasite to upload to.');
        }
        $siteid = $siteconfig->siteid;
    } else {
        $siteid = $courseconfig->mediasite_site;
    }
    if ($siteid == null) {
        throw new moodle_exception('generalexceptionmessage', 'error', '', 'atto_mediasitebutton_get_mediasite_site was unable to determine which Mediasite to upload to.');
    } else {
        return $DB->get_record('mediasite_sites', array('id' => $siteid), '*', $strictness=MUST_EXIST);
    }
}

/**
 * Determine if the current page is an assignment
 */
function atto_mediasitebutton_is_assignment() {
    global $COURSE, $DB, $PAGE;

    $assignmentview = false;
    if($PAGE->url->compare(new moodle_url('/mod/assign/view.php'), URL_MATCH_BASE)) {
        $assignmentview = true;
    }
    return $assignmentview;
}

/**
 * Determine if the current course has assignment submission enabled
 */
function atto_mediasitebutton_is_assignment_submission_enabled($courseid, $mediasitesite) {
    global $DB;
    if ($mediasitesite->show_assignment_submission == 0) {
        return false;
    } else if ($mediasitesite->show_assignment_submission == 3) {
        return true;
    } else if ($DB->record_exists('mediasite_course_config', array('course' => $courseid))) {
        $assignmentsubmissionenabled = $DB->get_field(
            'mediasite_course_config',
            'assignment_submission_enabled',
            array('course' => $courseid)
        );
        return $assignmentsubmissionenabled;
    }

    return ($mediasitesite->show_assignment_submission > 1);
}