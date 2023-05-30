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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/mediasite/basiclti_locallib.php');

class mediasite_endpoint {
    const LTI_SEARCH = 0;
    const LTI_MY_MEDIASITE = 1;
    const LTI_CATALOG = 2;
    const LTI_LAUNCH = 3;
    const LTI_COVERPLAY = 4;
    const LTI_ASSIGNMENT_UPLOAD = 5;
    const LTI_ASSIGNMENTSUBMISSION = 6;
    const LTI_UPLOAD = 7;
}

class mediasite_menu_placement {
    const SITE_PAGES = 0;
    const COURSE_MENU = 1;
    const INPAGE_MENU = 2;
}

function mediasite_basiclti_mediasite_view(
    $instance,
    $siteid,
    $endpointtype,
    $mediasiteid = null,
    $arrayofcustomparameters = null) {

    $typeconfig = mediasite_basiclti_get_type_config($siteid);
    $endpoint = $typeconfig->endpoint;
    $roles = mediasite_get_all_enrollments();

    if (!isset($typeconfig->lti_consumer_key)) {
        $inpopup = optional_param('inpopup', 0, PARAM_BOOL);
        redirect(new moodle_url('/mod/mediasite/error.php', array('inpopup' => $inpopup)));
    }

    switch ($endpointtype) {
        case mediasite_endpoint::LTI_SEARCH:
            $endpoint = $endpoint.'/LTI/';
            break;
        case mediasite_endpoint::LTI_MY_MEDIASITE:
            $endpoint = $endpoint.'/LTI/MyMediasite';
            break;
        case mediasite_endpoint::LTI_CATALOG:
            $endpoint = $endpoint.'/LTI/Catalog';
            break;
        case mediasite_endpoint::LTI_LAUNCH:
            if ($mediasiteid == null) {
                throw new moodle_exception(
                    'generalexceptionmessage',
                    'error',
                    '',
                    'mediasite_basiclti_mediasite_view was called without a value for $mediasiteid.'
                );
            }
            $endpoint = $endpoint.'/LTI/Home/Launch?mediasiteid='.$mediasiteid;
            break;
        case mediasite_endpoint::LTI_COVERPLAY:
            if ($mediasiteid == null) {
                throw new moodle_exception(
                    'generalexceptionmessage',
                    'error',
                    '',
                    'mediasite_basiclti_mediasite_view was called without a value for $mediasiteid.'
                );
            }
            $endpoint = $endpoint.'/LTI/Home/Coverplay?mediasiteid='.$mediasiteid;
            break;
        case mediasite_endpoint::LTI_ASSIGNMENT_UPLOAD:
            $endpoint = $endpoint.'/LTI/Assignment';
            break;
        case mediasite_endpoint::LTI_UPLOAD:
            $endpoint = $endpoint.'/LTI/Assignment';
            break;
        case mediasite_endpoint::LTI_ASSIGNMENTSUBMISSION:
            $endpoint = $endpoint.'/LTI/Assignment/Launch?id='.$mediasiteid;
            break;
        default:
            throw new moodle_exception(
                'generalexceptionmessage',
                'error',
                '',
                'mediasite_basiclti_mediasite_view was called with an invalid value for the $endpointtype argument.'
            );
    }

    mediasite_basiclti_view($instance, $siteid, $typeconfig, $endpoint, $roles, $arrayofcustomparameters);
}

