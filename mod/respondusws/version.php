<?php
// Respondus 4.0 Web Service Extension For Moodle
// Copyright (c) 2009-2014 Respondus, Inc.  All Rights Reserved.
// Date: September 19, 2014.
defined("MOODLE_INTERNAL") || die();
if (isset($CFG) && $CFG->version >= 2014051200) {
    if (!isset($plugin))
        $plugin = new stdClass();
    $respondusws_info = $plugin;
    $respondusws_info->component = 'mod_respondusws';
} else {
    if (!isset($module))
        $module = new stdClass();
    $respondusws_info = $module;
}
$respondusws_info->version = 2014091801;
$respondusws_info->respondusws_release = "2.7.1.01";
$respondusws_info->requires = 2010122500;
$respondusws_info->respondusws_latest = 2014051200.09;
$respondusws_info->cron = 0;
