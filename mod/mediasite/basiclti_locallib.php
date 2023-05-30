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

require_once($CFG->dirroot.'/mod/mediasite/basiclti_oauth.php');
require_once($CFG->dirroot.'/mod/mediasite/mediasitesite.php');
require_once($CFG->dirroot.'/lib/simplepie/library/SimplePie/Misc.php');

/**
 * Prints a Basic LTI activity
 *
 * $param int $basicltiid       Basic LTI activity id
 */
function mediasite_basiclti_view($instance, $siteid, $typeconfig, $endpoint, $arrayofenrollments = null,
    $arrayofcustomparameters = null) {
    global $PAGE, $CFG;

    $key = $typeconfig->lti_consumer_key;
    $secret = $typeconfig->lti_consumer_secret;
    $orgid = ''; // TODO: SHOULD THIS BE SOMETHING?
    $instance->debuglaunch = $typeconfig->lti_debug_launch;
    $instance->preferheight = 600;

    $course = $PAGE->course;
    $requestparams = mediasite_basiclti_build_request($instance, $typeconfig, $course, $arrayofenrollments,
        $arrayofcustomparameters);

    // Make sure we let the tool know what LMS they are being called from.
    $requestparams["ext_lms"] = "moodle-2";

    // Add oauth_callback to be compliant with the 1.0A spec.
    $requestparams["oauth_callback"] = "about:blank";

    $submittext = get_string('press_to_submit', 'mediasite');
    $parms = mediasite_sign_parameters($requestparams, $endpoint, "POST", $key, $secret, $submittext, $orgid);

    $debuglaunch = ( $instance->debuglaunch == 1 );
    if ( true ) {
        // TODO: Need frame height.
        $height = $instance->preferheight;
        if ((!$height) || ($height == 0)) {
            $height = 400;
        }
        $content = mediasite_post_launch_html($parms, $endpoint, $debuglaunch, $height);
    } else {
        $content = mediasite_post_launch_html($parms, $endpoint, $debuglaunch, false);
    }

    print $content;
}

/**
 * This function builds the request that must be sent to the tool producer
 *
 * @param object    $instance       Basic LTI instance object
 * @param object    $typeconfig     Basic LTI tool configuration
 * @param object    $course         Course object
 *
 * @return array    $request        Request details
 */
