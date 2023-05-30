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

$siteid = optional_param('site', 0, PARAM_INT);

$context = context_system::instance();

global $CFG, $PAGE, $DB, $OUTPUT;

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot . '/mod/mediasite/site/edit.php');

require_login();
require_capability('mod/mediasite:addinstance', $context);
admin_externalpage_setup('activitysettingmediasite');

$PAGE->set_pagelayout('admin');
$PAGE->requires->js(new moodle_url('/mod/mediasite/js/mod_mediasite_site_form.js'), true);

$record = $DB->get_record('mediasite_sites', array('id' => $siteid));

$site = new Sonicfoundry\MediasiteSite($record);

$PAGE->set_title($site->get_sitename());

$editform = new mod_mediasite_site_form($site);
$mform =& $editform;
if ($mform->is_cancelled()) {
    redirect("configuration.php");
}
$data = $mform->get_data();
if ($data) {
    $navinstalled = $mform->is_navigation_installed();
    $site->set_sitename($data->sitename);
    $site->set_endpoint($data->siteurl);
    $site->set_lti_consumer_key($data->sitelti_consumer_key);
    $site->set_lti_consumer_secret($data->sitelti_consumer_secret);
    $site->set_lti_custom_parameters($data->sitelti_custom_parameters);
    if ($navinstalled) {
        $site->set_show_integration_catalog($data->show_integration_catalog);
        $site->set_integration_catalog_title($data->integration_catalog_title);
        $site->set_openpopup_integration_catalog($data->openpopup_integration_catalog);
        $site->set_show_my_mediasite($data->show_my_mediasite);
        $site->set_my_mediasite_title($data->my_mediasite_title);
        $site->set_my_mediasite_placement($data->my_mediasite_placement);
        $site->set_openaspopup_my_mediasite($data->openaspopup_my_mediasite);
    }
    $site->set_lti_debug_launch($data->lti_debug_launch);
    $site->set_lti_embed_type_thumbnail($data->lti_embed_type_thumbnail);
    $site->set_lti_embed_type_abstract_only($data->lti_embed_type_abstract_only);
    $site->set_lti_embed_type_abstract_plus_player($data->lti_embed_type_abstract_plus_player);
    $site->set_lti_embed_type_link($data->lti_embed_type_link);
    $site->set_lti_embed_type_embed($data->lti_embed_type_embed);
    $site->set_lti_embed_type_presentation_link($data->lti_embed_type_presentation_link);
    $site->set_lti_embed_type_player_only($data->lti_embed_type_player_only);
    $site->set_custom_integration_callback($data->sitecustom_integration_callback);

    $lastchar = substr($site->get_endpoint(), -1);
    if (strcmp($lastchar, '/') === 0) {
        $url = rtrim($site->get_endpoint(), '/');
        $site->set_endpoint($url);
    }

    $site->update_database();
    redirect("configuration.php");
}

echo $OUTPUT->header();

echo "<table border=\"0\" style=\"margin-left:auto;margin-right:auto\" cellspacing=\"3\" cellpadding=\"3\" width=\"640\">";
echo "<tr>";
echo "<td colspan=\"2\">";

$mform->display();

echo '</td></tr></table>';
echo $OUTPUT->footer();
