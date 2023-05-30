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
require_once($CFG->dirroot . '/mod/mediasite/lib.php');
require_once("mod_mediasite_siteselection_form.php");

// Check the user is logged in.
require_login();

global $PAGE, $DB, $OUTPUT, $ADMIN;

$context = context_system::instance();

admin_externalpage_setup('activitysettingmediasite');
require_capability('mod/mediasite:addinstance', $context);

$PAGE->requires->yui_module('moodle-mod_mediasite-configure', 'M.mod_mediasite.configure.init');

$sql = '
SELECT MS.*, (SELECT COUNT(*) FROM {mediasite} M WHERE M.siteid = MS.id) AS usage_count
  FROM {mediasite_sites} MS
';
$sites = $DB->get_records_sql($sql);
$siteselectionform = new Sonicfoundry\mod_mediasite_siteselection_form($sites);
$mform =& $siteselectionform;
if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot);
}
$data = $mform->get_data();
if ($data) {
    $record = new stdClass();
    if (!isset($data->sites) || is_null($data->sites)) {
        $sites = $DB->get_records('mediasite_sites', null, '', "id");
        if (!is_null($sites) && count($sites) > 0) {
            $record->siteid = reset($sites)->id;
        }
    } else {
        $record->siteid = $data->sites;
    }
    $record->openaspopup = $data->openaspopup;
    $ids = $DB->get_records('mediasite_config', null, '', "id");
    if (!is_null($ids) && count($ids) > 0) {
        $record->id = reset($ids)->id;
        $DB->update_record('mediasite_config', $record);
    } else {
        $DB->insert_record('mediasite_config', $record);
    }
    redirect($CFG->wwwroot);
}

echo $OUTPUT->header();

echo "<table border=\"0\" style=\"margin-left:auto;margin-right:auto\" cellspacing=\"3\" cellpadding=\"3\" width=\"100%\" >";
echo "<tr>";
echo "<td colspan=\"2\">";

$mform->display();

echo '</td></tr></table>';

echo $OUTPUT->footer();
