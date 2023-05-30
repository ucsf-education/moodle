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

require_once("../../config.php");
require_once("$CFG->dirroot/mod/mediasite/lib.php");

$inpopup  = optional_param('inpopup', 0, PARAM_BOOL);
$id = required_param('id', PARAM_INT);
global $DB, $OUTPUT, $PAGE;

if (! ($course = $DB->get_record("course", array ("id" => $id)))) {
    print_error("Course ID is incorrect");
}

$context = context_course::instance($course->id);
require_login($course->id);

require_once("$CFG->dirroot/mod/mediasite/navigation.php");

$event = \mod_mediasite\event\course_module_instance_list_viewed::create(array(
    'context' => $context
));
$event->add_record_snapshot('course', $course);
$event->trigger();

$PAGE->set_url($CFG->wwwroot . '/mod/mediasite/view.php', array("id" => $id, "inpopup" => $inpopup));

$strmediasites = get_string("modulenameplural", "mediasite");
$strmediasite  = get_string("modulename", "mediasite");

$PAGE->set_title("$strmediasites");
$PAGE->set_heading("");
$PAGE->set_cacheable(true);
$PAGE->set_button("");
echo $OUTPUT->header();

if (! $mediasites = get_all_instances_in_course("mediasite", $course)) {
    notice("There are no mediasites", "../../course/view.php?id=$course->id");
    die;
}

$timenow = time();
$strname  = get_string("name");
$strweek  = get_string("week");
$strtopic  = get_string("topic");

$table = new html_table();
if ($course->format == "weeks") {
    $table->head  = array ($strweek, $strname);
    $table->align = array ("center", "left");
} else if ($course->format == "topics") {
    $table->head  = array ($strtopic, $strname);
    $table->align = array ("center", "left", "left", "left");
} else {
    $table->head  = array ($strname);
    $table->align = array ("left", "left", "left");
}

foreach ($mediasites as $mediasite) {
    if (!empty($mediasite->extra)) {
        $extra = urldecode($mediasite->extra);
    } else {
        $extra = "";
    }
    if (!$mediasite->visible) {
        $link = "<a class=\"dimmed\" $extra href=\"view.php?id=$mediasite->coursemodule\">".
        format_string($mediasite->name, true)."</a>";
    } else {
        $link = "<a foo='bar' $extra href=\"view.php?id=$mediasite->coursemodule\">".
        format_string($mediasite->name, true)."</a>";
    }

    if ($course->format == "weeks" or $course->format == "topics") {
        $cell1 = new html_table_cell($mediasite->section);
        $cell2 = new html_table_cell($link);
        $row = new html_table_row();
        $row->cells[] = $cell1;
        $row->cells[] = $cell2;
        $table->data[] = $row;
    } else {
        $cell = new html_table_cell($link);
        $row = new html_table_row();
        $row->cells[] = $cell;
        $table->data[] = $row;
    }
}

echo "<br />";

echo html_writer::table($table);

echo $OUTPUT->footer($course);
