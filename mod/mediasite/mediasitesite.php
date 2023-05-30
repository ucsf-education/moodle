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

namespace Sonicfoundry;

defined('MOODLE_INTERNAL') || die();

class Mediasitecontenttypes {
    const PRESENTATION = 'Presentation';
    const CATALOG = 'CatalogFolderDetails';
    const CHANNEL = 'MediasiteChannel';
}
class MediasiteEmbedFormatValues {
    const THUMBNAIL = 1;
    const ABSTRACT_ONLY = 2;
    const ABSTRACT_PLUS_PLAYER = 4;
    const LINK = 8;
    const EMBED = 16;
    const PRESENTATION_LINK = 32;
    const PLAYER_ONLY = 64;
}
class MediasiteEmbedFormatTypes {
    const THUMBNAIL = 'MetadataLight';
    const ABSTRACT_ONLY = 'MetadataOnly';
    const ABSTRACT_PLUS_PLAYER = 'MetadataPlusPlayer';
    const LINK = 'BasicLTI';
    const EMBED = 'iFrame';
    const PRESENTATION_LINK = 'PresentationLink';
    const PLAYER_ONLY = 'PlayerOnly';
}
class MediasiteEmbedFormat {
    public $contenttype;
    public $formatvalue;
    public $formattype;
    public $enabled;

    public function __construct($contenttype = null, $formatvalue = null, $formattype = null, $enabled = null) {
        $this->contenttype = $contenttype;
        $this->formatvalue = $formatvalue;
        $this->formattype = $formattype;
        $this->enabled = $enabled;
    }
}


/**
 * Class MediasiteSite
 * @package Sonicfoundry
 */
class MediasiteSite {
    private $id;
    private $sitename;
    private $endpoint;
    private $lticonsumerkey;
    private $lticonsumersecret;
    private $lticustomparameters;
    private $showintegrationcatalog;
    private $showassignmentsubmission;
    private $integrationcatalogtitle;
    private $openpopupintegrationcatalog;
    private $showmymediasite;
    private $mymediasitetitle;
    private $ltidebuglaunch;
    private $mymediasiteplacement;
    private $openaspopupmymediasite;
    private $embedformats;
    private $customintegrationcallback;