function mediasite_basiclti_build_request($instance, $typeconfig, $course, $arrayofenrollments, $arrayofcustomparameters) {
    global $USER, $CFG;

    $context = context_system::instance();
    $role = mediasite_copyof_lti_get_ims_role($USER, null, $course->id, false);

    $locale = $course->lang;
    if ( strlen($locale) < 1 ) {
         $locale = $CFG->lang;
    }

    $instance->launchinpopup = 0;

    $requestparams = array(
        "user_id" => $USER->id,
        "roles" => $role,
        "context_id" => $course->id,
        "context_label" => $course->shortname,
        "context_title" => $course->fullname,
        "launch_presentation_locale" => $locale,
        "launch_presentation_document_target" => $instance->launchinpopup == 0 ? "iframe" : "window",
        "lis_course_section_sourcedid" => $course->idnumber
    );

    if (!empty($USER->idnumber)) {
        $requestparams['lis_person_sourcedid'] = $USER->idnumber;
    }
    if (!empty($instance->name)) {
        $requestparams['resource_link_title'] = trim(html_to_text($instance->name, 0));
    }
    if (!empty($instance->cmid)) {
        $intro = format_module_intro('lti', $instance, $instance->cmid);
        $intro = trim(html_to_text($intro, 0, false));

        // This may look weird, but this is required for new lines
        // so we generate the same OAuth signature as the tool provider.
        $intro = str_replace("\n", "\r\n", $intro);
        $requestparams['resource_link_description'] = $intro;
    }
    if (!empty($instance->id)) {
        $requestparams['resource_link_id'] = $instance->id;
    }
    if (!empty($instance->resource_link_id)) {
        $requestparams['resource_link_id'] = $instance->resource_link_id;
    }
    if ($course->format == 'site') {
        $requestparams['context_type'] = 'Group';
    } else {
        $requestparams['context_type'] = 'CourseSection';
    }

    $requestparams["lis_person_name_given"] = $USER->firstname;
    $requestparams["lis_person_name_family"] = $USER->lastname;
    $requestparams["lis_person_name_full"] = $USER->firstname." ".$USER->lastname;
    $requestparams["lis_person_contact_email_primary"] = $USER->email;

    $placementsecret = $typeconfig->lti_consumer_secret;
    if ( isset($placementsecret) ) {
        $suffix = ':::' . $USER->id . ':::' . $instance->id;
        $plaintext = $placementsecret . $suffix;
        $hashsig = hash('sha256', $plaintext, false);
        $sourcedid = $hashsig . $suffix;
    }

    // Concatenate the custom parameters from the administrator and the instructor
    // Instructor parameters are only taken into consideration if the administrator
    // has giver permission.
    $customstr = $typeconfig->lti_custom_parameters;
    $custom = array();
    if ($customstr) {
        $custom = mediasite_split_custom_parameters($customstr);
    }
    $requestparams = array_merge($custom, $requestparams);

    // The LTI app will look for the following specific custom_mediasite_integration_* attributes
    // in the LTI POST and configure the LTI Search app accordingly.
    $mediasiteintegration = array();
    $mediasiteintegration['custom_mediasite_integration_callback']
        = mediasite_get_current_url($typeconfig->custom_integration_callback);

    $mediasiteembedformats = array();
    $site = new Sonicfoundry\MediasiteSite($typeconfig);
    $enabledembedformats = $site->get_embed_capabilities();
    foreach ($enabledembedformats as $format) {
        $mediasiteembedformats[] = $format->formattype;
    }

    $mediasiteintegration['ext_content_return_types'] = implode(',', $mediasiteembedformats);
    $mediasiteintegration['ext_user_username'] = $USER->username;
    $mediasiteintegration['ext_content_return_url'] = $typeconfig->id;

    // Similar to what the Mediasite Building Block for Blackboard and Canvas ext_roles, include
    // all the enrollment information for this user.
    if ($arrayofenrollments != null) {
        $mediasiteintegration['custom_mediasite_roles'] = implode(',', $arrayofenrollments);
    }
    if ($arrayofcustomparameters != null) {
        foreach ($arrayofcustomparameters as $param) {
            $mediasiteintegration['custom_mediasite_' . $param->getkey()] = $param->getvalue();

            // For assignment submission scenario, "lis_course_section_sourcedid" may empty from $PAGE->course->idnumber. Set this value to custom parameter "courseidnumber" instead.
            if($param->getkey() == "courseidnumber") {
                if(empty($requestparams["lis_course_section_sourcedid"])) {
                    $requestparams["lis_course_section_sourcedid"] = $param->getvalue();
                }
            }
        }
    }
    $requestparams = array_merge($mediasiteintegration, $requestparams);

    return $requestparams;
}

/**
 * Splits the custom parameters field to the various parameters
 *
 * @param string $customstr     String containing the parameters
 *
 * @return Array of custom parameters
 */
function mediasite_split_custom_parameters($customstr) {
    $lines = preg_split("/[\n;]/", $customstr);
    $retval = array();
    foreach ($lines as $line) {
        $pos = strpos($line, "=");
        if ( $pos === false || $pos < 1 ) {
            continue;
        }
        $key = trim(core_text::substr($line, 0, $pos));
        $val = trim(core_text::substr($line, $pos + 1));
        $key = mediasite_map_keyname($key);
        $retval['custom_'.$key] = $val;
    }
    return $retval;
}

/**
 * Used for building the names of the different custom parameters
 *
 * @param string $key   Parameter name
 *
 * @return string       Processed name
 */
function mediasite_map_keyname($key) {
    $newkey = "";
    $key = core_text::strtolower(trim($key));
    foreach (str_split($key) as $ch) {
        if ( ($ch >= 'a' && $ch <= 'z') || ($ch >= '0' && $ch <= '9') ) {
            $newkey .= $ch;
        } else {
            $newkey .= '_';
        }
    }
    return $newkey;
}

