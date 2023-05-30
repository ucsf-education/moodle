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
 * @package local_mediasite_courses
 * @copyright Sonic Foundry 2017  {@link http://sonicfoundry.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once(dirname(__FILE__) . '/../../config.php');

if (is_mod_mediasite_installed()) {
    require_once("$CFG->dirroot/mod/mediasite/navigation.php");
}

function local_mediasite_courses_extend_navigation(global_navigation $nav) {
    if (!is_mod_mediasite_installed()) {
        return;
    }
    mediasite_navigation_extension_mymediasite_placement();
    mediasite_navigation_extension_courses7_course();
}


function local_mediasite_courses_extends_navigation(global_navigation $nav) {
    if (!is_mod_mediasite_installed()) {
        return;
    }
    local_mediasite_courses_extend_navigation($nav);
}

function local_mediasite_courses_extend_settings_navigation($settingsnav, $context) {
    if ($context->contextlevel == CONTEXT_COURSE) {
        global $PAGE;
        $key = 'courseadmin';
        $coursenode = $settingsnav->get($key);
        if ($coursenode != false) {
            return mediasite_extend_navigation_course_settings($coursenode, $context);
        } else {
            return mediasite_extend_navigation_course_settings($settingsnav, $context);
        }
    }
    return null;
}

function is_mod_mediasite_installed() {
    global $CFG;
    return file_exists($CFG->dirroot."/mod/mediasite/navigation.php");
}

