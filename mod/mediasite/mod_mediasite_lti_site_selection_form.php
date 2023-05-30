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

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/lib/formslib.php");
require_once("$CFG->dirroot/mod/mediasite/locallib.php");

/**
 * Class mod_mediasite_lti_site_selection_form
 * Extends the moodleform to select the Mediasite site to use
 * for searching
 */
class mod_mediasite_lti_site_selection_form extends moodleform {
    private $cid;
    public function __construct($cid) {
        $this->cid = $cid;
        parent::__construct(null, null, 'post', '', null, true);
    }

    public function definition() {
        global $DB;

        $mform =& $this->_form;
        $context = context_course::instance($this->cid);

        $mform->addElement('header', 'siteselectionheader', get_string('siteselectionheader', 'mediasite'));
        $records = $DB->get_records('mediasite_sites');
        $defaults = $DB->get_record('mediasite_config', array());

        if (count($records) == 1) {
            foreach ($records as $record) {
                $this->launchredirect($this->cid, $record->id);
            }
        }

        if (count($records) > 1 && has_capability('mod/mediasite:overridedefaults', $context)) {
            $sitenames = array();
            foreach ($records as $record) {
                $sitenames[$record->id] = $record->sitename;
            }
            $selectdropdown = $mform->addElement(
                'select',
                'siteid',
                get_string('sitenames', 'mediasite'),
                $sitenames,
                array('id' => 'id_siteid')
            );
            $selectdropdown->setSelected($defaults->siteid);
        } else {
            $mform->addElement('hidden', 'siteid', $defaults->siteid, array('id' => 'id_siteid'));
            $mform->setType('siteid', PARAM_INT);
            $mform->setDefault('siteid', $defaults->siteid);
        }

        $mform->closeHeaderBefore('submitbutton');

        $mform->addElement('submit', 'launchsite', get_string('launchsite', 'mediasite'));

        $mform->addElement('hidden', 'course', $this->cid);
        $mform->setType('course', PARAM_INT);

        $mform->disable_form_change_checker();

    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }

    public function launchredirect($courseid, $siteid) {
        redirect("basiclti_launch.php?course=".$courseid."&siteid=".$siteid);
    }

}
