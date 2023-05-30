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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/mod/mediasite/basiclti_mediasite_lib.php');
require_once($CFG->dirroot.'/mod/mediasite/locallib.php');

function mediasite_extend_navigation_user_settings(
    navigation_node $parentnode,
    stdClass $user,
    context_user $context,
    stdClass $course,
    context_course $coursecontext) {
}

function mediasite_navigation_extension_mymediasite() {
    global $PAGE;
    $mediasitenode = $PAGE->navigation->add(get_string('mediasite', 'mediasite'), null, navigation_node::TYPE_COURSE);
    $url = new moodle_url('/mod/mediasite/mymediasite.php');
    $mymediasitenode = $mediasitenode->add(get_string('my_mediasite', 'mediasite'), $url);
    $mymediasitenode->make_active();
    return $mediasitenode;
}

function mediasite_extend_navigation_course_settings(navigation_node $parentnode, context_course $context) {
    $overridecapability = 'mod/mediasite:overridedefaults';
    if (!has_capability($overridecapability, $context)) {
        return;
    }
    global $PAGE;
    $label = get_string('course_settings', 'mediasite');
    $key = 'mediasite_course_settings';
    $coursesettings = $parentnode->get($key);

    if ($coursesettings == null && $PAGE->course->id > 1) {
        $coursesettings = $parentnode->add(
            $label,
            new moodle_url(
                '/mod/mediasite/site/course_settings.php',
                array('id' => $PAGE->course->id)
        ), navigation_node::TYPE_SETTING, null, $key, new pix_icon('i/settings', $label));
    }

    return $coursesettings;
}

function mediasite_navigation_extension_mymediasite_placement() {
    if (!isLoggedIn()) {
        return;
    }

    global $PAGE, $USER, $DB;

    if (empty($USER->id)) {
        return;
    }

    $sitepagesid = 1;
    $id = optional_param('id', $sitepagesid, PARAM_INT);
    if (($PAGE->course != null)) {
        // BUG44083:    MOODLE: Naviagation error in Moodle if MyMediasite clicked while viewing media.
        $id = $PAGE->course->id;
    }
    $mymediasitecapability = 'mod/mediasite:mymediasite';

    $haspermission = mediasite_has_capability_in_any_context($mymediasitecapability);
    if (!$haspermission) {
        return;
    }

    $mymediasiteplacements = get_mediasite_sites(false, true);

    $coursenode = null;
    $coursecontext = null;
    $coursemediasitesite = null;
    $usercontext = context_user::instance($USER->id);

    if ($PAGE->course != null && $PAGE->course->id != $sitepagesid) {
        $coursecontext = context_course::instance($PAGE->course->id);
        $coursenode = $PAGE->navigation->find($PAGE->course->id, navigation_node::TYPE_COURSE);
        $coursemediasitesite = $DB->get_field('mediasite_course_config', 'mediasite_site', array('course' => $PAGE->course->id));
    }

    $showboostdivider = true;

    $inpagemenuoffset = 0;
    foreach ($mymediasiteplacements as $site) {
        $url = new moodle_url('/mod/mediasite/mymediasite.php', array('id' => $id, 'siteid' => $site->id));

        switch ($site->my_mediasite_placement) {
            case mediasite_menu_placement::SITE_PAGES:
                if ($haspermission) {

                    $sitepagenode = $PAGE->navigation->add($site->my_mediasite_title, $url, navigation_node::TYPE_CONTAINER);
                    // Comment out: "$sitepagenode->title("this is title!");".
                    $sitepagenode->isexpandable = true;
                    $sitepagenode->showinflatnavigation = true;

                    if (is_boost_navigation_available()) {
                        add_to_boost_navigation($site->my_mediasite_title, $url, $usercontext, $showboostdivider);
                        $showboostdivider = false;
                    }
                }
                break;

            case mediasite_menu_placement::COURSE_MENU:
                if ($coursecontext != null && $coursenode != null && $haspermission) {
                    $coursenode->add($site->my_mediasite_title, $url);
                }
                if (is_boost_navigation_available() && $haspermission) {
                    add_to_boost_navigation($site->my_mediasite_title, $url, $usercontext, $showboostdivider);
                }
                break;

            case mediasite_menu_placement::INPAGE_MENU:
                if ($haspermission) {
                    $inpagemenuoffset++;
                    $islastmenu = $inpagemenuoffset == 1;
                    $title = $site->my_mediasite_title;
                    add_inpage_menu($title, $url, $islastmenu);
                }
                break;
        }
    }
}

function add_inpage_menu($title, $url, $islastmenu) {
    global $PAGE;

    $menuappend = 0;
    $menusearch = '#region-main';
    $style = 'class="btn btn-default pull-right"';
    $inlinestyle = ' style="margin-left:5px;"';
    $menu = '<a '.$style.$inlinestyle.' href="'. $url.'">'.$title.' </a>';
    if ($islastmenu) {
        $menu .= '<br><br>';
    }

    $PAGE->requires->yui_module(['moodle-mod_mediasite-custmenu'],
        'M.local_mediasite.custmenu.init',
        [[
            'menusearch' => $menusearch,
            'menuappend' => $menuappend,
            'items'      => $menu,
        ]],
        null,
        false
    );
}