/**
 * Returns the IMS user role in a given context
 *
 * This function queries Moodle for an user role and
 * returns the correspondant IMS role
 *
 * @param StdClass $user          Moodle user instance
 * @param StdClass $context       Moodle context
 *
 * @return string                 IMS Role
 *
 */
function mediasite_basiclti_get_ims_role($user, $context) {

    $roles = get_user_roles($context, $user->id);
    $rolesname = array();
    foreach ($roles as $role) {
        $rolesname[] = $role->shortname; 
    }

    if (in_array('admin', $rolesname) || in_array('coursecreator', $rolesname) || in_array('manager', $rolesname)) {
        return 'Instructor,Administrator';
    }

    if (in_array('editingteacher', $rolesname) || in_array('teacher', $rolesname)) {
        return 'Instructor';
    }

    return 'Learner';
}

/**
 * Returns configuration details for the tool
 *
 * @param int $typeid   Basic LTI tool typeid
 *
 * @return array        Tool Configuration
 */
function mediasite_basiclti_get_type_config($siteid) {
    global $DB;

    $config = $DB->get_record('mediasite_sites', array('id' => $siteid));
    return $config;
}

/**
 * Transforms a basic LTI object to an array
 *
 * @param object $bltiobject    Basic LTI object
 *
 * @return array Basic LTI configuration details
 */
function mediasite_basiclti_get_config($bltiobject) {
    $typeconfig = array();
    $typeconfig = (array)$bltiobject;
    $additionalconfig = mediasite_basiclti_get_type_config($bltiobject->typeid);
    $typeconfig = array_merge($typeconfig, $additionalconfig);
    return $typeconfig;
}

/**
 *
 * Generates some of the tool configuration based on the instance details
 *
 * @param int $id
 *
 * @return Instance configuration
 *
 */
function mediasite_basiclti_get_type_config_from_instance($id) {
    global $DB;

    $instance = $DB->get_record('basiclti', array('id' => $id));
    $config = mediasite_basiclti_get_config($instance);

    $type = new stdClass();
    $type->lti_fix = $id;
    if (isset($config['toolurl'])) {
        $type->lti_toolurl = $config['toolurl'];
    }
    if (isset($config['preferheight'])) {
        $type->lti_preferheight = $config['preferheight'];
    }
    if (isset($config['instructorchoicesendname'])) {
        $type->lti_sendname = $config['instructorchoicesendname'];
    }
    if (isset($config['instructorchoicesendemailaddr'])) {
        $type->lti_sendemailaddr = $config['instructorchoicesendemailaddr'];
    }
    if (isset($config['instructorchoiceacceptgrades'])) {
        $type->lti_acceptgrades = $config['instructorchoiceacceptgrades'];
    }
    if (isset($config['instructorchoiceallowroster'])) {
        $type->lti_allowroster = $config['instructorchoiceallowroster'];
    }
    if (isset($config['instructorchoiceallowsetting'])) {
        $type->lti_allowsetting = $config['instructorchoiceallowsetting'];
    }
    if (isset($config['instructorcustomparameters'])) {
        $type->lti_allowsetting = $config['instructorcustomparameters'];
    }
    return $type;
}

/**
 * Signs the petition to launch the external tool using OAuth
 *
 * @param $oldparms     Parameters to be passed for signing
 * @param $endpoint     url of the external tool
 * @param $method       Method for sending the parameters (e.g. POST)
 * @param $oauth_consumoer_key          Key
 * @param $oauth_consumoer_secret       Secret
 * @param $submittext  The text for the submit button
 * @param $orgid       LMS name
 * @param $orgdesc     LMS key
 */
