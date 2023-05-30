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

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/mediasite/basiclti_locallib.php');
require_once($CFG->dirroot.'/mod/mediasite/basiclti_mediasite_lib.php');

$id = optional_param('id', 1, PARAM_INT);
$siteid = required_param('siteid', PARAM_INT);

if ($id && $id > 0) {
    if (! $course = $DB->get_record("course", array("id" => $id))) {
        throw new moodle_exception('generalexceptionmessage', 'error', '', 'Course is misconfigured');
    }
}

require_login($course);

mediasite_basiclti_mediasite_view($course, $siteid, mediasite_endpoint::LTI_MY_MEDIASITE);