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

/**
 * Class MediasiteResource
 * @package Sonicfoundry
 */
class MediasiteResource {
    public function __construct($record) {
        if ($record instanceof \stdClass) {
            $this->id = $record->id;
            $this->course = $record->course;
            $this->name = $record->name;
            $this->description = $record->description;
            $this->resourceid = $record->resourceid;
            $this->resourcetype = $record->resourcetype;
            $this->openaspopup = $record->openaspopup;
            $this->restrictip = $record->restrictip;
            $this->recorddateutc = $record->recorddateutc;
            $this->presenters = $record->presenters;
            $this->sofotags = $record->sofotags;
            $this->displaymode = $record->displaymode;
            $this->launchurl = $record->launchurl;
            $this->siteid = $record->siteid;
        }
    }
    public $id;
    public $course;
    public $name;
    public $description;
    public $resourceid;
    public $resourcetype;
    public $openaspopup;
    public $restrictip;
    public $recorddateutc;
    public $presenters;
    public $sofotags;
    public $displaymode;
    public $launchurl;
    public $siteid;
}