function mediasite_sign_parameters($oldparms, $endpoint, $method, $oauthconsumerkey, $oauthconsumersecret, $submittext, $orgid) {
    global $lastbasestring, $CFG;
    $parms = $oldparms;
    $parms["lti_version"] = "LTI-1p0";
    $parms["lti_message_type"] = "basic-lti-launch-request";
    if ( $orgid ) {
        $parms["tool_consumer_instance_guid"] = $orgid;
    }
    $parms["ext_submit"] = $submittext;

    if (!empty($CFG->mod_lti_institution_name)) {
        $parms['tool_consumer_instance_name'] = trim(html_to_text($CFG->mod_lti_institution_name, 0));
    } else {
        $parms['tool_consumer_instance_name'] = get_site()->shortname;
    }
    $parms['tool_consumer_instance_description'] = trim(html_to_text(get_site()->fullname, 0));

    $testtoken = '';
    $hmacmethod = new MediasiteOAuthSignatureMethod_HMAC_SHA1();
    $testconsumer = new MediasiteOAuthConsumer($oauthconsumerkey, $oauthconsumersecret, null);

    $accreq = MediasiteOAuthRequest::from_consumer_and_token($testconsumer, $testtoken, $method, $endpoint, $parms);
    $accreq->sign_request($hmacmethod, $testconsumer, $testtoken);

    // Pass this back up "out of band" for debugging.
    $lastbasestring = $accreq->get_signature_base_string();

    $newparms = $accreq->get_parameters();

    return $newparms;
}

/**
 * Posts the launch petition HTML
 *
 * @param $newparms     Signed parameters
 * @param $endpoint     URL of the external tool
 * @param $debug        Debug (true/false)
 */
function mediasite_post_launch_html($newparms, $endpoint, $debug=false, $height=false) {
    global $lastbasestring;
    if ($height) {
        $r = "<form action=\"".$endpoint.
            "\" name=\"ltiLaunchForm\" id=\"ltiLaunchForm\" method=\"post\" encType=\"application/x-www-form-urlencoded\">\n";
    } else {
        $r = "<form action=\"".$endpoint.
            "\" name=\"ltiLaunchForm\" id=\"ltiLaunchForm\" method=\"post\" encType=\"application/x-www-form-urlencoded\">\n";
    }
    $submittext = $newparms['ext_submit'];

    // Contruct html for the launch parameters.
    foreach ($newparms as $key => $value) {
        $key = htmlspecialchars($key);
        $value = htmlspecialchars($value);
        if ( $key == "ext_submit" ) {
            $r .= "<input type=\"submit\" name=\"";
        } else {
            $r .= "<input type=\"hidden\" name=\"";
        }
        $r .= $key;
        $r .= "\" value=\"";
        $r .= $value;
        $r .= "\"/>\n";
    }

    if ( $debug ) {
        $r .= "<script language=\"javascript\"> \n";
        $r .= "  //<![CDATA[ \n";
        $r .= "function basicltiDebugToggle() {\n";
        $r .= "    var ele = document.getElementById(\"basicltiDebug\");\n";
        $r .= "    if (ele.style.display == \"block\") {\n";
        $r .= "        ele.style.display = \"none\";\n";
        $r .= "    }\n";
        $r .= "    else {\n";
        $r .= "        ele.style.display = \"block\";\n";
        $r .= "    }\n";
        $r .= "} \n";
        $r .= "  //]]> \n";
        $r .= "</script>\n";
        $r .= "<a id=\"displayText\" href=\"javascript:basicltiDebugToggle();\">";
        $r .= get_string("toggle_debug_data", "mediasite")."</a>\n";
        $r .= "<div id=\"basicltiDebug\" style=\"display:none\">\n";
        $r .= "<b>".get_string("basiclti_endpoint", "mediasite")."</b><br/>\n";
        $r .= $endpoint . "<br/>\n&nbsp;<br/>\n";
        $r .= "<b>".get_string("basiclti_parameters", "mediasite")."</b><br/>\n";
        foreach ($newparms as $key => $value) {
            $key = htmlspecialchars($key);
            $value = htmlspecialchars($value);
            $r .= "$key = $value<br/>\n";
        }
        $r .= "&nbsp;<br/>\n";
        $r .= "<p><b>".get_string("basiclti_base_string", "mediasite")."</b><br/>\n".$lastbasestring."</p>\n";
        $r .= "</div>\n";
    }
    $r .= "</form>\n";

    if ( ! $debug ) {
        $extsubmit = "ext_submit";
        $extsubmittext = $submittext;
        $r .= " <script type=\"text/javascript\"> \n" .
            "  //<![CDATA[ \n" .
            "    document.getElementById(\"ltiLaunchForm\").style.display = \"none\";\n" .
            "    nei = document.createElement('input');\n" .
            "    nei.setAttribute('type', 'hidden');\n" .
            "    nei.setAttribute('name', '".$extsubmit."');\n" .
            "    nei.setAttribute('value', '".$extsubmittext."');\n" .
            "    document.getElementById(\"ltiLaunchForm\").appendChild(nei);\n" .
            "    document.ltiLaunchForm.submit(); \n" .
            "  //]]> \n" .
            " </script> \n";
    }
    return $r;
}

