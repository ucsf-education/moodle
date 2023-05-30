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
 * @package assignsubmission_mediasite
 * @copyright Sonic Foundry 2017  {@link http://sonicfoundry.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


function assignsubmission_mediasite_get_mediasite_submission($submissionid)
{
    global $DB;
    return $DB->get_record('assignsubmission_mediasite', array('submission' => $submissionid));
}

function assignsubmission_mediasite_build_mymediasite_launch_url($assignid)
{
    $id = optional_param('id', 0, PARAM_INT);
    $update = optional_param('update', 0, PARAM_INT);
    if ($id == 0 && $update > 0) {
        $id = $update;
    }

    $isassignment = 1;
    $siteid = assignsubmission_mediasite_get_siteid_by_assignid($assignid);

    $mymediasitelaunchurl = new moodle_url('/mod/mediasite/upload_launch.php', array('id' => $id, 'isassignment' => $isassignment, 'siteid' => $siteid));
    return base64_encode($mymediasitelaunchurl);
}

function assignsubmission_mediasite_build_assignment_launch_url($assignid)
{
    global $COURSE;

    $id = optional_param('id', 0, PARAM_INT);
    $update = optional_param('update', 0, PARAM_INT);
    if ($id == 0 && $update > 0) {
        $id = $update;
    }

    $coursecontext = context_course::instance($COURSE->id);
    $coursecontextid = $coursecontext->id;
    $courseid = $COURSE->id;
    $siteid = assignsubmission_mediasite_get_siteid_by_assignid($assignid);

    $launchurl = new moodle_url(
        '/mod/mediasite/assignment_launch.php',
        array(
            'id' => $id,
            'coursecontext' => $coursecontextid,
            'course' => $courseid,
            'siteid' => $siteid,
            'resourceid' => "REPLACE_THIS_RESOURCEID"
        )
    );

    return base64_encode($launchurl);
}

function assignsubmission_mediasite_get_siteid_by_assignid($assignid)
{
    global $DB;

    $assign = $DB->get_record('assign', array('id' => $assignid));
    $mediasitesite = assignsubmission_mediasite_get_mediasite_site($assign->course);

    return $mediasitesite->id;
}

// This is the same function as in Atto button plugin "atto_mediasitebutton_get_mediasite_site". Move here to avoid installing Atto plugin.
function assignsubmission_mediasite_get_mediasite_site($courseid)
{
    global $DB;

    $courseconfig = $DB->get_record('mediasite_course_config', array('course' => $courseid), '*', $strictness = IGNORE_MISSING);
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
        return $DB->get_record('mediasite_sites', array('id' => $siteid), '*', $strictness = MUST_EXIST);
    }
}