// We do not use build-in function "get_user_roles" to avoid DB calls inside a loop.
// Rewrite this function based on source code of "get_user_roles": https://github.com/moodle/moodle/blob/master/lib/accesslib.php .
function mediasite_get_all_enrollments() {
    global $DB, $USER;

    $allenrollments = array();

    $selectenrolledcourses = '
        SELECT DISTINCT c.id as courseid, ct.id as contextid
            FROM {user} u
               INNER JOIN {role_assignments} ra ON ra.userid = u.id
               INNER JOIN {context} ct ON ct.id = ra.contextid
               INNER JOIN {course} c ON c.id = ct.instanceid
            WHERE u.id = ?';
    $coursesandcontextsinfo = $DB->get_records_sql($selectenrolledcourses, array($USER->id));
    if ($coursesandcontextsinfo == null) {
        return $allenrollments;
    }

    $coursecontextids = array();
    foreach ($coursesandcontextsinfo as $ccinfo) {
        $coursecontext = context_course::instance($ccinfo->courseid);
        array_push($coursecontextids, $ccinfo->contextid);
        $coursecontextids = array_merge($coursecontextids, $coursecontext->get_parent_context_ids());
    }

    // Filter out duplicate contextids.
    $coursecontextids = array_unique($coursecontextids);

    list($contextidinsql, $params) = $DB->get_in_or_equal($coursecontextids, SQL_PARAMS_QM);
    array_unshift($params, $USER->id);

    $selectroles = '
        SELECT ra.id, r.name, r.shortname, co.shortname as coursename
            FROM {role_assignments} ra, {role} r, {context} c, {course} co
            WHERE ra.userid = ?
                AND ra.roleid = r.id
                AND ra.contextid = c.id
                AND c.instanceid = co.id
                AND ra.contextid '.$contextidinsql;
    $rolesinfo = $DB->get_records_sql($selectroles, $params);

    foreach ($rolesinfo as $roleinfo) {
        $rolename = $roleinfo->shortname;
        if ($rolename == "admin" || $rolename == "coursecreator" || $rolename == "manager") {
            $imsrole = "Instructor,Administrator";
        } else if ($rolename == "editingteacher" || $rolename == "teacher") {
            $imsrole = "Instructor";
        } else {
            $imsrole = "Learner";
        }

        $allenrollments[] = mediasite_get_mediasite_formatted_role($imsrole, $roleinfo->coursename);
    }

    return $allenrollments;
}

function mediasite_get_mediasite_formatted_role($imsrole, $courseidentifier) {
    return $courseidentifier.':'.$imsrole;
}

