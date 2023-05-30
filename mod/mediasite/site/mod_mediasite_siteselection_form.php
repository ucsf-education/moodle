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

class mod_mediasite_siteselection_form extends \moodleform {
    private $sitelist = null;
    public function __construct($sites) {
        $this->sitelist = $sites;
        parent::__construct();
    }
    public function definition() {
        global $OUTPUT, $DB;

        $mform = $this->_form;

        $options = array();
        if (is_array($this->sitelist) && count($this->sitelist) > 0) {
            foreach ($this->sitelist as $site) {
                $options[$site->id] = $site->sitename;
            }
        }

        $mform->addElement('html', '<h2>'.get_string('mediasite_server_list', 'mediasite').'</h2>');

        $usagecount = optional_param('usage_count', 0, PARAM_INT);
        if ($usagecount > 0) {
            $mform->addElement(
                'html',
                '<div id="purge_all_caches" class="box generalbox m-b-1 adminerror alert alert-danger p-y-1">'.
                get_string('purge_all_caches_message', 'mediasite').
                '</div>'
            );
        }

        $displaymbstringwarning = false;
        try {
            $displaymbstringwarning = !function_exists('mb_convert_encoding');
        } catch (exception $e) {
            $displaymbstringwarning = true;
        }

        if ($displaymbstringwarning) {
            $mform->addElement(
                'html',
                '<div id="mbstring_required" class="box generalbox m-b-1 adminerror alert alert-danger p-y-1">'.
                get_string('mbstring_required_message', 'mediasite').
                '</div>'
            );
        }

        if (is_array($this->sitelist) && count($this->sitelist) > 0) {
            $table = new \html_table();
            $table->head = array(get_string('sitenametblhder', 'mediasite'),
                                 get_string('siteroottblhder', 'mediasite'),
                                 get_string('my_mediasite', 'mediasite'),
                                 get_string('courses7', 'mediasite'),
                                 get_string('actiontblhder', 'mediasite'));
            foreach ($this->sitelist as $site) {
                $cells = array();
                $cells[] = new \html_table_cell($site->sitename);
                $cells[] = new \html_table_cell($site->endpoint);
                $cells[] = new \html_table_cell($site->show_my_mediasite ? $site->my_mediasite_title : '-');
                $cells[] = new \html_table_cell($site->show_integration_catalog ? $site->integration_catalog_title : '-');
                $actioncell = new \html_table_cell();
                $confirmationmessage = ($site->usage_count > 0) ? get_string(
                    'site_deletion_inuse_confirmation',
                    'mediasite',
                    $site->usage_count
                ) : get_string(
                    'site_deletion_unused_confirmation',
                    'mediasite'
                );
                $actioncell->text = $OUTPUT->action_icon(new \moodle_url('/mod/mediasite/site/edit.php',
                            array('site' => $site->id, 'section' => 'modmediasite')),
                        new \pix_icon('t/editstring', get_string('actionedit', 'mediasite')))
                    ." ".
                    $OUTPUT->action_icon(new \moodle_url('/mod/mediasite/site/delete.php', array(
                        'site' => $site->id,
                        'section' => 'modmediasite',
                        'usage_count' => $site->usage_count)
                        ), new \pix_icon('t/delete', get_string('actiondelete', 'mediasite')),
                                         null,
                                         array('onclick' => 'return confirm("'.$confirmationmessage.'");'));
                $cells[] = $actioncell;
                $row = new \html_table_row();
                $row->cells = $cells;
                $table->data[] = $row;
            }
            $mform->addElement('html', \html_writer::table($table));
        } else {
            $mform->addElement('html',  \html_writer::tag('p', \get_string('nosites', 'mediasite')));
        }
        $mform->addElement('html', \html_writer::tag('input', '', array('value' => \get_string('siteaddbuttonlabel', 'mediasite'),
                                                                        'type' => 'button',
                                                                        'id' => 'id_siteaddbutton',
                                                                        'name' => 'siteaddbutton')));

        $mform->addElement('header', 'mediasite_server_defaults', get_string('mediasite_server_defaults', 'mediasite'));

        if (is_array($this->sitelist)) {
            if (count($this->sitelist) > 0) {
                if (!$defaults = $DB->get_record('mediasite_config', array())) {
                    $sites = array_values($this->sitelist);
                    $site = $sites[0];
                    $record = new \stdClass();
                    $record->siteid = $site->id;
                    $record->openaspopup = 1;
                    $DB->insert_record('mediasite_config', $record);
                    $defaults = $DB->get_record('mediasite_config', array());
                }
            }
            if (count($this->sitelist) > 1) {
                $selectdropdown = $mform->addElement('select', 'sites', \get_string('sitenames', 'mediasite'), $options);
                $selectdropdown->setSelected($defaults->siteid);
            }
        }

        $config = $DB->get_record('mediasite_config', array());
        if (!$config && is_array($this->sitelist) && count($this->sitelist) > 0) {
            echo \html_writer::tag(
                'div',
                '* To complete the plugin configuration you must save the options below',
                array('class' => 'sofo-configuration-notice')
            );
        }

        $mform->addElement('advcheckbox', 'openaspopup', \get_string('openaspopup', 'mediasite') );
        if ($config) {
            $mform->setDefault('openaspopup', $config->openaspopup);
        } else {
            $mform->setDefault('openaspopup', 1);
        }

        $mform->closeHeaderBefore('mediasite_server_defaults');

        $this->add_action_buttons(true, get_string('savechangebutton', 'mediasite'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}