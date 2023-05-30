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
require_once("$CFG->dirroot/mod/mediasite/exceptions.php");
require_once("$CFG->dirroot/mod/mediasite/db/upgradelib.php");

function xmldb_mediasite_upgrade($oldversion = 0) {

    global $CFG, $DB;
    $dbman = $DB->get_manager();

    $result = true;

    $plugin = new stdClass();
    include("$CFG->dirroot/mod/mediasite/version.php");

    // Upgrade.
    if ($oldversion < 2012032900) {
        $result = \mod_mediasite\db\upgradelib\mediasite_upgrade_from_2012032900($oldversion, $dbman, $plugin);
        upgrade_mod_savepoint(true, 2012032900, 'mediasite');
    }

    if ($oldversion < 2016041803) {
        $result = \mod_mediasite\db\upgradelib\mediasite_upgrade_from_2014042900($oldversion, $dbman, $plugin);
        upgrade_mod_savepoint(true, 2016041803, 'mediasite');
    }

    if ($oldversion < 2017020100) {
        $result = \mod_mediasite\db\upgradelib\mediasite_upgrade_from_2016041803($oldversion, $dbman, $plugin);
        upgrade_mod_savepoint(true, 2017020100, 'mediasite');
    }

    if ($oldversion < 2018062201) {
        $result = \mod_mediasite\db\upgradelib\mediasite_upgrade_from_2017020100($oldversion, $dbman, $plugin);
        upgrade_mod_savepoint(true, 2018062201, 'mediasite');
    }

    if ($oldversion < 2019090403) {
        $result = \mod_mediasite\db\upgradelib\mediasite_upgrade_from_2018062201($oldversion, $dbman, $plugin);
        upgrade_mod_savepoint(true, 2019090403, 'mediasite');
    }

    return true;
}