    public function __construct($record = null) {
        if (!is_null($record)) {
            if ($record instanceof MediasiteSite) {
                $this->id = $record->id;
                $this->sitename = $record->sitename;
                $this->endpoint = $record->endpoint;
                $this->lticonsumerkey = $record->lti_consumer_key;
                $this->lticonsumersecret = $record->lti_consumer_secret;
                $this->lticustomparameters = $record->lti_custom_parameters;
                $this->embedformats = $record->embed_formats;
                $this->showintegrationcatalog = $record->show_integration_catalog;
                $this->showassignmentsubmission = $record->show_assignment_submission;
                $this->integrationcatalogtitle = $record->integration_catalog_title;
                $this->openpopupintegrationcatalog = $record->openpopup_integration_catalog;
                $this->showmymediasite = $record->show_my_mediasite;
                $this->mymediasitetitle = $record->my_mediasite_title;
                $this->mymediasiteplacement = $record->my_mediasite_placement;
                $this->openaspopupmymediasite = $record->openaspopup_my_mediasite;
                $this->ltidebuglaunch = $record->lti_debug_launch;
                $this->customintegrationcallback = $record->custom_integration_callback;
            } else if ($record instanceof \stdClass) {
                $this->id = $record->id;
                $this->sitename = $record->sitename;
                $this->endpoint = $record->endpoint;
                $this->lticonsumerkey = $record->lti_consumer_key;
                $this->lticonsumersecret = $record->lti_consumer_secret;
                $this->lticustomparameters = $record->lti_custom_parameters;
                $this->embedformats = $record->embed_formats;
                $this->showintegrationcatalog = $record->show_integration_catalog;
                $this->showassignmentsubmission = $record->show_assignment_submission;
                $this->integrationcatalogtitle = $record->integration_catalog_title;
                $this->openpopupintegrationcatalog = $record->openpopup_integration_catalog;
                $this->showmymediasite = $record->show_my_mediasite;
                $this->mymediasitetitle = $record->my_mediasite_title;
                $this->mymediasiteplacement = $record->my_mediasite_placement;
                $this->openaspopupmymediasite = $record->openaspopup_my_mediasite;
                $this->ltidebuglaunch = $record->lti_debug_launch;
                $this->customintegrationcallback = $record->custom_integration_callback;
            } else if (is_numeric($record)) {
                global $DB;
                $record = $DB->get_record('mediasite_sites', array('id' => $record));
                if ($record) {
                    $this->id = $record->id;
                    $this->sitename = $record->sitename;
                    $this->endpoint = $record->endpoint;
                    $this->lticonsumerkey = $record->lti_consumer_key;
                    $this->lticonsumersecret = $record->lti_consumer_secret;
                    $this->lticustomparameters = $record->lti_custom_parameters;
                    $this->embedformats = $record->embed_formats;
                    $this->showintegrationcatalog = $record->show_integration_catalog;
                    $this->showassignmentsubmission = $record->show_assignment_submission;
                    $this->integrationcatalogtitle = $record->integration_catalog_title;
                    $this->openpopupintegrationcatalog = $record->openpopup_integration_catalog;
                    $this->showmymediasite = $record->show_my_mediasite;
                    $this->mymediasitetitle = $record->my_mediasite_title;
                    $this->mymediasiteplacement = $record->my_mediasite_placement;
                    $this->openaspopupmymediasite = $record->openaspopup_my_mediasite;
                    $this->ltidebuglaunch = $record->lti_debug_launch;
                    $this->customintegrationcallback = $record->custom_integration_callback;
                }
            }
        }
    }
    public function update_database() {
        $record = new \stdClass();
        $record->id = $this->id;
        $record->sitename = $this->sitename;
        $record->endpoint = $this->endpoint;
        $record->lti_consumer_key = $this->lticonsumerkey;
        $record->lti_consumer_secret = $this->lticonsumersecret;
        $record->lti_custom_parameters = $this->lticustomparameters;
        $record->embed_formats = $this->embedformats;
        $record->show_integration_catalog = $this->showintegrationcatalog;
        $record->show_assignment_submission = $this->showassignmentsubmission;
        $record->integration_catalog_title = $this->integrationcatalogtitle;
        $record->openpopup_integration_catalog = $this->openpopupintegrationcatalog;
        $record->show_my_mediasite = $this->showmymediasite;
        $record->my_mediasite_title = $this->mymediasitetitle;
        $record->my_mediasite_placement = $this->mymediasiteplacement;
        $record->openaspopup_my_mediasite = $this->openaspopupmymediasite;
        $record->lti_debug_launch = $this->ltidebuglaunch;
        $record->custom_integration_callback = $this->customintegrationcallback;

        global $DB;
        $DB->update_record('mediasite_sites', $record);
    }
    public function get_embed_capabilities($includedisabled = false, $contenttypefilter = null) {
        $result = array();
        if (($includedisabled || $this->embedformats & MediasiteEmbedFormatValues::PRESENTATION_LINK) &&
            ($contenttypefilter == null || $contenttypefilter == Mediasitecontenttypes::PRESENTATION)) {
            $result[] = new MediasiteEmbedFormat(
                Mediasitecontenttypes::PRESENTATION,
                MediasiteEmbedFormatValues::PRESENTATION_LINK,
                MediasiteEmbedFormatTypes::PRESENTATION_LINK,
                $this->embedformats & MediasiteEmbedFormatValues::PRESENTATION_LINK
            );
        }
        if (($includedisabled || $this->embedformats & MediasiteEmbedFormatValues::THUMBNAIL) &&
            ($contenttypefilter == null || $contenttypefilter == Mediasitecontenttypes::PRESENTATION)) {
            $result[] = new MediasiteEmbedFormat(
                Mediasitecontenttypes::PRESENTATION,
                MediasiteEmbedFormatValues::THUMBNAIL,
                MediasiteEmbedFormatTypes::THUMBNAIL,
                $this->embedformats & MediasiteEmbedFormatValues::THUMBNAIL
            );
        }
        if (($includedisabled ||
            $this->embedformats & MediasiteEmbedFormatValues::ABSTRACT_ONLY) &&
            ($contenttypefilter == null || $contenttypefilter == Mediasitecontenttypes::PRESENTATION)) {
            $result[] = new MediasiteEmbedFormat(
                Mediasitecontenttypes::PRESENTATION,
                MediasiteEmbedFormatValues::ABSTRACT_ONLY,
                MediasiteEmbedFormatTypes::ABSTRACT_ONLY,
                $this->embedformats & MediasiteEmbedFormatValues::ABSTRACT_ONLY
            );
        }
        if (($includedisabled || $this->embedformats & MediasiteEmbedFormatValues::PLAYER_ONLY) &&
            ($contenttypefilter == null || $contenttypefilter == Mediasitecontenttypes::PRESENTATION)) {
            $result[] = new MediasiteEmbedFormat(
                Mediasitecontenttypes::PRESENTATION,
                MediasiteEmbedFormatValues::PLAYER_ONLY,
                MediasiteEmbedFormatTypes::PLAYER_ONLY,
                $this->embedformats & MediasiteEmbedFormatValues::PLAYER_ONLY
            );
        }
        if (($includedisabled || $this->embedformats & MediasiteEmbedFormatValues::ABSTRACT_PLUS_PLAYER) &&
            ($contenttypefilter == null || $contenttypefilter == Mediasitecontenttypes::PRESENTATION)) {
            $result[] = new MediasiteEmbedFormat(
                Mediasitecontenttypes::PRESENTATION,
                MediasiteEmbedFormatValues::ABSTRACT_PLUS_PLAYER,
                MediasiteEmbedFormatTypes::ABSTRACT_PLUS_PLAYER,
                $this->embedformats & MediasiteEmbedFormatValues::ABSTRACT_PLUS_PLAYER
            );
        }
        if (($includedisabled || $this->embedformats & MediasiteEmbedFormatValues::LINK) &&
            ($contenttypefilter == null || $contenttypefilter == Mediasitecontenttypes::CATALOG)) {
            $result[] = new MediasiteEmbedFormat(
                Mediasitecontenttypes::CATALOG,
                MediasiteEmbedFormatValues::LINK,
                MediasiteEmbedFormatTypes::LINK,
                $this->embedformats & MediasiteEmbedFormatValues::LINK
            );
        }
        if (($includedisabled || $this->embedformats & MediasiteEmbedFormatValues::EMBED) &&
            ($contenttypefilter == null || $contenttypefilter == Mediasitecontenttypes::CATALOG)) {
            $result[] = new MediasiteEmbedFormat(
                Mediasitecontenttypes::CATALOG,
                MediasiteEmbedFormatValues::EMBED,
                MediasiteEmbedFormatTypes::EMBED,
                $this->embedformats & MediasiteEmbedFormatValues::EMBED
            );
        }
        if (($includedisabled || $this->embedformats & MediasiteEmbedFormatValues::LINK) &&
            ($contenttypefilter == null || $contenttypefilter == Mediasitecontenttypes::CHANNEL)) {
            $result[] = new MediasiteEmbedFormat(
                Mediasitecontenttypes::CHANNEL,
                MediasiteEmbedFormatValues::LINK,
                MediasiteEmbedFormatTypes::LINK,
                $this->embedformats & MediasiteEmbedFormatValues::LINK
            );
        }
        if (($includedisabled || $this->embedformats & MediasiteEmbedFormatValues::EMBED) &&
            ($contenttypefilter == null || $contenttypefilter == Mediasitecontenttypes::CHANNEL)) {
            $result[] = new MediasiteEmbedFormat(
                Mediasitecontenttypes::CHANNEL,
                MediasiteEmbedFormatValues::EMBED,
                MediasiteEmbedFormatTypes::EMBED,
                $this->embedformats & MediasiteEmbedFormatValues::EMBED
            );
        }
        return $result;
    }
    public function get_siteid() {
        return $this->id;
    }
    public function set_sitename($value) {
        $this->sitename = $value;
    }
    public function get_sitename() {
        return $this->sitename;
    }
    public function set_endpoint($value) {
        $this->endpoint = $value;
    }
    public function get_endpoint() {
        return $this->endpoint;
    }
    public function set_lti_consumer_key($value) {
        $this->lticonsumerkey = $value;
    }
    public function get_lti_consumer_key() {
        return $this->lticonsumerkey;
    }
    public function set_lti_consumer_secret($value) {
        $this->lticonsumersecret = $value;
    }
    public function get_lti_consumer_secret() {
        return $this->lticonsumersecret;
    }
    public function set_lti_custom_parameters($value) {
        $this->lticustomparameters = $value;
    }
    public function get_lti_custom_parameters() {
        return $this->lticustomparameters;
    }
    public function set_show_integration_catalog($value) {
        $this->showintegrationcatalog = $value;
    }
    public function get_show_integration_catalog() {
        return $this->showintegrationcatalog;
    }
    public function set_show_assignment_submission($value) {
        $this->showassignmentsubmission = $value;
    }
    public function get_show_assignment_submission() {
        return $this->showassignmentsubmission;
    }
    public function set_integration_catalog_title($value) {
        $this->integrationcatalogtitle = $value;
    }
    public function get_integration_catalog_title() {
        return $this->integrationcatalogtitle;
    }
    public function get_openpopup_integration_catalog() {
        return $this->openpopupintegrationcatalog;
    }
    public function set_openpopup_integration_catalog($value) {
        $this->openpopupintegrationcatalog = $value;
    }
    public function set_show_my_mediasite($value) {
        $this->showmymediasite = $value;
    }
    public function get_show_my_mediasite() {
        return $this->showmymediasite;
    }
    public function set_my_mediasite_title($value) {
        $this->mymediasitetitle = $value;
    }
    public function get_my_mediasite_title() {
        return $this->mymediasitetitle;
    }
    public function set_my_mediasite_placement($value) {
        $this->mymediasiteplacement = $value;
    }
    public function get_my_mediasite_placement() {
        return $this->mymediasiteplacement;
    }
    public function get_openaspopup_my_mediasite() {
        return $this->openaspopupmymediasite;
    }
    public function set_openaspopup_my_mediasite($value) {
        $this->openaspopupmymediasite = $value;
    }
    public function set_lti_debug_launch($value) {
        $this->ltidebuglaunch = $value;
    }
    public function get_lti_debug_launch() {
        return $this->ltidebuglaunch;
    }
    public function get_lti_embed_type_thumbnail() {
        return $this->embedformats & MediasiteEmbedFormatValues::THUMBNAIL;
    }
    public function set_lti_embed_type_thumbnail($value) {
        $this->set_lti_embed_type_bitmask($value, MediasiteEmbedFormatValues::THUMBNAIL);
    }
    public function get_lti_embed_type_abstract_only() {
        return $this->embedformats & MediasiteEmbedFormatValues::ABSTRACT_ONLY;
    }
    public function set_lti_embed_type_abstract_only($value) {
        $this->set_lti_embed_type_bitmask($value, MediasiteEmbedFormatValues::ABSTRACT_ONLY);
    }
    public function get_lti_embed_type_abstract_plus_player() {
        return $this->embedformats & MediasiteEmbedFormatValues::ABSTRACT_PLUS_PLAYER;
    }
    public function set_lti_embed_type_abstract_plus_player($value) {
        $this->set_lti_embed_type_bitmask($value, MediasiteEmbedFormatValues::ABSTRACT_PLUS_PLAYER);
    }
    public function get_lti_embed_type_link() {
        return $this->embedformats & MediasiteEmbedFormatValues::LINK;
    }
    public function set_lti_embed_type_link($value) {
        $this->set_lti_embed_type_bitmask($value, MediasiteEmbedFormatValues::LINK);
    }
    public function get_lti_embed_type_embed() {
        return $this->embedformats & MediasiteEmbedFormatValues::EMBED;
    }
    public function set_lti_embed_type_embed($value) {
        $this->set_lti_embed_type_bitmask($value, MediasiteEmbedFormatValues::EMBED);
    }
    public function get_lti_embed_type_presentation_link() {
        return $this->embedformats & MediasiteEmbedFormatValues::PRESENTATION_LINK;
    }
    public function set_lti_embed_type_presentation_link($value) {
        $this->set_lti_embed_type_bitmask($value, MediasiteEmbedFormatValues::PRESENTATION_LINK);
    }
    public function get_lti_embed_type_player_only() {
        return $this->embedformats & MediasiteEmbedFormatValues::PLAYER_ONLY;
    }
    public function set_lti_embed_type_player_only($value) {
        $this->set_lti_embed_type_bitmask($value, MediasiteEmbedFormatValues::PLAYER_ONLY);
    }
    public function set_lti_embed_type_bitmask($value, $bit) {
        if ($value == 0) {
            $this->embedformats = $this->embedformats & ~$bit;
        } else {
            $this->embedformats = $this->embedformats | $bit;
        }
    }
    public function get_custom_integration_callback() {
        return $this->customintegrationcallback;
    }
    public function set_custom_integration_callback($value) {
        $this->customintegrationcallback = $value;
    }

    public static function loadbyname($name) {
        global $DB;
        if ($record = $DB->get_record('mediasite_sites', array('sitename' => $name))) {
            $site = new MediasiteSite($record);
            return $site;
        } else {
            return false;
        }
    }
}

class MediasiteAssignmentDetail {
    private $key;
    private $value;

    /**
     * Get the value of key
     */
    public function getkey() {
        return $this->key;
    }

    /**
     * Set the value of key
     *
     * @return  self
     */
    public function setkey($key) {
        $this->key = $key;

        return $this;
    }

    /**
     * Get the value of value
     */
    public function getvalue() {
        return $this->value;
    }

    /**
     * Set the value of value
     *
     * @return  self
     */
    public function setvalue($value) {
        $this->value = $value;

        return $this;
    }

    public function __construct() {
    }

    public static function withdetails($key, $value) {
        $instance = new self();
        $instance->key = $key;
        $instance->value = $value;
        return $instance;
    }
}