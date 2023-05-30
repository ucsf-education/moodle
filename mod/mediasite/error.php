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

global $PAGE, $OUTPUT;

$PAGE->set_url($CFG->wwwroot . '/mod/mediasite/error.php');
$PAGE->set_title(get_string('mediasite', 'mediasite'));

$inpopup = optional_param('inpopup', 0, PARAM_BOOL);
if (!$inpopup) {
    $PAGE->set_pagelayout('popup');
} else {
    $PAGE->set_pagelayout('standard');
}
echo $OUTPUT->header();

echo html_writer::start_tag('div', array('class' => 'mform'));
echo html_writer::start_tag('div', array('class' => 'error'));
echo html_writer::tag('span', get_string('site_configuration_incomplete', 'mediasite'), array('class' => 'error'));
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

echo $OUTPUT->footer();

