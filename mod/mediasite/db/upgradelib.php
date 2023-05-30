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

namespace mod_mediasite\db\upgradelib;

defined('MOODLE_INTERNAL') || die();

function mediasite_upgrade_from_2012032900($oldversion, $dbman, $plugin) {
    global $CFG, $DB;
    // Define table mediasite_status to be created.
    $table = new \xmldb_table('mediasite_status');

    // Adding fields to table mediasite_status.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('sessionid', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('processed', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('status', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null);

    // Adding keys to table mediasite_status.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

    // Conditionally launch create table for mediasite_status.
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
    // Conditionally launch create index for mediasite_status.
    $index = new \xmldb_index('sessionid');
    $index->set_attributes(XMLDB_INDEX_UNIQUE, array('sessionid'));
    if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
    }

    // Define table mediasite_sites to be created.
    $table = new \xmldb_table('mediasite_sites');

    // Adding fields to table mediasite_sites.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('sitename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, 'Default');
    $table->add_field('endpoint', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    $table->add_field('apikey', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null);
    $table->add_field('username', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, 'MediasiteAdmin');
    $table->add_field('password', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    $table->add_field('passthru', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
    $table->add_field('siteclient', XMLDB_TYPE_CHAR, '16', null, XMLDB_NOTNULL, null, null);
    $table->add_field('sslselect', XMLDB_TYPE_INTEGER, '1',   null, XMLDB_NOTNULL, null, '0');
    $table->add_field('cert',      XMLDB_TYPE_BINARY,  null,  null, null,          null, null);

    // Adding keys to table mediasite_sites.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_key('sitename', XMLDB_KEY_UNIQUE, array('sitename'));

    // Conditionally launch create table for mediasite_sites.
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

    // Define table mediasite_config to be created.
    $table = new \xmldb_table('mediasite_config');

    // Adding fields to table mediasite_config.
    $table = new \xmldb_table('mediasite_config');
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('siteid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('openaspopup', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
    $table->add_field('duration', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '300');
    $table->add_field('restrictip', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');

    // Adding keys to table mediasite_config.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_key('siteid', XMLDB_KEY_UNIQUE, array('siteid'));

    // Conditionally launch create table for mediasite_config.
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
        // Foreign key for mediasite_config.
        $key = new \xmldb_key('defaultsiteidforeignkey', XMLDB_KEY_FOREIGN, array('siteid'), 'mediasite_sites', array('id'));
        // Launch add key defaultsiteidforeignkey.
        $dbman->add_key($table, $key);
    }

    // Define field siteid & description & duration & restrictip to be added to mediasite.
    $table = new \xmldb_table('mediasite');
    $field = new \xmldb_field('siteid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

    // Conditionally launch add field siteid.
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    $field = new \xmldb_field('description', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
    // Conditionally launch add field intro.
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    $field = new \xmldb_field('duration', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '300');
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    $field = new \xmldb_field('restrictip', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
    $key = new \xmldb_key('siteidforeignkey', XMLDB_KEY_FOREIGN, array('siteid'), 'mediasite_sites', array('id'));
    // Launch add key siteidforeignkey.
    $dbman->add_key($table, $key);

    // At this point we have the new table and have updated the old table with the new field.
    // Update new table with records in table config.
    $defaultrecord = array();
    $siterecord = array();
    $siterecord['sitename'] = 'Default';
    $siterecord['passthru'] = '0';
    $whereclause = 'name LIKE \'mediasite%\'';

    $configrecords = $DB->get_records_sql("SELECT * FROM {config} WHERE $whereclause");
    foreach ($configrecords as $configrecord) {
        if ($configrecord->name == 'mediasite_username') {
            $siterecord['username'] = $configrecord->value;
        } else if ($configrecord->name == 'mediasite_password') {
            $siterecord['password'] = $configrecord->value;
        } else if ($configrecord->name == 'mediasite_serverurl') {
            $siterecord['endpoint'] = $configrecord->value;
        } else if ($configrecord->name == 'mediasite_ticketduration') {
            $defaultrecord['duration'] = $configrecord->value;
        } else if ($configrecord->name == 'mediasite_restricttoip') {
            $defaultrecord['restrictip'] = $configrecord->value;
        } else if ($configrecord->name == 'mediasite_openaspopup') {
            $defaultrecord['openaspopup'] = $configrecord->value;
        }
    }

    // When site is configured in old version of plugin. If not, will be configured using add.php.
    if (array_key_exists("endpoint", $siterecord) &&
        array_key_exists("username", $siterecord) &&
        array_key_exists("password", $siterecord)) {
        // TODO: get version of Mediasite (6 or 7).
        $soapclient = Sonicfoundry\MediasiteClientFactory::MediasiteClient(
            'soap',
            $siterecord['endpoint'],
            $siterecord['username'],
            $siterecord['password'],
            null
        );
        $siteproperties = $soapclient->QuerySiteProperties();
        $version = $siteproperties->SiteVersion;
        $soapclient->Logout();
        $matches = array();
        if (preg_match('/(6|7)\.(\d+)\.(\d+)/i', $version, $matches)) {
            if ($matches[1] == 6) {
                $client = Sonicfoundry\MediasiteClientFactory::MediasiteClient(
                    'soap',
                    $siterecord['endpoint'],
                    $siterecord['username'],
                    $siterecord['password'],
                    null
                );
                $apikeyneeded = false;
                $siterecord['siteclient'] = 'soap';
            } else if ($matches[1] == 7) {
                $client = Sonicfoundry\MediasiteClientFactory::MediasiteClient(
                    'odata',
                    $siterecord['endpoint'],
                    $siterecord['username'],
                    $siterecord['password'],
                    null
                );
                $apikeyneeded = true;
                $siterecord['siteclient'] = 'odata';
            }
        }
        try {
            if ($apikeyneeded) {
                if (!($apikey = $client->GetapikeyById())) {
                    if (!($apikey = $client->Createapikey())) {
                        return false;
                    }
                }
                $siterecord['apikey'] = $apikey->Id;
            }
        } catch (\Sonicfoundry\SonicfoundryException $se) {
            if (!($apikey = $client->Createapikey())) {
                return false;
            }
            $siterecord['apikey'] = $apikey->Id;
        } catch (Exception $e) {
            if (!($apikey = $client->Createapikey())) {
                return false;
            }
            $siterecord['apikey'] = $apikey->Id;
        }
    }

    // Now we are modifying the database records.

    $DB->delete_records_select('config', $whereclause);

    // Try not inserting duplicate records to database.
    try {
        $siteid = $DB->insert_record('mediasite_sites', $siterecord, true);
        $defaultrecord['siteid'] = $siteid;
        $DB->insert_record('mediasite_config', $defaultrecord, true);

        $mediasiters = $DB->get_recordset('mediasite');
        if ($mediasiters->valid()) {
            foreach ($mediasiters as $mediasiterecord) {
                $record = new stdClass();
                $record->id = $mediasiterecord->id;
                if ($mediasiterecord->resourcetype == get_string('presentation', 'mediasite')) {
                    $presentation = $client->QueryPresentationById($mediasiterecord->resourceid);
                    $record->description = $presentation->Description;

                } else if ($mediasiterecord->resourcetype == get_string('catalog', 'mediasite')) {
                    $catalog = $client->QueryCatalogById($mediasiterecord->resourceid);
                    $record->description = $catalog->Description;
                }
                $record->siteid = $siteid;
                if (isset($defaultrecord['openaspopup'])) {
                    $record->openaspopup = $defaultrecord['openaspopup'];
                }
                if (isset($defaultrecord['duration'])) {
                    $record->duration = $defaultrecord['duration'];
                }
                if (isset($defaultrecord['restrictip'])) {
                    $record->restrictip = $defaultrecord['restrictip'];
                }
                $DB->update_record('mediasite', $record, true);
                rebuild_course_cache($mediasiterecord->course, true);
            }
        }
        $mediasiters->close();
    } catch (Exception $e) {
        // Allow the upgrade to continue.
        return true;
    }

    return true;
}

function mediasite_upgrade_from_2014042900($oldversion, $dbman, $plugin) {
    $sitestable = new \xmldb_table('mediasite_sites');
    $contenttable = new \xmldb_table('mediasite');
    $configtable = new \xmldb_table('mediasite_config');
    $courseconfigtable = new \xmldb_table('mediasite_course_config');

    if ($dbman->table_exists($sitestable)) {
        $keyfield = new \xmldb_field('lti_consumer_key', XMLDB_TYPE_CHAR, '255', null, null, null);
        conditionally_add_field_to_table($sitestable, $keyfield, $dbman);

        $secretfield = new \xmldb_field('lti_consumer_secret', XMLDB_TYPE_CHAR, '255', null, null, null);
        conditionally_add_field_to_table($sitestable, $secretfield, $dbman);

        $customparamfield = new \xmldb_field('lti_custom_parameters', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
        conditionally_add_field_to_table($sitestable, $customparamfield, $dbman);

        $showintegrationcatalogfield = new \xmldb_field(
            'show_integration_catalog',
            XMLDB_TYPE_INTEGER,
            '1',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        conditionally_add_field_to_table($sitestable, $showintegrationcatalogfield, $dbman);

        $integrationcatalogtitlefield = new \xmldb_field(
            'integration_catalog_title',
            XMLDB_TYPE_CHAR,
            '255',
            null,
            XMLDB_NOTNULL,
            null,
            'Mediasite Catalog'
        );
        conditionally_add_field_to_table($sitestable, $integrationcatalogtitlefield, $dbman);

        $integrationcatalogopenaspopupfield = new \xmldb_field(
            'openpopup_integration_catalog',
            XMLDB_TYPE_INTEGER,
            '1',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        conditionally_add_field_to_table($sitestable, $integrationcatalogopenaspopupfield, $dbman);

        $showmymediasitefield = new \xmldb_field('show_my_mediasite', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        conditionally_add_field_to_table($sitestable, $showmymediasitefield, $dbman);

        $mymediasitetitlefield = new \xmldb_field(
            'my_mediasite_title',
            XMLDB_TYPE_CHAR,
            '255',
            null,
            XMLDB_NOTNULL,
            null,
            'My Mediasite'
        );
        conditionally_add_field_to_table($sitestable, $mymediasitetitlefield, $dbman);

        $mymediasiteplacementfield = new \xmldb_field(
            'my_mediasite_placement',
            XMLDB_TYPE_INTEGER,
            '1',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        conditionally_add_field_to_table($sitestable, $mymediasiteplacementfield, $dbman);

        $mymediasiteopenaspopupfield = new \xmldb_field(
            'openaspopup_my_mediasite',
            XMLDB_TYPE_INTEGER,
            '1',
            null,
            XMLDB_NOTNULL,
            null,
            '0'
        );
        conditionally_add_field_to_table($sitestable, $mymediasiteopenaspopupfield, $dbman);

        $debuglaunchfield = new \xmldb_field('lti_debug_launch', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        conditionally_add_field_to_table($sitestable, $debuglaunchfield, $dbman);

        $embedformatsfield = new \xmldb_field('embed_formats', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '31');
        conditionally_add_field_to_table($sitestable, $embedformatsfield, $dbman);

        // Remove columns that are no longer in use.
        $usernamefield = new \xmldb_field('username', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, 'MediasiteAdmin');
        conditionally_drop_field_from_table($sitestable, $usernamefield, $dbman);

        $passwordfield = new \xmldb_field('password', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        conditionally_drop_field_from_table($sitestable, $passwordfield, $dbman);

        $apikeyfield = new \xmldb_field('apikey', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null);
        conditionally_drop_field_from_table($sitestable, $apikeyfield, $dbman);

        $passthrufield = new \xmldb_field('passthru', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        conditionally_drop_field_from_table($sitestable, $passthrufield, $dbman);

        $siteclientfield = new \xmldb_field('siteclient', XMLDB_TYPE_CHAR, '16', null, XMLDB_NOTNULL, null, null);
        conditionally_drop_field_from_table($sitestable, $siteclientfield, $dbman);

        $sslselectfield = new \xmldb_field('sslselect', XMLDB_TYPE_INTEGER, '1',   null, XMLDB_NOTNULL, null, '0');
        conditionally_drop_field_from_table($sitestable, $sslselectfield, $dbman);

        $certfield = new \xmldb_field('cert',      XMLDB_TYPE_BINARY,  null,  null, null,          null, null);
        conditionally_drop_field_from_table($sitestable, $certfield, $dbman);

    } else {
        return false;
    }

    if ($dbman->table_exists($contenttable)) {
        $timecreatedfield = new \xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null);
        conditionally_rename_field($contenttable, $timecreatedfield, 'recorddateutc', $dbman);
        // The timecreated field should no longer be present, but let's make sure.
        conditionally_drop_field_from_table($contenttable, $timecreatedfield, $dbman);

        $presentersfield = new \xmldb_field('presenters', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
        conditionally_add_field_to_table($contenttable, $presentersfield, $dbman);

        $tagsfield = new \xmldb_field('tags', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
        conditionally_add_field_to_table($contenttable, $tagsfield, $dbman);

        $modefield = new \xmldb_field('mode', XMLDB_TYPE_CHAR, '50', null, null, null, null);
        conditionally_add_field_to_table($contenttable, $modefield, $dbman);

        $launchurlfield = new \xmldb_field('launchurl', XMLDB_TYPE_CHAR, '1000', null, null, null);
        conditionally_add_field_to_table($contenttable, $launchurlfield, $dbman);

        $sitesforeignkey = new \xmldb_key('siteidforeignkey', XMLDB_KEY_FOREIGN, array('siteid'), 'mediasite_sites', array('id'));
        conditionally_add_foreign_key_to_table($contenttable, $sitesforeignkey, $dbman);

        $timemodifiedfield = new \xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null);
        conditionally_drop_field_from_table($contenttable, $timemodifiedfield, $dbman);
    }

    if ($dbman->table_exists($configtable)) {
        $durationfield = new \xmldb_field('duration', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '300');
        conditionally_drop_field_from_table($configtable, $durationfield, $dbman);

        $restrictipfield = new \xmldb_field('restrictip', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        conditionally_drop_field_from_table($configtable, $restrictipfield, $dbman);
    } else {
        return false;
    }

    // Exposing the 'show description' field, set the flag on mdl_course_modules.
    update_show_description($dbman);

    $statustable = new \xmldb_table('mediasite_status');
    conditionally_drop_table($statustable, $dbman);

    // Add new course config table.
    if (!$dbman->table_exists($courseconfigtable)) {
        $courseconfigtable->add_field(
            'id',
            XMLDB_TYPE_INTEGER,
            '10',
            XMLDB_UNSIGNED,
            XMLDB_NOTNULL,
            XMLDB_SEQUENCE,
            null,
            null,
            null
        );
        $courseconfigtable->add_field('course', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $courseconfigtable->add_field(
            'mediasite_site',
            XMLDB_TYPE_INTEGER,
            '10',
            XMLDB_UNSIGNED,
            XMLDB_NOTNULL,
            null,
            null,
            null,
            null
        );
        $courseconfigtable->add_field('mediasite_courses_enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);

        $courseconfigtable->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);
        $courseconfigtable->add_key('foreignkey_course', XMLDB_KEY_FOREIGN, array('course'), 'course', array('id'));
        $courseconfigtable->add_key(
            'foreignkey_mediasite_sites',
            XMLDB_KEY_FOREIGN,
            array('mediasite_site'),
            'mediasites_sites',
            array('id')
        );

        $dbman->create_table($courseconfigtable);
    }

    return true;
}

function mediasite_upgrade_from_2016041803($oldversion, $dbman, $plugin) {
    global $CFG, $DB;

    // BUG44763: Moodle - Support for Tags in Moodle 3.1.x.
    $contenttable = new \xmldb_table('mediasite');
    $newtagsfield = new \xmldb_field('sofotags', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
    conditionally_add_field_to_table($contenttable, $newtagsfield, $dbman);

    $sql = "
        UPDATE {mediasite}
           SET sofotags = tags
         WHERE tags IS NOT NULL
    ";
    $DB->execute($sql);

    $oldtagsfield = new \xmldb_field('tags', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
    conditionally_drop_field_from_table($contenttable, $oldtagsfield, $dbman);

    // BUG45712: Moodle - "ORA-00928: missing SELECT keyword" when used with Oracle.
    $newmodefield = new \xmldb_field('displaymode', XMLDB_TYPE_CHAR, '50', null, null, null, null);
    conditionally_add_field_to_table($contenttable, $newmodefield, $dbman);

    $sql = "
        UPDATE {mediasite}
           SET displaymode = mode
         WHERE mode IS NOT NULL
    ";
    try {
        $DB->execute($sql);
    } catch (Exception $e) {
        // The mode migration SQL will fail in Oracle. Try again with Oracle-specific syntax.
        $sql = "
            UPDATE {mediasite}
               SET displaymode = \"mode\"
             WHERE \"mode\" IS NOT NULL
        ";
        // If this fails, the upgrade should fail.
        $DB->execute($sql);
    }

    $oldmodefield = new \xmldb_field('mode', XMLDB_TYPE_CHAR, '50', null, null, null, null);
    conditionally_drop_field_from_table($contenttable, $oldmodefield, $dbman);

    // BUG45712: Moodle - "ORA-00928: missing SELECT keyword" when used with Oracle.
    // The description column on the mediasite table should not be required.
    $descriptionfield = new \xmldb_field('description', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
    $dbman->change_field_notnull($contenttable, $descriptionfield, $continue = true, $feedback = true);

    return true;
}

function mediasite_upgrade_from_2017020100($oldversion, $dbman, $plugin) {
    $sitestable = new \xmldb_table('mediasite_sites');
    $sitesfield = new \xmldb_field('show_assignment_submission', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', null);

    // Conditionally launch add field show_assignment_submission.
    conditionally_add_field_to_table($sitestable, $sitesfield, $dbman);

    $coursetable = new \xmldb_table('mediasite_course_config');
    $coursefield = new \xmldb_field('assignment_submission_enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', null);

    // Conditionally launch add field assignment_submission_enabled.
    conditionally_add_field_to_table($coursetable, $coursefield, $dbman);
}

function mediasite_upgrade_from_2018062201($oldversion, $dbman, $plugin) {
    $sitestable = new \xmldb_table('mediasite_sites');
    $sitesfield = new \xmldb_field('custom_integration_callback', XMLDB_TYPE_CHAR, '255', null, null, null, null);

    // Conditionally launch add field custom_integration_callback.
    conditionally_add_field_to_table($sitestable, $sitesfield, $dbman);
}

function conditionally_add_field_to_table($table, $field, $dbman) {
    // Conditionally launch add field intro.
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
}

function conditionally_drop_field_from_table($table, $field, $dbman) {
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }
}

function conditionally_add_foreign_key_to_table($table, $key, $dbman) {
    try {
        $dbman->add_key($table, $key);
    } catch (Exception $e) {
        // Allow the upgrade to proceed.
        return true;
    }
}

function conditionally_drop_table($table, $dbman) {
    if ($dbman->table_exists($table)) {
        $dbman->drop_table($table);
    }
}

function conditionally_rename_field($table, $field, $newname, $dbman) {
    $targetfield = new \xmldb_field(
        $newname,
        $field->getType(),
        $field->getLength(),
        $field->getUnsigned(),
        $field->getNotNull(),
        $field->getSequence(),
        $field->getDefault()
    );
    if ($dbman->field_exists($table, $field) && !$dbman->field_exists($table, $targetfield)) {
        $dbman->rename_field($table, $field, $newname);
    }
}

function update_show_description($dbman) {
    global $DB;
    $sql = "
UPDATE {course_modules}
   SET showdescription = 1
 WHERE module IN (
                  SELECT M.id
                    FROM {modules} M
                   WHERE M.name = 'mediasite'
                )
   AND showdescription = 0
   AND id > 0
";
    $DB->execute($sql);
}
