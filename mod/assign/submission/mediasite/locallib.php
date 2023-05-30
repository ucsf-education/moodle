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

global $CFG, $PAGE;
require_once("$CFG->dirroot/mod/assign/submission/mediasite/lib.php");
$PAGE->requires->js(new moodle_url('/mod/assign/submission/mediasite/js/mediasitesubmission.js'), true);


class assign_submission_mediasite extends assign_submission_plugin
{

    public function get_name()
    {
        return get_string('submissiontype', 'assignsubmission_mediasite');
    }

    public function get_settings(MoodleQuickForm $mform)
    {
    }

    public function save_settings(stdClass $data)
    {
        // Happens on Add/Edit Moodle Assignment
        return true;
    }

    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data)
    {
        $mform->addElement(
            'static',
            'display_submission_content',
            get_string('submissiontext', 'assignsubmission_mediasite')
        );

        $mform->addElement(
            'textarea',
            'submission_content',
            //get_string('submissiontext', 'assignsubmission_mediasite'),
            '',
            array(
                'rows' => 5,
                'cols' => 100,
                'style' => 'display:none;'
            )
        );
        $mform->setType('submission_content', PARAM_RAW);

        $mymediasitelaunchurl = assignsubmission_mediasite_build_mymediasite_launch_url($submission->assignment);
        $assignmentlaunchurl = assignsubmission_mediasite_build_assignment_launch_url($submission->assignment);

        // Add [Upload Mediasite] button
        $mform->addElement(
            'button',
            'btnAssignLtiLaunch',
            get_string('launchbuttontext', 'assignsubmission_mediasite'),
            array(
                'onclick' => "launchMyMediasite('" . $mymediasitelaunchurl . "','" . $assignmentlaunchurl . "')",
            )
        );

        // Set Mediasite Submission Content
        $mediasitesubmission = assignsubmission_mediasite_get_mediasite_submission($submission->id);
        if ($mediasitesubmission) {
            // Set value content
            $assigncontent = $mediasitesubmission->mediasitecontent;
            $data->submission_content = $assigncontent;

            // Set display content
            if (empty(trim($assigncontent))) {
                $data->display_submission_content = '';
            } else {
                $data->display_submission_content = $mediasitesubmission->mediasitecontent;
            }
        }

        return true;
    }

    public function save(stdClass $submission, stdClass $data)
    {
        global $DB;

        $assignmentid = $submission->assignment;
        $submissionid = $submission->id;
        $content = $data->submission_content;

        if(empty($content)) {
            $this->set_error(get_string('emptycontenterrormessage', 'assignsubmission_mediasite'));
            return false;
        }

        $existingmediasitesubmission = assignsubmission_mediasite_get_mediasite_submission($submissionid);
        if ($existingmediasitesubmission) {
            $existingmediasitesubmission->mediasitecontent = $content;
            return $DB->update_record('assignsubmission_mediasite', $existingmediasitesubmission);
        } else {
            $newmediasitesubmission = new stdClass();
            $newmediasitesubmission->assignment = $assignmentid;
            $newmediasitesubmission->submission = $submissionid;
            $newmediasitesubmission->mediasitecontent = $content;
            return $DB->insert_record('assignsubmission_mediasite', $newmediasitesubmission) > 0;
        }
    }

    public function view_summary(stdClass $submission, &$showviewlink)
    {
        global $PAGE;

        if ($PAGE->pagetype == "mod-assign-view") {
            // Student preview view
            $summary = $this->view($submission);
        } elseif ($PAGE->pagetype == "mod-assign-grading") {
            // Admin/Instructor preview view
            $summary = "View details";
        }

        $showviewlink = true;
        return $summary;
    }

    public function view(stdClass $submission)
    {
        $mediasitesubmission = assignsubmission_mediasite_get_mediasite_submission($submission->id);
        if ($mediasitesubmission) {
            return $mediasitesubmission->mediasitecontent;
        }

        return "No Submission yet.";
    }

    public function can_upgrade($type, $version)
    {
        return false;
    }

    public function upgrade_settings(context $oldcontext, stdClass $oldassignment, &$log)
    {
        return true;
    }

    public function upgrade($oldcontext, $oldassignment, $oldsubmission, $submission, &$log)
    {
        return true;
    }

    public function get_editor_fields()
    {
        return array('assignsubmissionmediasite' => get_string('pluginname', 'assignsubmission_mediasite'));
    }

    public function get_editor_text($name, $submissionid)
    {
        if ($name == 'assignsubmissionmediasite') {
            $assignmediasite = assignsubmission_mediasite_get_mediasite_submission($submissionid->id);
            if ($assignmediasite) {
                return $assignmediasite->mediasitecontent;
            }
        }

        return '';
    }

    public function get_editor_format($name, $submissionid)
    {
        return 1;
    }

    // This function is called everywhere, important!
    public function is_empty(stdClass $submission)
    {
        $assignmediasite = assignsubmission_mediasite_get_mediasite_submission($submission->id);
        if ($assignmediasite) {
            return false;
        }

        return true;
    }

    public function copy_submission(stdClass $sourcesubmission, stdClass $destsubmission)
    {
        global $DB;

        $assignmediasite = assignsubmission_mediasite_get_mediasite_submission($sourcesubmission->id);
        if ($assignmediasite) {
            unset($assignmediasite->id);
            $assignmediasite->submission = $destsubmission->id;
            $DB->insert_record('assignsubmission_mediasite', $assignmediasite);
        }
        return true;
    }

    public function format_for_log(stdClass $submission)
    {
        // Format the info for each submission plugin (will be logged).
        $assignmediasite = assignsubmission_mediasite_get_mediasite_submission($submission->id);
        $loginfo = '';
        $loginfo .= get_string(
            'numwordsforlog',
            'assignsubmission_mediasite',
            count_words($assignmediasite->mediasitecontent)
        );

        return $loginfo;
    }

    public function delete_instance()
    {
        global $DB;
        // will throw exception on failure                                                                                          
        $DB->delete_records('assignsubmission_mediasite', array('assignment' => $this->assignment->get_instance()->id));
        return true;
    }

    // No idea why this function is not appeared in official article: https://docs.moodle.org/dev/Assign_submission_plugins
    public function remove(stdClass $submission)
    {
        global $DB;

        $submissionid = $submission ? $submission->id : 0;
        if ($submissionid) {
            $DB->delete_records('assignsubmission_mediasite', array('submission' => $submissionid));
        }
        return true;
    }
}
