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

global $DB, $PAGE, $OUTPUT;

$id       = optional_param('id', 0, PARAM_INT);
$a        = optional_param('a', 0, PARAM_INT);
$frameset = optional_param('frameset', '', PARAM_ALPHA);
$inpopup  = optional_param('inpopup', 0, PARAM_BOOL);

$cm = $DB->get_record("course_modules", array("id" => $id));
if ($cm == null) {
    print_error(get_string('error_course_module_id_incorrect', 'mediasite'));
    return;
}
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

$context = context_module::instance($cm->id);
require_login($course, true);
require_capability('mod/mediasite:view', $context);

$mediasite = $DB->get_record("mediasite", array("id" => $cm->instance));

if (!$lti = $DB->get_record("mediasite_sites", array("id" => $mediasite->siteid))) {
    print_error(get_string('error_site_id_incorrect', 'mediasite'));
    return;
}

$url = new moodle_url('/mod/mediasite/view.php', array('id' => $id, 'a' => $a, 'frameset' => $frameset, 'inpopup' => $inpopup));

if ($inpopup) {
    $launchurl = new moodle_url(
        '/mod/mediasite/content_launch.php',
        array('id' => $id, 'a' => $a, 'frameset' => $frameset, 'inpopup' => $inpopup)
    );
    redirect($launchurl);
}

$PAGE->set_url($url);

$PAGE->set_pagelayout('incourse');

$pagetitle = strip_tags($course->shortname);
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

if ($mediasite->openaspopup == '1' and !$inpopup) {

    echo "\n<script type=\"text/javascript\">";
    echo "\n<!--\n";
    echo 'openpopup(null, {"url":"/mod/mediasite/view.php?id='.$cm->id.'&inpopup=true", '.'"name":"mediasite'.$mediasite->id.
        '", '.'"options":"resizable=1,scrollbars=1,directories=1,location=1,menubar=1,toolbar=1,status=1"});';
    echo "\n-->\n";
    echo '</script>';

    $link = "<a href=\"$CFG->wwwroot/mod/mediasite/view.php?inpopup=true&amp;id={$cm->id}\" ".
        "onclick=\"this.target='mediasite{$mediasite->id}'; return openpopup('/mod/mediasite/view.php?inpopup=true&amp;".
        "id={$cm->id}', 'mediasite{$mediasite->id}','resizable=1,scrollbars=1,directories=1,location=1,menubar=1,toolbar=1,".
        "status=1');\">".format_string($mediasite->name, true)."</a>";

    echo '<div class="popupnotice">';
    print_string('popupresource', 'resource');
    echo '<br />';
    print_string('popupresourcelink', 'resource', $link);
    echo '</div>';

} else {
    $launchurl = new moodle_url(
        '/mod/mediasite/content_launch.php',
        array('id' => $id, 'a' => $a, 'frameset' => $frameset, 'inpopup' => $inpopup)
    );

    echo '<iframe id="contentframe" class="mediasite_lti_courses_iframe" src="'.$launchurl.'"></iframe>';

}

echo $OUTPUT->footer();

$event = \mod_mediasite\event\course_module_viewed::create(array(
    'objectid' => $mediasite->id,
    'context' => $context
    ));
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('mediasite', $mediasite);
    $event->trigger();