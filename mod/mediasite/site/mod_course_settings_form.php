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

namespace Sonicfoundry;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once("$CFG->dirroot/lib/formslib.php");
require_once("$CFG->dirroot/mod/mediasite/lib.php");
require_once("$CFG->dirroot/mod/mediasite/mediasitesite.php");

class mod_course_settings_form extends \moodleform {
    private $courseid = null;
    public function __construct($courseid) {
        global $PAGE;
        $this->courseid = $courseid;
        parent::__construct();
        $PAGE->requires->js(new \moodle_url('/mod/mediasite/js/course_settings.js'), true);
    }
    public function definition() {
        global $DB, $OUTPUT;

        $mform = $this->_form;
        $context = \context_course::instance($this->courseid);

        if (!(has_capability('mod/mediasite:overridedefaults', $context))) {
            return;
        }

        $courseconfig = $DB->get_record('mediasite_course_config', array('course' => $this->courseid));
        $sitelist = $DB->get_records('mediasite_sites');
        $defaults = $DB->get_record('mediasite_config', array());

        $mform->addElement('header', 'siteselectionheader', get_string('siteselectionheader', 'mediasite'));

        $json = '';
        $sitenames = array();

        if (!$courseconfig) {
            $currentsiteid = $defaults->siteid;
        } else {
            $currentsiteid = $courseconfig->mediasite_site;
        }

        $courses7locked = false;
        $assignmentsubmissionlocked = false;

        foreach ($sitelist as $site) {
            $sitenames[$site->id] = $site->sitename;
            if ($json != '') {
                $json = $json.', ';
            }
            $json = $json.'{"id":"'.$site->id.'", "name":"'.htmlspecialchars($site->sitename).'", "coursesTitle":"'.
            htmlspecialchars($site->integration_catalog_title).'", "showIntegrationCatalog":"'
                .$site->show_integration_catalog.'", "showAssignmentSubmission":"'.$site->show_assignment_submission.'"}';
            if ($site->id == $currentsiteid) {
                $courses7locked = $site->show_integration_catalog == 0 || $site->show_integration_catalog == 3;
                $assignmentsubmissionlocked = $site->show_assignment_submission == 0 || $site->show_assignment_submission == 3;
            }
        }
        $json = '{"sites": ['.$json.']}';

        $course7linktext = $DB->get_field('mediasite_sites', 'integration_catalog_title', array('id' => $currentsiteid));

        $sitedropdown = $mform->addElement(
            'select',
            'mediasite_site',
            get_string('sitenames', 'mediasite'),
            $sitenames,
            array('id' => 'id_siteid', 'onchange' => 'javascript:SiteChange(this, '.$json.');')
        );

        $courseoptions = null;
        if ($courses7locked) {
            $courseoptions = array('disabled' => 'disabled');
        }
        $assignmentsubmissionoptions = null;
        if ($assignmentsubmissionlocked) {
            $assignmentsubmissionoptions = array('disabled' => 'disabled');
        }

        $coursesmode = $mform->addElement(
            'advcheckbox',
            'mediasite_courses_enabled',
            get_string('mediasite_courses_enabled', 'mediasite'),
            $course7linktext,
            $courseoptions,
            array(0, 1)
        );

        $assignmentsubmissionmode = $mform->addElement(
            'advcheckbox',
            'assignment_submission_enabled',
            get_string('assignment_submission_enabled', 'mediasite'),
            '&nbsp;',
            $assignmentsubmissionoptions,
            array(0, 1)
        );

        if (!$courseconfig) {
            $sitedropdown->setSelected($defaults->siteid);
            $showcourses = $DB->get_field('mediasite_sites', 'show_integration_catalog', array('id' => $defaults->siteid));
            $mform->setDefault('mediasite_courses_enabled', ($showcourses > 1));
            $showassignment = $DB->get_field('mediasite_sites', 'show_assignment_submission', array('id' => $defaults->siteid));
            $mform->setDefault('assignment_submission_enabled', ($showassignment > 1));

        } else {
            $sitedropdown->setSelected($courseconfig->mediasite_site);
            $mform->setDefault('mediasite_courses_enabled', $courseconfig->mediasite_courses_enabled);
            $mform->setDefault('assignment_submission_enabled', $courseconfig->assignment_submission_enabled);
        }

        $mform->addElement('hidden', 'id', $this->courseid);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'mediasite_course_config_id', $courseconfig ? $courseconfig->id : '0');
        $mform->setType('mediasite_course_config_id', PARAM_INT);

        $this->add_action_buttons(true, get_string('savechangebutton', 'mediasite') );

    }
}
