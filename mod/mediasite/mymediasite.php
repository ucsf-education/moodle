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
require_once($CFG->dirroot.'/mod/mediasite/locallib.php');

global $DB, $PAGE, $OUTPUT;

$id = optional_param('id', 1, PARAM_INT);
$siteid = required_param('siteid', PARAM_INT);
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

$context = context_course::instance($id);
$PAGE->set_context($context);

$url = new moodle_url('/mod/mediasite/mymediasite.php', array('id' => $id, 'siteid' => $siteid));
$PAGE->set_url($url);

$typeconfig = mediasite_basiclti_get_type_config($siteid);

if ($typeconfig->my_mediasite_placement == mediasite_menu_placement::SITE_PAGES) {
    $context = context_system::instance();
}

require_login($course, true);
if (mediasite_has_capability_in_any_context('mod/mediasite:mymediasite')) {
    $PAGE->set_pagelayout('incourse');

    $pagetitle = strip_tags($course->shortname.': '.format_string($typeconfig->my_mediasite_title));
    $PAGE->set_title($pagetitle);
    $PAGE->set_heading($course->fullname);

    // Start the page.
    echo $OUTPUT->header();

    if ($typeconfig->openaspopup_my_mediasite == '1' and !$inpopup) {

        echo "\n<script type=\"text/javascript\">";
        echo "\n<!--\n";
        echo 'openpopup(null, {"url":"/mod/mediasite/mymediasite_launch.php?id='.$id.'&siteid='.$siteid.'&inpopup=true", '.
             '"name":"myMediasite","options":"resizable=1,scrollbars=1,directories=1,location=1,menubar=1,toolbar=1,status=1"});';
        echo "\n-->\n";
        echo '</script>';

        $link = "<a href=\"$CFG->wwwroot/mod/mediasite/mymediasite_launch.php?inpopup=true&amp;id={$id}&amp;siteid={$siteid}\" ".
                "onclick=\"this.target='myMediasite'; return openpopup('/mod/mediasite/mymediasite_launch.php?inpopup=true&amp;".
                "id={$id}', 'myMediasite','resizable=1,scrollbars=1,directories=1,location=1,menubar=1,toolbar=1,status=1');\">".
            format_string($typeconfig->my_mediasite_title, true)."</a>";

        echo '<div class="popupnotice">';
        print_string('popupresource', 'mediasite');
        echo '<br />';
        print_string('popupresourcelink', 'mediasite', $link);
        echo '</div>';

    } else {
        // Request the launch content with an iframe tag.
        $launchurl = new moodle_url('/mod/mediasite/mymediasite_launch.php', array('id' => $id, 'siteid' => $siteid));

        echo '<iframe id="contentframe" class="mediasite_lti_courses_iframe" src="'.$launchurl.'"></iframe>';

    }
    // Finish the page.
    echo $OUTPUT->footer();
}