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
$capabilities = array(
    'mod/mediasite:addinstance' => array (
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array (
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:manageactivities'
    ),
    'mod/mediasite:view' => array (
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array (
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW
        ),
    ),
    'mod/mediasite:overridedefaults' => array (
        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array (
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW
          ),
      ),
      'mod/mediasite:mymediasite' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array (
          'student' => CAP_ALLOW,
          'teacher' => CAP_ALLOW,
          'editingteacher' => CAP_ALLOW,
          'manager' => CAP_ALLOW,
          'coursecreator' => CAP_ALLOW,
          'user' => CAP_ALLOW
        ),
          ),
      'mod/mediasite:courses7' => array (
          'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_CONFIG,
          'captype' => 'read',
          'contextlevel' => CONTEXT_COURSE,
          'archetypes' => array (
              'student' => CAP_ALLOW,
              'teacher' => CAP_ALLOW,
              'editingteacher' => CAP_ALLOW,
              'manager' => CAP_ALLOW,
              'coursecreator' => CAP_ALLOW
              ),
          )
);
