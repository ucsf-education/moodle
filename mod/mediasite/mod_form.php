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

require_once("$CFG->dirroot/course/moodleform_mod.php");
require_once("$CFG->dirroot/mod/mediasite/locallib.php");
require_once("$CFG->dirroot/mod/mediasite/mediasitesite.php");
require_once("$CFG->dirroot/mod/mediasite/exceptions.php");

class mod_mediasite_mod_form extends moodleform_mod {
    public function __construct($data, $section, $cm, $course) {
        global $CFG, $DB, $PAGE;

        parent::__construct($data, $section, $cm, $course);

        $sites = $DB->get_records('mediasite_sites');
        $config = $DB->get_record('mediasite_config', array());
        if (!$config || is_null($config) ||
           !$sites  || is_null($sites) || count($sites) < 1) {
            // Go home.
            print_error(get_string('incompleteconfiguration', 'mediasite'));
            redirect($CFG->wwwroot);
        }
        $PAGE->requires->js(new moodle_url('/mod/mediasite/js/basiclti_callback.js'), true);
    }

    public function definition() {
        global $CFG, $COURSE, $DB;

        $mform = $this->_form;
        $cm = $this->_cm;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('hidden', 'gate');
        $mform->setType('gate', PARAM_INT);
        $mform->setDefault('gate', 0);

        if (!is_object($cm) || !isset($cm->id) || !($cm->id > 0)) {
            if (count($this->mediasite_get_lti_sites(false)) > 0) {
                $ltiurl = "$CFG->wwwroot/mod/mediasite/lti_site_selection.php?course=".
                    strval($COURSE->id)."&cm=".strval($this->current->instance);
                $mform->addElement('html', '<div id="mediasite_lti_content"><iframe id="mediasite_lti_content_iframe" src="'.
                    $ltiurl.'"></iframe></div>');

            } else {
                throw new moodle_exception(
                    'generalexceptionmessage',
                    'error',
                    '',
                    'Plugin configuration is incomplete. '.
                    'Please contact the administrator and request sites be added to the Mediasite Activity Plugin.'
                );
            }
        }

        if (isset($cm->id)) {
            $mediasite = $DB->get_record("mediasite", array("id" => $cm->instance));
            $site = new Sonicfoundry\MediasiteSite($DB->get_record("mediasite_sites", array("id" => $mediasite->siteid)));
            $supportedembedtypes = $site->get_embed_capabilities(false, $mediasite->resourcetype);
            $formoptions = array();
            foreach ($supportedembedtypes as $s) {
                $formoptions[$s->formattype] = get_string($s->formattype, 'mediasite');
            }
            $mform->addElement(
                'select',
                'displaymode',
                get_string('mode', 'mediasite'),
                $formoptions,
                array('onchange' => 'javascript:toggleEmbedModeChange(this.value);')
            );
            $mform->setType('displaymode', PARAM_TEXT);
            $mform->setDefault('displaymode', $mediasite->displaymode);
            $mform->addRule('displaymode', null, 'required', null, 'server');

            $tags = str_replace('~!~', ', ', $mediasite->sofotags);
            $presenters = str_replace('~!~', '\n\n', $mediasite->presenters);

            $mform->addElement('html', '<script type="text/javascript">setTimeout(function() { toggleEmbedModeChange("'.
                $mediasite->displaymode.'") }, 50);</script>');

        } else {
            $mform->addElement('hidden', 'displaymode', '', array('id' => 'id_displaymode'));
            $mform->setType('displaymode', PARAM_TEXT);
        }

        $mform->addElement(
            'text',
            'name',
            get_string('resourcetitle', 'mediasite'),
            array(
                'size' => '97',
                'class' => 'sofo-embed sofo-embed-type-PresentationLink sofo-embed-type-PlayerOnly sofo-embed-type-MetadataLight '.
                'sofo-embed-type-MetadataOnly sofo-embed-type-MetadataPlusPlayer sofo-embed-type-BasicLTI sofo-embed-type-iFrame'
            )
        );
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'server');

        $mform->addElement(
            'textarea',
            'description',
            get_string('description', 'mediasite'),
            array('wrap' => "virtual",
                'rows' => 20,
                'cols' => 100,
                'class' => 'sofo-embed sofo-embed-type-MetadataOnly sofo-embed-type-MetadataPlusPlayer sofo-embed-type-iFrame'
            )
        );
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement(
            'textarea',
            'presenters_display',
            get_string('presenters', 'mediasite'),
            array('wrap' => "virtual",
                'rows' => 15,
                'cols' => 100,
                'class' => 'sofo-embed mediasite-readonly sofo-embed-type-MetadataOnly sofo-embed-type-MetadataPlusPlayer',
                'onfocus' => 'this.blur();'
            )
        );
        $mform->setType('presenters_display', PARAM_TEXT);

