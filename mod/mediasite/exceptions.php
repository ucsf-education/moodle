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

class SonicfoundryException extends \Exception {
    const QUERY_API_KEY_BY_NAME = 1;
    const CREATE_APIKEY = 2;
    const QUERY_SITE_PROPERTIES = 3;
    const QUERY_CATALOG_SHARES = 4;
    const QUERY_PRESENTATIONS = 5;
    const MODIFY_PRESENTATION = 6;
    const QUERY_TAGS_FOR_PRESENTATION = 7;
    const QUERY_PRESENTERS_FOR_PRESENTATION = 8;
    const QUERY_THUMBNAILS_FOR_PRESENTATION = 9;
    const QUERY_SLIDES_FOR_PRESENTATION = 10;
    const QUERY_LAYOUTOPTIONS_FOR_PRESENTATION = 11;
    const QUERY_PRESENTATION_BY_ID = 12;
    const QUERY_PLAYBACKURL = 13;
    const QUERY_CATALOG_BY_ID = 14;
    const MODIFY_CATALOG = 15;
    const CREATE_AUTH_TICKET = 16;
    const QUERY_API_KEY_BY_NAME_UNAUTHORIZED_DURING_CLIENT_CONSTRUCTION = 17;
    const QUERY_API_KEY_BY_NAME_UNKNOWN_DURING_CLIENT_CONSTRUCTION = 18;
    const INVALID_RESOURCE_TYPE = 19;
    const QUERY_API_KEY_BY_ID = 20;
    const QUERY_CATALOG_SHARES_TIMEOUT = 21;
    const QUERY_PRESENTATIONS_TIMEOUT = 22;
    const QUERY_FOLDERS = 23;
    const QUERY_PRESENTATIONS_FOR_FOLDER = 24;
    const INVALID_ARGUMENT = 25;
    private $data;
    public function __construct($message, $code, $data = null) {
        parent::__construct($message, $code);
        $this->data = $data;
    }
    public function get_data() {
        return $this->data;
    }
    public function is_timeout() {
        return parent::getCode() == self::QUERY_CATALOG_SHARES_TIMEOUT ||
               parent::getCode() == self::QUERY_PRESENTATIONS_TIMEOUT;
    }
    public function code_to_string() {
        switch($this->getCode()) {
            case self::QUERY_API_KEY_BY_NAME:
                return 'query API key by name';
            case self::CREATE_APIKEY:
                return 'create API key';
            case self::QUERY_SITE_PROPERTIES:
                return 'query site properties';
            case self::QUERY_CATALOG_SHARES:
                return 'query catalog shares';
            case self::QUERY_PRESENTATIONS:
                return 'query presentations';
            case self::MODIFY_PRESENTATION:
                return 'modify presentation';
            case self::QUERY_TAGS_FOR_PRESENTATION:
                return 'query tags for presentation';
            case self::QUERY_PRESENTERS_FOR_PRESENTATION:
                return 'query presenters for presentation';
            case self::QUERY_THUMBNAILS_FOR_PRESENTATION:
                return 'query thumbnails for presentation';
            case self::QUERY_SLIDES_FOR_PRESENTATION:
                return 'query slides for presentation';
            case self::QUERY_LAYOUTOPTIONS_FOR_PRESENTATION:
                return 'query layout options for presentation';
            case self::QUERY_PRESENTATION_BY_ID:
                return 'query presentation by id';
            case self::QUERY_PLAYBACKURL:
                return 'query playback URL';
            case self::QUERY_CATALOG_BY_ID:
                return 'query catalog by id';
            case self::MODIFY_CATALOG:
                return 'modify catalog';
            case self::CREATE_AUTH_TICKET:
                return 'create auth ticket';
            case self::QUERY_API_KEY_BY_NAME_UNAUTHORIZED_DURING_CLIENT_CONSTRUCTION:
                return 'unauthorized error query API key by name during client construction';
            case self::QUERY_API_KEY_BY_NAME_UNKNOWN_DURING_CLIENT_CONSTRUCTION:
                return 'unknown error query API key by name during client construction';
            case self::INVALID_RESOURCE_TYPE:
                return 'invalid resource type';
            case self::QUERY_API_KEY_BY_ID:
                return 'query API key by id';
            case self::QUERY_FOLDERS:
                return 'query folders';
            case self::QUERY_PRESENTATIONS_FOR_FOLDER:
                return 'query presentations for folder';
            case self::INVALID_ARGUMENT:
                return 'invalid argument';
            default:
                return 'unknonwn code';
        }
    }
};