function mediasite_get_assignment_details($coursemoduleid) {
    global $DB, $USER;
    $details = array();

    $assignmentdetails = '
        SELECT A.*,
               C.fullname,
               C.shortname,
               C.idnumber
          FROM {course_modules} CM
               INNER JOIN {course} C ON CM.course = C.id
               INNER JOIN {modules} M ON CM.module = M.id
               INNER JOIN {assign} A ON CM.instance = A.id
         WHERE CM.id = ?';

    $individualassignment = '
        SELECT *
          FROM {assign_submission} ASU
         WHERE ASU.userid = ?
           AND ASU.assignment = ?';

    $groupassignment = '
        SELECT ASU.id AS submissionid,
               ASU.attemptnumber,
               ASU.latest,
               G.id AS groupid,
               G.idnumber AS groupidnumber,
               G.name AS groupname
          FROM {assign_submission} ASU
               INNER JOIN {groups} G ON ASU.groupid = G.id
               INNER JOIN {groups_members} GM ON ASU.groupid = GM.groupid
         WHERE ASU.assignment = ?
           AND GM.userid = ?';

    if ($assignment = $DB->get_record_sql($assignmentdetails, array($coursemoduleid, $USER->id))) {
        array_push($details,
            Sonicfoundry\MediasiteAssignmentDetail::withDetails(
                'is_an_assignment', "true"
            ));
        array_push($details,
            Sonicfoundry\MediasiteAssignmentDetail::withDetails(
                'coursefullname', $assignment->fullname
            ));
        array_push($details,
            Sonicfoundry\MediasiteAssignmentDetail::withDetails(
                'courseshortname', $assignment->shortname
            ));
        array_push($details,
            Sonicfoundry\MediasiteAssignmentDetail::withDetails(
                'courseidnumber', $assignment->idnumber
            ));
        array_push($details,
            Sonicfoundry\MediasiteAssignmentDetail::withDetails(
                'assignmentname', $assignment->name
            ));
        array_push($details,
            Sonicfoundry\MediasiteAssignmentDetail::withDetails(
                'assignmentid', $assignment->id
            ));
        array_push($details,
            Sonicfoundry\MediasiteAssignmentDetail::withDetails(
                'courseid', $assignment->course
            ));
        if ($assignment->duedate > 0) {
            array_push($details,
                Sonicfoundry\MediasiteAssignmentDetail::withDetails(
                    'duedate', mediasite_format_datetime($assignment->duedate)
                ));
        }
        if ($assignment->allowsubmissionsfromdate > 0) {
            array_push($details,
                Sonicfoundry\MediasiteAssignmentDetail::withDetails(
                    'allowsubmissionsfromdate', mediasite_format_datetime($assignment->allowsubmissionsfromdate)
                ));
        }
        if ($assignment->cutoffdate > 0) {
            array_push($details,
                Sonicfoundry\MediasiteAssignmentDetail::withDetails(
                    'cutoffdate', mediasite_format_datetime($assignment->cutoffdate))
                );
        }
        array_push($details,
            Sonicfoundry\MediasiteAssignmentDetail::withDetails(
                'teamsubmission', $assignment->teamsubmission
            ));
        array_push($details,
            Sonicfoundry\MediasiteAssignmentDetail::withDetails(
                'requireallteammemberssubmit', $assignment->requireallteammemberssubmit
            ));
        array_push($details,
            Sonicfoundry\MediasiteAssignmentDetail::withDetails(
                'teamsubmissiongroupingid', $assignment->teamsubmissiongroupingid
            ));
        array_push($details,
            Sonicfoundry\MediasiteAssignmentDetail::withDetails(
                'blindmarking', $assignment->blindmarking
            ));
        array_push($details,
            Sonicfoundry\MediasiteAssignmentDetail::withDetails(
                'revealidentities', $assignment->revealidentities
            ));
        array_push($details,
            Sonicfoundry\MediasiteAssignmentDetail::withDetails(
                'attemptreopenmethod', $assignment->attemptreopenmethod
            ));
        array_push($details,
            Sonicfoundry\MediasiteAssignmentDetail::withDetails(
                'maxattempts', $assignment->maxattempts
            ));
        // Is this group or individual submission?
        if ($assignment->teamsubmission) {
            if ($group = $DB->get_record_sql($groupassignment, array($assignment->id, $USER->id))) {
                array_push($details,
                    Sonicfoundry\MediasiteAssignmentDetail::withDetails (
                        'groupid', $group->groupid
                    )
                );
                array_push($details,
                    Sonicfoundry\MediasiteAssignmentDetail::withDetails (
                        'groupidnumber', $group->groupidnumber
                    )
                );
                array_push($details,
                    Sonicfoundry\MediasiteAssignmentDetail::withDetails (
                        'groupname', $group->groupname
                    )
                );
                array_push($details,
                    Sonicfoundry\MediasiteAssignmentDetail::withDetails (
                        'submissionid', $group->submissionid
                    )
                );
                array_push($details,
                    Sonicfoundry\MediasiteAssignmentDetail::withDetails (
                        'attemptnumber', $group->attemptnumber
                    )
                );
                array_push($details,
                    Sonicfoundry\MediasiteAssignmentDetail::withDetails (
                        'latest', $group->latest
                    )
                );
            }
        } else {
            if ($individual = $DB->get_record_sql($individualassignment, array($assignment->id, $USER->id))) {
                array_push($details,
                    Sonicfoundry\MediasiteAssignmentDetail::withDetails (
                        'submissionid', $individual->id
                    )
                );
                array_push($details,
                    Sonicfoundry\MediasiteAssignmentDetail::withDetails (
                        'attemptnumber', $individual->attemptnumber
                    )
                );
                array_push($details,
                    Sonicfoundry\MediasiteAssignmentDetail::withDetails (
                        'latest', $individual->latest
                    )
                );
            }
        }
    }
    return $details;
}

function mediasite_format_datetime($date) {
    date_default_timezone_set(core_date::get_server_timezone_object()->getName());
    return date(DATE_ATOM, $date);
}