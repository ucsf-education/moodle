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

require_once("$CFG->dirroot/mod/mediasite/navigation.php");
require_once("$CFG->dirroot/lib/formslib.php");
require_once("$CFG->dirroot/mod/mediasite/mediasitesite.php");
require_once("$CFG->dirroot/mod/mediasite/exceptions.php");

function mediasite_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_GROUPMEMBERSONLY:
            return true;
        case FEATURE_MOD_INTRO:
            return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_COMPLETION_HAS_RULES:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_ADVANCED_GRADING:
            return false;
        case FEATURE_PLAGIARISM:
            return false;

        default:
            return null;
    }
}

function mediasite_add_instance($data, $mform = null) {
    global $DB, $CFG;
    require_once("$CFG->libdir/resourcelib.php");

    $cmid = $data->coursemodule;

    $data->id = $DB->insert_record('mediasite', $data);

    return $data->id;
}
function mediasite_update_instance($data, $mform) {
    global $DB, $CFG;
    require_once("$CFG->libdir/resourcelib.php");

    $data->id = $data->instance;

    $DB->update_record('mediasite', $data);

    return true;
}
function mediasite_delete_instance($mediasiteid) {
    global $DB;
    return $DB->delete_records("mediasite", array('id' => $mediasiteid));
}
/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 * See get_array_of_activities() in course/lib.php
 *
 * @global object
 * @param object $coursemodule
 * @return cached_cm_info|null
 */
function mediasite_get_coursemodule_info($coursemodule) {
    global $DB;

    if ($mediasite = $DB->get_record('mediasite', array(
        'id' => $coursemodule->instance),
        'id, course, name, description, resourceid, resourcetype, recorddateutc AS recorddate, recorddateutc, '.
        'presenters, LENGTH(LTRIM(RTRIM(presenters))) AS presenters_length, sofotags, '.
        'LENGTH(LTRIM(RTRIM(sofotags))) AS tags_length, displaymode, launchurl, siteid')) {

        $mediasite->recorddate = date("Y-m-d H:i:s", $mediasite->recorddate);

        if ($lti = $DB->get_record('mediasite_sites', array('id' => $mediasite->siteid))) {
            // On upgrade, the lti consumer key must be set to allow a successful post. Only show this if the config is valid.
            if (isset($lti->lti_consumer_key)) {
                if (empty($mediasite->name)) {
                    $mediasite->name = "label{$mediasite->id}";
                    $DB->set_field('mediasite', 'name', $mediasite->name, array('id' => $mediasite->id));
                }
                if (!$record = $DB->get_record("mediasite_sites", array('id' => $mediasite->siteid))) {
                    mediasite_delete_instance($mediasite->id);
                    return null;
                }

                $info = new cached_cm_info();
                $info->content = render_mediasite_resource($mediasite, $coursemodule, $lti);

                return $info;
            } else {
                return null;
            }
        }

    } else {
        return null;
    }
}
function mediasite_user_complete($mediasite) {

}
function mediasite_user_outline($mediasite) {

}
function mediasite_cron($mediasite) {

}
function mediasite_print_recent_activity($mediasite) {
}

function render_mediasite_resource($mediasite, $coursemodule, $lti) {
    if ($mediasite->displaymode == 'BasicLTI') {
        return;
    }

    $contentelementid = 'mediasite-content-'.$coursemodule->id;
    $resize = 'function mediasite_resize_'.$coursemodule->id.'() { '.
        'var mc = document.getElementById("'.$contentelementid.'");'.
        'var foundparent = false;'.
        'var parent = mc.parentNode;'.
        'while (!foundparent && parent !== null) {'.
        '   if (parent.classList !=null && parent.classList.contains("mod-indent-outer")) {'.
        '       foundparent = true;'.
        '       parent.style.width = "100%";'.
        '   } else {'.
        '       parent = parent.parentNode;'.
        '   }'.
        '}'.
        '}; mediasite_resize_'.$coursemodule->id.'();';

    $content = html_writer::start_tag('div', array('class' => 'mediasite-content', 'id' => $contentelementid));

    if ($mediasite->resourcetype == 'Presentation') {
        switch ($mediasite->displaymode) {
            case 'MetadataLight' :
            case 'MetadataOnly' :
                $content .= render_mediasite_presentation_metadata($mediasite, $coursemodule, $lti);
            break;
            case 'MetadataPlusPlayer' :
                $content .= render_mediasite_presentation_metadata($mediasite, $coursemodule, $lti);
                $content .= generate_mediasite_iframe($mediasite, $coursemodule);
            break;
            case 'iFrame' :
                $content .= generate_mediasite_iframe($mediasite, $coursemodule);
            break;
            case 'PlayerOnly' :
                $content .= generate_mediasite_iframe($mediasite, $coursemodule);
            break;
            case 'PresentationLink' :
                return '';
            break;
            default :
                $content .= render_mediasite_presentation_metadata($mediasite, $coursemodule, $lti);
        }
    } else {
        switch ($mediasite->displaymode) {
            case 'iFrame' :
                $content .= render_mediasite_catalog_metadata($mediasite, $coursemodule->showdescription);
                $content .= generate_mediasite_iframe($mediasite, $coursemodule);
            break;
            default :
                $content .= render_mediasite_catalog_metadata($mediasite, $coursemodule->showdescription);
        }
    }

    $content .= html_writer::end_tag('div');
    $content .= html_writer::tag('script', $resize);
    return $content;
}