function mediasite_copyof_lti_get_ims_role($user, $cmid, $courseid, $islti2) {
    $roles = array();

    if (empty($cmid)) {
        // If no cmid is passed, check if the user is a teacher in the course
        // This allows other modules to programmatically "fake" a launch without
        // a real LTI instance.
        $coursecontext = context_course::instance($courseid);

        if (has_capability('moodle/course:manageactivities', $coursecontext)) {
            array_push($roles, 'Instructor');
        } else {
            array_push($roles, 'Learner');
        }
    } else {
        $context = context_module::instance($cmid);

        if (has_capability('mod/lti:manage', $context)) {
            array_push($roles, 'Instructor');
        } else {
            array_push($roles, 'Learner');
        }
    }

    if (is_siteadmin($user)) {
        if (!$islti2) {
            array_push($roles, 'urn:lti:sysrole:ims/lis/Administrator', 'urn:lti:instrole:ims/lis/Administrator');
        } else {
            array_push($roles, 'http://purl.imsglobal.org/vocab/lis/v2/person#Administrator');
        }
    }

    return join(',', $roles);
}

function mediasite_get_current_url($customintegrationcallback) {
    $phpserverport = $_SERVER['SERVER_PORT'];
    $phphttps = $_SERVER['HTTPS'];
    $phphttphost = $_SERVER['HTTP_HOST'];
    $phpself = $_SERVER['PHP_SELF'];

    $protocol = "http";
    $specialport = "";
    if ($phphttps == "on") {
        $protocol = "https";
        if ($phpserverport !== 443) {
            $specialport = $phpserverport;
        }
    } else {
        if ($phpserverport !== 80) {
            $specialport = $phpserverport;
        }
    }

    $host = $phphttphost;

    // If settings "custom_integration_callback" is not null or empty.
    if (isset($customintegrationcallback) && trim($customintegrationcallback) !== '') {
        $customcallbackurl = $_SERVER[$customintegrationcallback];
        if (strlen($customcallbackurl) > 0) {
            $host = $customcallbackurl;
        }
    }

    $url = $protocol . "://";
    if ($specialport === "" || ($specialport !== "" && stripos($host, strval($specialport)) !== false)) {
        $url .= $host;
    } else {
        $url .= $host . ":" . $specialport;
    }

    $request = $phpself;
    $query = isset($_SERVER['argv']) ? substr($_SERVER['argv'][0], strpos($_SERVER['argv'][0], ';') + 1) : '';

    return $url . $request . $query;
}

function mediasite_ensure_url_is_https($url) {
    if (!strstr($url, '://')) {
        $url = 'https://' . $url;
    } else {
        // If the URL starts with http, replace with https.
        if (stripos($url, 'http://') === 0) {
            $url = 'https://' . substr($url, 7);
        }
    }

    return $url;
}

/**
 * Build source ID
 *
 * @param int $instanceid
 * @param int $userid
 * @param string $servicesalt
 * @param null|int $typeid
 * @param null|int $launchid
 * @return stdClass
 */
function mediasite_build_sourcedid($instanceid, $userid, $servicesalt, $typeid = null, $launchid = null) {
    $data = new \stdClass();

    $data->instanceid = $instanceid;
    $data->userid = $userid;
    $data->typeid = $typeid;
    if (!empty($launchid)) {
        $data->launchid = $launchid;
    } else {
        $data->launchid = mt_rand();
    }

    $json = json_encode($data);

    $hash = hash('sha256', $json . $servicesalt, false);

    $container = new \stdClass();
    $container->data = $data;
    $container->hash = $hash;

    return $container;
}