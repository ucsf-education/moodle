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

$modsettingmediasiteurl = new moodle_url('/mod/mediasite/site/configuration.php?section=modmediasite');

if (!$ADMIN->fulltree) {
    $settings = new admin_externalpage('activitysettingmediasite',
        get_string('pluginname', 'mediasite'),
        $modsettingmediasiteurl,
        'mod/mediasite:addinstance'
    );
} else {
    $settings->add(new admin_setting_heading(
        'name',
        get_string('admin_settings_header', 'mediasite'),
        get_string(
            'admin_settings_body',
            'mediasite',
            $CFG->wwwroot.'/mod/mediasite/site/configuration.php?section=modmediasite').
            '<script type="text/javascript">window.location.href="'.$modsettingmediasiteurl.'";</script>'
        )
    );
}