<?php
// Respondus LockDown Browser Extension for Moodle
// Copyright (c) 2011-2014 Respondus, Inc.  All Rights Reserved.
// Date: May 28, 2014.

$string['pluginname']      = 'Respondus LockDown Browser';
$string['lockdownbrowser'] = 'Respondus LockDown Browser';

// admin settings page
$string["blockdescheader"]     = "Description";
$string["blockdescription"]    = "Respondus LockDown Browser Extension for Moodle";
$string["blockversionheader"]  = "Current version";
$string["adminsettingsheader"] = "Admin settings";

$string['adminsettingsheaderinfo'] =
    'The values for these settings are provided by Respondus. If you download the
    block from the Respondus Campus Portal, the settings are already included in the
    block. Please review the Administrator Guide for additional details.';

$string["adminstatus"] = "Block Status";

$string["authenticationsettingsheader"]     = "Authentication Settings";

$string["authenticationsettingsheaderinfo"] =
    "These are the credentials for the user under which the Respondus LockDown Browser
    and Respondus Monitor web services run. The user entered must be authorized to
    view and modify activities for all users in all courses. This information is never
    transmitted outside of this Moodle server and all Respondus Monitor web service
    requests are authenticated using Hash-based Message Authentication Codes. If the
    option \"Use HTTPS for logins\" in the Security->HTTP Security settings is selected,
    all Respondus Monitor web service requests enforce the use of HTTPS.";

$string["password"]                         = "Password";
$string["passwordinfo"]                     = "Password for the Respondus Monitor user.";
$string["username"]                         = "User name";
$string["usernameinfo"]                     = "Respondus Monitor user name.";

$string['servername'] = 'Server Name';

$string['servernameinfo'] =
    'This setting must match the name entered in the Respondus Campus Portal profile for this Moodle Server.';

$string['serverid']     = 'Server Id';
$string['serveridinfo'] = 'Institution ID for this Moodle server. Assigned by Respondus.';
$string['serversecret'] = 'Shared Secret';

$string['serversecretinfo'] =
    'This setting must match the secret entered in the Respondus Campus Portal profile for this Moodle Server.';

$string['servertype']     = 'License Type';
$string['servertypeinfo'] = 'Campus-wide = 0, Lab Pack = 1.';
$string['downloadurl']    = 'Download URL';

$string['downloadinfo'] =
    'Link for students to download browser client.  Leave blank to not display a link on attempts page.';

$string['sessioncookie']     = 'Moodle Session Cookie';
$string['sessioncookieinfo'] = 'Cookie name used by the Moodle server for user sessions.';

$string['dashboard']         = 'Dashboard';
$string['quizzes']           = 'Quizzes';
$string['lockdown_settings'] = 'LockDown Browser Settings';
$string['quiz']              = 'Quiz';
$string['disable']           = 'Disable';
$string['enable']            = 'Enable';
$string['ldb_required']      = 'Respondus LockDown Browser is required for this quiz.';
$string['click']             = 'Click';
$string['here']              = 'here';
$string['todownload']        = ' to download the installer.';
$string['requires_ldb']      = '- Requires Respondus LockDown Browser';
$string['requires_webcam']   = '- Requires Respondus LockDown Browser + Webcam';
$string['test_server']       = 'Test the server by requesting more tokens';
$string['tokens_free']       = 'Authentication tokens free';
$string['count_tokens']      = 'Counting existing tokens';
$string['request_tokens']    = 'Requesting additional tokens from server';
$string['added']             = 'Added';
$string['tokensok']          = 'tokens, token server working';
$string['curlerror']         = 'extension_loaded claims curl is not loaded.  Giving up.';
$string['mcrypterror']       = 'extension_loaded claims mcrypt is not loaded.  Giving up.';

$string['tokenerror'] =
    "No tokens added, possible causes are: locklibcfg.php settings incorrect,
    lib mcrypt not enabled, database problem, proxy/firewall blocking access to token server";

$string['ldb_download_disabled'] = 'The LockDown Browser download is not enabled on this site.';
$string['iframe_error']          = 'This page requires iframes support';

$string["errtokendb"]   = "- token db empty, please have server admin check status.";
$string["errsessiondb"] = "- session db error, please have server admin check status.";
$string["errdblook"]    = "- db lookup error, please have server admin check status.";
$string["errdbupdate"]  = "- db update error, please have server admin check status.";

$string['errcmid'] = 'There is no coursemodule with id {$a}';
$string['errcourse'] = 'Course is misconfigured';
$string['errquizid'] = 'There is no quiz with id {$a}';
$string['errnocourse'] = 'The course with id {$a->course} that the quiz with id {$a->id} belongs to is missing';
$string['errnocm'] = 'The course module for the quiz with id {$a} is missing';
$string['errattempt'] = 'No such attempt ID exists';
$string['errnoquiz1'] = 'The quiz with id {$a->instance} corresponding to this coursemodule {$a->id} is missing';
$string['errnoquiz2'] = 'The quiz with id {$a->quiz} belonging to attempt {$a->id} is missing';

