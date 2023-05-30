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

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/mediasite/basiclti_locallib.php');
require_once($CFG->dirroot.'/mod/mediasite/basiclti_mediasite_lib.php');
require_once("$CFG->dirroot/mod/mediasite/locallib.php");
require_once("$CFG->dirroot/mod/mediasite/mediasiteresource.php");
require_once("$CFG->dirroot/mod/mediasite/exceptions.php");

global $CFG, $DB;

$id       = optional_param('id', 0, PARAM_INT);
$a        = optional_param('a', 0, PARAM_INT);
$frameset = optional_param('frameset', '', PARAM_ALPHA);
$inpopup  = optional_param('inpopup', 0, PARAM_BOOL);
$coverplay = optional_param('coverplay', 0, PARAM_BOOL);
$resourceid = optional_param('resourceid', '', PARAM_ALPHANUMEXT);
$courseid = optional_param('course', 0, PARAM_INT);
$siteid   = optional_param('siteid', 0, PARAM_INT);

$endpoint = $coverplay ? mediasite_endpoint::LTI_COVERPLAY : mediasite_endpoint::LTI_LAUNCH;

if ($resourceid != '') {
    if ($courseid > 0) {
        $course = $DB->get_record("course", array("id" => $courseid));
        require_login($course);

        // Mark this activity as complete.
        $cm = $DB->get_record("course_modules", array("id" => $id));
        $completion = new completion_info($course);
        $completion->set_module_viewed($cm);

        mediasite_basiclti_mediasite_view($course, $siteid, $endpoint, $resourceid);
    } else {
        print_error(get_string('error_course_misconfigured', 'mediasite'));
    }
} else {
    if ($id > 0) {
        if (! ($cm = $DB->get_record("course_modules", array("id" => $id)))) {
            print_error(get_string('error_course_module_id_incorrect', 'mediasite'));
        }
    }

    if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
        print_error(get_string('error_course_misconfigured', 'mediasite'));
    }

    if (! ($mediasite = $DB->get_record("mediasite", array("id" => $cm->instance)))) {
        print_error(get_string('error_course_module_incorrect', 'mediasite'));
    } else {
        if (! ($course = $DB->get_record("course", array("id" => $mediasite->course)))) {
            print_error(get_string('error_course_misconfigured', 'mediasite'));
        }
        if (! ($cm = get_coursemodule_from_instance("mediasite", $mediasite->id, $course->id))) {
            print_error(get_string('error_course_module_id_incorrect', 'mediasite'));
        }
    }

    require_login($course);

    // Mark this activity as complete.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);

    mediasite_basiclti_mediasite_view($course, $mediasite->siteid, $endpoint,
        mediasite_guid_to_muid($mediasite->resourceid, $mediasite->resourcetype));
}