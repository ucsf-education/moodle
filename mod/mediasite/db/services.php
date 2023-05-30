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

defined('MOODLE_INTERNAL') || die();

// We defined the web service functions to install.
$functions = array(
        'local_mediasite_getgroupmembership' => array(
                'classname'   => 'local_mediasite_external',
                'methodname'  => 'getgroupmembership',
                'classpath'   => 'mod/mediasite/externallib.php',
                'description' => 'Get group membership for a specific group.',
                'type'        => 'read',
                'ajax'        => true
        )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'Mediasite Group Membership Service' => array(
                'functions'       => array('local_mediasite_getgroupmembership'),
                'restrictedusers' => 0,
                'enabled'         => 0,
                'shortname'       => 'groupservice'
        )
);