        $mform->addElement(
            'textarea',
            'sofotags_display',
            get_string('sofotags', 'mediasite'),
            array(
                'class' => 'sofo-embed mediasite-readonly sofo-embed-type-MetadataOnly sofo-embed-type-MetadataPlusPlayer',
                'wrap' => "virtual",
                'rows' => 3,
                'cols' => 100,
                'onfocus' => 'this.blur();'
            )
        );
        $mform->setType('sofotags_display', PARAM_TEXT);

        $mform->addElement('hidden', 'showdescription');
        $mform->setType('showdescription', PARAM_INT);
        $mform->setDefault('showdescription', 1);

        $context = context_course::instance($COURSE->id);

        if (has_capability('mod/mediasite:overridedefaults', $context)) {
            $mform->addElement('advcheckbox', 'openaspopup', get_string('openaspopup', 'mediasite'), null);
            $mform->setDefault('openaspopup', 1);
        } else {
            $mform->addElement('hidden', 'openaspopup');
            $mform->setType('openaspopup', PARAM_INT);
        }

        $config = $DB->get_record('mediasite_config', array());
        if ($config) {
            $mform->setDefault('openaspopup', $config->openaspopup);
        }

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();

        $mform->closeHeaderBefore('siteid');

        $mform->addElement('hidden', 'siteid', -1, array('id' => 'id_siteid'));
        $mform->setType('siteid', PARAM_INT);

        $mform->addElement('hidden', 'resourcetype', '', array('id' => 'id_resourcetype'));
        $mform->setType('resourcetype', PARAM_TEXT);

        $mform->addElement('hidden', 'resourceid', '', array('id' => 'id_resourceid'));
        $mform->setType('resourceid', PARAM_TEXT);

        $mform->addElement('hidden', 'recorddateutc', '', array('id' => 'id_recorddateutc'));
        $mform->setType('recorddateutc', PARAM_TEXT);

        $mform->addElement('hidden', 'presenters', '', array('id' => 'id_presenters'));
        $mform->setType('presenters', PARAM_TEXT);

        $mform->addElement('hidden', 'sofotags', '', array('id' => 'id_sofotags'));
        $mform->setType('sofotags', PARAM_TEXT);

        $mform->addElement('hidden', 'launchurl', '', array('id' => 'id_launchurl'));
        $mform->setType('launchurl', PARAM_TEXT);

        if (method_exists($mform, 'setExpanded')) {
            $mform->setExpanded('modstandardelshdr', false);
        }

    }

    public function mediasite_get_editor_options($context) {

    }

    public function definition_after_data() {
        global $DB;

        parent::definition_after_data();

        $mform = $this->_form;
        $cm = $this->_cm;
        if (isset($cm->id)) {
            $mediasite = $DB->get_record("mediasite", array("id" => $cm->instance));

            $tags = str_replace('~!~', ', ', $mediasite->sofotags);
            $presenters = str_replace('~!~', "\r\n\r\n", $mediasite->presenters);

            $mform->setDefault('sofotags_display', $tags);
            $mform->setDefault('presenters_display', $presenters);
        }
    }

    public function validation($data, $files) {
        global $USER;

        $errors = parent::validation($data, $files);

        if (!(mediasite_has_value($data['resourceid']) &&
            mediasite_has_value($data['siteid']) &&
            mediasite_has_value($data['resourcetype']) &&
            mediasite_has_value($data['displaymode']))) {
            // Bad, blow up.
            $errors['name'] = get_string('form_data_invalid', 'mediasite');
        }
        try {
            // Validate the current user has access to the selected resource.
            $valid = mediasite_check_resource_permission($data['resourceid'], $data['resourcetype'], $USER->username);
            if (!$valid) {
                $errors['resourceid'] = get_string('notauthorized', 'mediasite');
            }
        } catch (\Sonicfoundry\SonicfoundryException $se) {
            $errors['resourceid'] = $se->getMessage();
        } catch (Exception $e) {
            $errors['resourceid'] = $e->getMessage();
        }

        return $errors;
    }

    public function mediasite_get_lti_sites($onlyshowintegrationcatalogenabled = false) {
        global $DB;
        if ($onlyshowintegrationcatalogenabled) {
            $records = $DB->get_records('mediasite_sites', array('show_integration_catalog' => true));
        } else {
            $records = $DB->get_records('mediasite_sites');
        }
        return $records;
    }
}


