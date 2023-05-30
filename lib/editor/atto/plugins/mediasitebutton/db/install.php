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
 * Install script for atto_mediasitebutton
 *
 * @package atto
 * @subpackage   atto_mediasitebutton
 * @copyright Sonic Foundry 2017  {@link http://sonicfoundry.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Install the Mediasite button into the Atto text editor
 */
function xmldb_atto_mediasitebutton_install() {
    $toolbar = get_config('editor_atto', 'toolbar');
    if (strpos($toolbar, 'mediasitebutton') === false && $toolbar && $toolbar != '') {
        $groups = explode("\n", $toolbar);
        // Try to put wiris in math group.
        $found = false;
        foreach ($groups as $i => $group) {
            $parts = explode('=', $group);
            if (trim($parts[0]) == 'files') {
                $groups[$i] = 'files = ' . trim($parts[1]) . ', mediasitebutton';
                $found = true;
            }
        }
        // Otherwise create a math group in the second position starting from
        // the end.
        if (!$found) {
            do {
                $last = array_pop($groups);
            } while (empty($last) && !empty($groups));
            $groups[] = 'files = mediasitebutton';
            $groups[] = $last;
        }
        // Update config variable.
        $toolbar = implode("\n", $groups);
        set_config('toolbar', $toolbar, 'editor_atto');
    }
}