function is_boost_navigation_available() {
    global $PAGE, $CFG;
    $reason = 'All tests passed.';
    $result = true;

    try {
        if (!isLoggedIn()) {
            $reason = 'is_boost_navigation_available found you are not logged in';
            $result = false;
        } else if ($CFG->version < 2016120500) {
            $reason = 'is_boost_navigation_available  detected this is pre-3.2.';
            $result = false;
        } else if (is_null($PAGE)) {
            $reason = 'is_boost_navigation_available $PAGE is null';
            $result = false;
        } else if (starts_with($PAGE->pagetype, 'admin-')) {
            // On admin pages the $PAGE object acts weird, don't add Mediasite to admin pages.
            $reason = 'is_boost_navigation_available pagetype contains admin: ' . $PAGE->pagetype;
            $result = false;
        } else if (starts_with($PAGE->pagetype, 'grade-report-')) {
            // On admin pages the $PAGE object acts weird, don't add Mediasite to admin pages.
            $reason = 'is_boost_navigation_available pagetype contains grade-report: ' . $PAGE->pagetype;
            $result = false;
        } else if ($PAGE->pagetype == 'course-view-topics') {
            $reason = 'is_boost_navigation_available COURSE VIEW TOPICS';
            $result = false;
        } else if ($PAGE->pagetype == 'course-user') {
            $reason = 'is_boost_navigation_available COURSE USER';
            $result = false;
        } else if ($PAGE->pagetype == 'notes-index') {
            $reason = 'is_boost_navigation_available NOTES INDEX';
            $result = false;
        } else if (!property_exists('moodle_page', 'settingsnav') || is_null($PAGE->settingsnav)) {
            $reason = 'is_boost_navigation_available SETTINGSNAV is null';
            $result = false;
        } else if (is_null($PAGE->navigation)) {
            $reason = 'is_boost_navigation_available NAVIGATION is null';
            $result = false;
        } else if (is_null($PAGE->flatnav)) {
            $reason = 'is_boost_navigation_available FLATNAV is null';
            $result = false;
        }
    } catch (Exception $e) {
        $reason = $e->getMessage();
    }

    return $result;
}

function add_to_boost_navigation($linktext, $url, $context, $showboostdivider) {
    global $PAGE;
    try {
        $dashboard = navigation_node::create($linktext, $url);
        $flat = new flat_navigation_node($dashboard, 0);
        $flat->set_showdivider($showboostdivider);
        $flat->key = rand(1000, 9999);
        $PAGE->flatnav->add($flat);
        $templatecontext['flatnavigation'] = $PAGE->flatnav;
    } catch (Exception $e) {
        debugging('add_to_boost_navigation broke, ' . $e->getMessage());
    }
}

function starts_with($haystack, $needle) {
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function mediasite_navigation_extension_courses7_course() {
    global $PAGE, $DB, $CFG;
    $course = $PAGE->course;
    $currenttheme = $CFG->theme;

    if ($course && $course->id > 1) {
        $context = context_course::instance($course->id);
        if (!has_capability('mod/mediasite:courses7', $context)) {
            return;
        }

        $coursenode = $PAGE->navigation->find($course->id, navigation_node::TYPE_COURSE);

        try {
            $courseconfig = $DB->get_record('mediasite_course_config', array('course' => $course->id), '*', MUST_EXIST);
            if ($courseconfig->mediasite_courses_enabled) {
                $site = new Sonicfoundry\MediasiteSite($courseconfig->mediasite_site);
                $sitetitle = $site->get_integration_catalog_title();
                $url = new moodle_url(
                    '/mod/mediasite/courses7.php',
                    array('id' => $course->id, 'siteid' => $courseconfig->mediasite_site)
                );

                // For course Catalog, force to show as INPAGE_MENU if current theme is 'snap'.
                if ($currenttheme == 'snap') {
                    add_inpage_menu($sitetitle, $url, false);
                } else {
                    $coursesnode = $coursenode->add($site->get_integration_catalog_title(), $url);
                }

                if (is_boost_navigation_available()) {
                    add_to_boost_navigation($sitetitle, $url, $context, false);
                }
            }
        } catch (Exception $e) {
            foreach (get_mediasite_sites(true, false) as $site) {
                $url = new moodle_url('/mod/mediasite/courses7.php', array('id' => $course->id, 'siteid' => $site->id));
                $coursesnode = $coursenode->add($site->integration_catalog_title, $url);
                if (is_boost_navigation_available()) {
                    add_to_boost_navigation($site->integration_catalog_title, $url, $context, false);
                }
            }
        }
    }
}

function get_mediasite_sites($onlyintegrationcatalogenabled = false, $onlymymediasiteenabled = false) {
    global $DB;
    $select = '';
    $sort = '';
    if ($onlyintegrationcatalogenabled && $onlymymediasiteenabled) {
        $select = 'show_integration_catalog > 1 AND show_my_mediasite = 1';
    } else if ($onlyintegrationcatalogenabled) {
        $select = 'show_integration_catalog > 1';
        $sort = 'integration_catalog_title';
    } else if ($onlymymediasiteenabled) {
        $select = 'show_my_mediasite = 1';
        $sort = 'my_mediasite_title';
    }
    return $DB->get_records_select('mediasite_sites', $select, null, $sort, '*');
}
