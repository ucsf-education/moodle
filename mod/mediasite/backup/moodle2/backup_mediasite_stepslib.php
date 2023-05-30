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

/**
 * Define all the backup steps that will be used by the backup_mediasite_activity_task
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Define the complete mediasite structure for backup, with file and id annotations
 */
class backup_mediasite_activity_structure_step extends backup_activity_structure_step {
    protected function define_structure() {
         // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');
        // Define each element separated.
        $mediasite = new backup_nested_element('mediasite', array('id'), array(
            'id',
            'course',
            'name',
            'description',
            'resourceid',
            'resourcetype',
            'openaspopup',
            'siteid',
            'recorddateutc',
            'presenters',
            'sofotags',
            'displaymode',
            'launchurl'
        ));
        // Build the tree.
        // Define sources.
        $mediasite->set_source_table('mediasite', array('id' => backup::VAR_ACTIVITYID));
        // Define id annotations
        // None.
        // Define file annotations.
        $mediasite->annotate_files('mod_mediasite', 'description', null);
        // Return the root element(mediasite), wrapped into standard activity structure.
        return $this->prepare_activity_structure($mediasite);
    }
}