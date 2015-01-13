<?php
// Respondus LockDown Browser Extension for Moodle
// Copyright (c) 2011-2014 Respondus, Inc.  All Rights Reserved.
// Date: May 28, 2014.

if (ini_get('zlib.output_compression')) {
    @ini_set('zlib.output_compression', 'Off');
}
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_login();
require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));
require_once($CFG->dirroot . '/blocks/lockdownbrowser/locklib.php');
echo '<p>' . get_string('count_tokens', 'block_lockdownbrowser') . '</p>';
flush();
$lockdownbrowser_rf = $DB->count_records('block_lockdownbrowser_toke');
if (!extension_loaded("curl")) {
    echo '<p>' . get_string('curlerror', 'block_lockdownbrowser') . '</p>';
} else if (!extension_loaded("mcrypt")) {
    echo '<p>' . get_string('mcrypterror', 'block_lockdownbrowser') . '</p>';
} else {
    echo '<p>' . get_string('request_tokens', 'block_lockdownbrowser') . '</p>';
    flush();
    if (is_siteadmin()) {
        lockdownbrowser_generate_tokens_debug();
    } else {
        lockdownbrowser_generate_tokens();
    }
    $lockdownbrowser_rf2 = $DB->count_records('block_lockdownbrowser_toke');
    if ($lockdownbrowser_rf2 > $lockdownbrowser_rf) {
        $lockdownbrowser_rf2 -= $lockdownbrowser_rf;
        echo "<p>" . get_string('added', 'block_lockdownbrowser')
            . " $lockdownbrowser_rf2 "
            . get_string('tokensok', 'block_lockdownbrowser') . "</p>";
    } else {
        echo "<p>" . get_string('tokenerror', 'block_lockdownbrowser') . "</p>";
    }
}