function render_mediasite_catalog_metadata($mediasite, $showdescription) {
    if (!($showdescription)) {
        return null;
    }
    $content = html_writer::start_tag('div', array('class' => 'mediasite-content-catalog'));
    if (isset($mediasite->description) && !is_null($mediasite->description)) {
        $content .= html_writer::tag('div', $mediasite->description, array('class' => 'mediasite-description'));

    }
    $content .= html_writer::end_tag('div');
    return $content;
}

function render_mediasite_presentation_metadata($mediasite, $coursemodule, $lti) {
    $showdescription = $coursemodule->showdescription;
    if (!($showdescription)) {
        return null;
    }

    global $CFG;

    $lightmode = ($mediasite->displaymode == 'MetadataLight');
    $hidethumbnail = ($mediasite->displaymode == 'MetadataPlusPlayer');

    $content = html_writer::start_tag('div', array('class' => 'mediasite-content-presentation'));

    if (!$hidethumbnail) {
        if (!function_exists('mb_convert_encoding')) {
            $content .= html_writer::tag(
                'div',
                get_string('mbstring_required_embed_message', 'mediasite'),
                array('class' => 'box generalbox m-b-1 adminerror alert alert-danger p-y-1')
            );
        } else {
            $content .= html_writer::tag(
                'img',
                '',
                array('align' => 'right',
                      'class' => 'mediasite-thumbnail',
                      'onerror' => 'this.style.display="none"',
                      'onload' => 'this.style.display="block"',
                      'onclick' => 'window.location.href="'.$CFG->wwwroot.
                      '/mod/mediasite/view.php?id='.$coursemodule->id.'";',
                      'src' => generate_mediasite_presentation_thumbnail_url($mediasite, $lti)));
        }
    }
    if (isset($mediasite->recorddateutc) && !is_null($mediasite->recorddateutc) && $mediasite->recorddateutc > 0) {
        $content .= html_writer::tag(
            'span',
            userdate($mediasite->recorddateutc,
                get_string('strftimedate')),
            array('class' => 'mediasite-air-date')
        );
    }
    if (!$lightmode && isset($mediasite->description) && !is_null($mediasite->description)) {
        $content .= html_writer::tag('div', $mediasite->description, array('class' => 'mediasite-description'));
    }
    if (!$lightmode) {
        $content .= generate_mediasite_presenters($mediasite);
        $content .= generate_mediasite_tags($mediasite);
    }
    $content .= html_writer::end_tag('div');

    return $content;
}

function generate_mediasite_presenters($mediasite) {
    $content = '';
    if (isset($mediasite->presenters) && !is_null($mediasite->presenters) && $mediasite->presenters_length > 0) {
        // Split on the delimiter ~!~.
        $presenters = explode('~!~', $mediasite->presenters);
        if (count($presenters) > 0) {
            $content = html_writer::tag('div', get_string('presenters', 'mediasite'), array(
                'class' => 'mediasite-presenter-header')
            );
            $content .= html_writer::start_tag('ul', array('class' => 'mediasite-presenter-list'));
            foreach ($presenters as $presenter) {
                $content .= html_writer::tag('li', $presenter, array('class' => 'mediasite-presenter'));
            }
            $content .= html_writer::end_tag('ul');
        }
    }
    return $content;
}

function generate_mediasite_tags($mediasite) {
    $content = '';
    if (isset($mediasite->sofotags) && !is_null($mediasite->sofotags) && $mediasite->tags_length > 0) {
        // Split on the delimiter ~!~.
        $tags = explode('~!~', $mediasite->sofotags);
        if (count($tags) > 0) {
            $content = html_writer::tag('div', get_string('sofotags', 'mediasite'), array('class' => 'mediasite-tags-header'));
            $content .= html_writer::tag('div', implode(', ', $tags), array('class' => 'mediasite-tags'));
        }
    }
    return $content;
}

function generate_mediasite_presentation_thumbnail_url($mediasite, $lti) {
    if (!function_exists('mb_convert_encoding')) {
        return;
    }
    return $lti->endpoint.'/LTI/Thumbnail?id='.$mediasite->resourceid.
        '&tck='.base64_encode(mb_convert_encoding($lti->lti_consumer_key, 'UTF-16LE', 'UTF-8')).
        '&height=168&width=300';
}

function generate_mediasite_iframe($mediasite, $coursemodule) {
    $coverplay = $mediasite->resourcetype == 'Presentation' ? '1' : '0';
    $css = 'mediasite-content-iframe';
    if ($mediasite->resourcetype == 'CatalogFolderDetails') {
        $css .= ' mediasite-content-iframe-catalog';
    }
    $url = new moodle_url('/mod/mediasite/content_launch.php', array('id' => $coursemodule->id, 'coverplay' => $coverplay));
    $content = html_writer::tag('iframe', null, array(
        'id' => 'mod-mediasite-view-'.$coursemodule->id,
        'class' => $css,
        'src' => $url,
        'allowfullscreen' => 'allowfullscreen')
    );
    return $content;
}

function mediasite_extend_navigation_course(navigation_node $parentnode, stdClass $course, context_course $context) {
    return mediasite_extend_navigation_course_settings($parentnode, $context);
}

