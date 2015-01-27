<?php
// Respondus 4.0 Web Service Extension For Moodle
// Copyright (c) 2009-2015 Respondus, Inc.  All Rights Reserved.
// Date: January 07, 2015.
defined("MOODLE_INTERNAL") || die();
if (!function_exists("respondusws_getsettingsstring")) {
    function respondusws_getsettingsstring($identifier) {
        global $CFG;
        $component = "respondusws";
        if (isset($CFG) && $CFG->version >= 2012062500) {
            return new lang_string($identifier, $component);
        } else {
            return get_string($identifier, $component);
        }
    }
}
if ($ADMIN->fulltree) {
    $settings->add(
      new admin_setting_heading(
        "respondusws/moduledescheader",
        respondusws_getsettingsstring("moduledescheader"),
        respondusws_getsettingsstring("moduledescription")
      )
    );
    if (!isset($respondusws_info)) {
        $respondusws_version_file = dirname(__FILE__) . "/version.php";
        if (is_readable($respondusws_version_file)) {
            include($respondusws_version_file);
        }
    }
    if (isset($respondusws_info)) {
        $settings->add(
          new admin_setting_heading(
            "respondusws/moduleversionheader",
            respondusws_getsettingsstring("moduleversionheader"),
            "$respondusws_info->version ($respondusws_info->respondusws_release)"
          )
        );
    }
    $settings->add(
      new admin_setting_heading(
        "respondusws/authenticationsettingsheader",
        respondusws_getsettingsstring("authenticationsettingsheader"),
        respondusws_getsettingsstring("authenticationsettingsheaderinfo")
      )
    );
    $settings->add(
      new admin_setting_configtext(
        "respondusws/username",
        respondusws_getsettingsstring("username"),
        respondusws_getsettingsstring("usernameinfo"),
        "",
        PARAM_TEXT
      )
    );
    $settings->add(
      new admin_setting_configpasswordunmask(
        "respondusws/password",
        respondusws_getsettingsstring("password"),
        respondusws_getsettingsstring("passwordinfo"),
        ""
      )
    );
    $settings->add(
      new admin_setting_configpasswordunmask(
        "respondusws/secret",
        respondusws_getsettingsstring("secret"),
        respondusws_getsettingsstring("secretinfo"),
        ""
      )
    );
}
