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
require_once($CFG->libdir . "/externallib.php");

class local_mediasite_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function getgroupmembership_parameters() {
        return new external_function_parameters(
                array('groupId' => new external_value(PARAM_INT, 'The assignment group id"'))
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function getgroupmembership($groupid) {
        global $CFG, $DB, $USER;

        // Parameter validation
        // REQUIRED.
        $params = self::validate_parameters(self::getgroupmembership_parameters(),
                array('groupId' => $groupid));

        // Context validation
        // OPTIONAL but in most web service it should present.
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        // Capability checking
        // OPTIONAL but in most web service it should present.
        if (!has_capability('moodle/user:viewdetails', $context)) {
            throw new moodle_exception('cannotviewprofile');
        }

        // Get all the group membership
        // Pay attention here: the first field of select statement should NOT be "groupid" or "groupname" which is unique.
        // Otherwise php will return only one record instead of many records we want.
        $selectstatement = '
        SELECT gm.userid, u.username, u.email as useremail, gm.groupid, g.name as groupname,
               g.idnumber as groupidnumber, u.firstname as userfirstname, u.lastname as userlastname
        FROM {groups_members} gm
            INNER JOIN {groups} g ON g.id = gm.groupid
            INNER JOIN {user} u ON u.id = gm.userid
        WHERE gm.groupid =?';

        $groupmembers = $DB->get_records_sql($selectstatement, array($groupid));
        $members = array();
        foreach ($groupmembers as $member) {
            array_push($members,
                array(
                    'groupid' => $member->groupid,
                    'groupidnumber' => $member->groupidnumber,
                    'groupname' => $member->groupname,
                    'userid' => $member->userid,
                    'username' => $member->username,
                    'useremail' => $member->useremail,
                    'userfirstname' => $member->userfirstname,
                    'userlastname' => $member->userlastname,
                ));
        }

        return $members;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function getgroupmembership_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'groupid' => new external_value(PARAM_INT, 'group id'),
                    'groupidnumber' => new external_value(PARAM_TEXT, 'group id number'),
                    'groupname' => new external_value(PARAM_TEXT, 'group name'),
                    'userid' => new external_value(PARAM_INT, 'user id of the member'),
                    'username' => new external_value(PARAM_TEXT, 'username of the member'),
                    'useremail' => new external_value(PARAM_TEXT, 'email of the member'),
                    'userfirstname' => new external_value(PARAM_TEXT, 'user first name'),
                    'userlastname' => new external_value(PARAM_TEXT, 'user last name'),
                )
            )
        );
    }

}
