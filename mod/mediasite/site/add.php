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


require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once("mod_mediasite_site_form.php");
require_once("$CFG->dirroot/mod/mediasite/exceptions.php");

$context = context_system::instance();

require_login();
require_capability('mod/mediasite:addinstance', $context);
admin_externalpage_setup('activitysettingmediasite');

global $PAGE, $DB, $OUTPUT;

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot . '/mod/mediasite/site/add.php');
$PAGE->set_pagelayout('admin');
$PAGE->requires->js(new moodle_url('/mod/mediasite/js/mod_mediasite_site_form.js'), true);

$addform = new mod_mediasite_site_form();
$mform =& $addform;

if ($mform->is_cancelled()) {
    redirect("configuration.php");
}
$data = $mform->get_data();
if ($data) {
    $navinstalled = $mform->is_navigation_installed();
    $record = new stdClass();
    $url = $data->siteurl;
    $record->sitename = $data->sitename;
    if (!preg_match('%\bhttps?:\/\/%si', $data->siteurl)) {
        $data->siteurl = 'http://'.$data->siteurl;
    }
    $record->endpoint = $data->siteurl;
    $record->lti_consumer_key = $data->sitelti_consumer_key;
    $record->lti_consumer_secret = $data->sitelti_consumer_secret;
    $record->lti_custom_parameters = $data->sitelti_custom_parameters;
    $record->custom_integration_callback = $data->sitecustom_integration_callback;
    if ($navinstalled) {
        $record->show_integration_catalog = $data->show_integration_catalog;
        $record->integration_catalog_title = $data->integration_catalog_title;
        $record->openpopup_integration_catalog = $data->openpopup_integration_catalog;
        $record->show_my_mediasite = $data->show_my_mediasite;
        $record->my_mediasite_title = $data->my_mediasite_title;
        $record->my_mediasite_placement = $data->my_mediasite_placement;
        $record->openaspopup_my_mediasite = $data->openaspopup_my_mediasite;
    }
    $record->lti_debug_launch = $data->lti_debug_launch;
    $record->embed_formats = $data->lti_embed_type_thumbnail;
    $record->embed_formats |= $data->lti_embed_type_abstract_only;
    $record->embed_formats |= $data->lti_embed_type_abstract_plus_player;
    $record->embed_formats |= $data->lti_embed_type_link;
    $record->embed_formats |= $data->lti_embed_type_embed;
    $record->embed_formats |= $data->lti_embed_type_presentation_link;
    $record->embed_formats |= $data->lti_embed_type_player_only;
    $siteid = $DB->insert_record('mediasite_sites', $record);

    redirect("configuration.php");
}

echo $OUTPUT->header();

echo "<table border=\"0\" style=\"margin-left:auto;margin-right:auto\" cellspacing=\"3\" cellpadding=\"3\" width=\"640\">";
echo "<tr>";
echo "<td colspan=\"2\">";

$mform->display();

echo '</td></tr></table>';

echo $OUTPUT->footer();