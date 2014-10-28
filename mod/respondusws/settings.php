<?php
// Respondus 4.0 Web Service Extension For Moodle
// Copyright (c) 2009-2014 Respondus, Inc.  All Rights Reserved.
// Date: September 19, 2014.
defined("MOODLE_INTERNAL") || die();
if ($ADMIN->fulltree) {
    $settings->add(
      new admin_setting_heading(
        "respondusws/moduledescheader",
        get_string("moduledescheader", "respondusws"),
        get_string("moduledescription", "respondusws")
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
            get_string("moduleversionheader", "respondusws"),
            "$respondusws_info->version ($respondusws_info->respondusws_release)"
          )
        );
    }
    if (isset($respondusws_info) && $respondusws_info->respondusws_latest < $CFG->version) {
            $respondusws_warning = get_string("upgradewarning", "respondusws");
            $respondusws_warning .= $respondusws_info->respondusws_latest;
            $settings->add(
              new admin_setting_heading(
                "respondusws/upgradewarningheader",
                get_string("upgradewarningheader", "respondusws"),
                $respondusws_warning
              )
            );
    }
    $settings->add(
      new admin_setting_heading(
        "respondusws/authenticationsettingsheader",
        get_string("authenticationsettingsheader", "respondusws"),
        get_string("authenticationsettingsheaderinfo", "respondusws")
      )
    );
    $settings->add(
      new admin_setting_configtext(
        "respondusws/username",
        get_string("username", "respondusws"),
        get_string("usernameinfo", "respondusws"),
        "",
        PARAM_TEXT
      )
    );
    $settings->add(
      new admin_setting_configpasswordunmask(
        "respondusws/password",
        get_string("password", "respondusws"),
        get_string("passwordinfo", "respondusws"),
        ""
      )
    );
    $settings->add(
      new admin_setting_configpasswordunmask(
        "respondusws/secret",
        get_string("secret", "respondusws"),
        get_string("secretinfo", "respondusws"),
        ""
      )
    );
}
