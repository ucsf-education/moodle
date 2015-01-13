<?php
// Respondus LockDown Browser Extension for Moodle
// Copyright (c) 2011-2014 Respondus, Inc.  All Rights Reserved.
// Date: May 28, 2014.

// production flags
// - should all default to false
// - set to TRUE only for exceptional environments
$lockdownbrowser_ignore_https_login = false; // set true to ignore $CFG->loginhttps

// debug-only flags
// - should always be false for production environments
$lockdownbrowser_disable_callbacks  = false; // set true to skip login security callbacks
$lockdownbrowser_monitor_enable_log = false; // set true to enable logging to temp file
$lockdownbrowser_monitor_enable_phpinfo = false; // set true to enable PHPInfo reporting

// local options
define ("LOCKDOWNBROWSER_MONITOR_REDEEMURL",
    "https://smc-service-cloud.respondus2.com/MONServer/lms/redeemtoken.do");
define ("LOCKDOWNBROWSER_MONITOR_LOG", "ldb_monitor.log");

// Moodle options
define("NO_DEBUG_DISPLAY", true);

$lockdownbrowser_moodlecfg_file =
    dirname(dirname(dirname(__FILE__))) . "/config.php";
if (is_readable($lockdownbrowser_moodlecfg_file)) {
    require_once($lockdownbrowser_moodlecfg_file);
} else {
    lockdownbrowser_monitorserviceerror(2001, "Moodle config.php not found");
}

$lockdownbrowser_gradelib_file = "$CFG->libdir/gradelib.php";
if (is_readable($lockdownbrowser_gradelib_file)) {
    require_once($lockdownbrowser_gradelib_file);
} else {
    lockdownbrowser_monitorserviceerror(2030, "Moodle gradelib.php not found");
}

$lockdownbrowser_locklib_file =
    "$CFG->dirroot/blocks/lockdownbrowser/locklib.php";
if (is_readable($lockdownbrowser_locklib_file)) {
    require_once($lockdownbrowser_locklib_file);
} else {
    lockdownbrowser_monitorserviceerror(2033, "locklib.php not found");
}

if (!empty($CFG->maintenance_enabled)
    || file_exists($CFG->dataroot . "/" . SITEID . "/maintenance.html")
) {
    lockdownbrowser_monitorserviceerror(2002, "The Moodle site is currently undergoing maintenance");
}

raise_memory_limit(MEMORY_EXTRA);

/*** Use this if we exceed the default script time limit.
set_time_limit(300);
***/

set_exception_handler("lockdownbrowser_monitorexceptionhandler");

lockdownbrowser_monitorservicerequest();

exit;

function lockdownbrowser_monitorserviceerror($code = "", $message = "", $encrypt = true) {

    if (empty($code)) {
        $code    = "2000";
        $message = "Unspecified error";
    }

    $body = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
    $body .= "<service_error>\r\n";

    $body .= "\t<code>";
    $body .= utf8_encode(htmlspecialchars(trim($code)));
    $body .= "</code>\r\n";

    if (empty($message)) {
        $body .= "\t<message />\r\n";
    } else {
        $body .= "\t<message>";
        $body .= utf8_encode(htmlspecialchars(trim($message)));
        $body .= "</message>\r\n";
    }

    $body .= "</service_error>\r\n";

    lockdownbrowser_monitorserviceresponse("text/xml", $body, $encrypt);
}

function lockdownbrowser_monitorservicestatus($code = "", $message = "") {

    if (empty($code)) {
        $code    = "1000";
        $message = "Unspecified status";
    }

    $body = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
    $body .= "<service_status>\r\n";

    $body .= "\t<code>";
    $body .= utf8_encode(htmlspecialchars(trim($code)));
    $body .= "</code>\r\n";

    if (empty($message)) {
        $body .= "\t<message />\r\n";
    } else {
        $body .= "\t<message>";
        $body .= utf8_encode(htmlspecialchars(trim($message)));
        $body .= "</message>\r\n";
    }

    $body .= "</service_status>\r\n";

    lockdownbrowser_monitorserviceresponse("text/xml", $body, true);
}

function lockdownbrowser_monitorserviceresponse($content_type, $body, $encrypt, $log = true) {

    if ($log) {
        lockdownbrowser_monitorlog("service response: " . $body);
    }

    header("Cache-Control: private, must-revalidate");
    header("Expires: -1");
    header("Pragma: no-cache");

    if ($encrypt === true) {
        $encrypted = lockdownbrowser_monitorbase64encrypt($body, true);
        if (is_null($encrypted)) {
            header("Content-Type: $content_type");
            echo $body;
        } else {
            header("Content-Type: text/html"); // needed for IE client
            $url_encoded = urlencode($encrypted);
            echo $url_encoded;
        }
    } else {
        header("Content-Type: $content_type");
        echo $body;
    }

    exit;
}

function lockdownbrowser_monitorcourselistresponse($courses) {

    $body = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";

    if (empty($courses)) {
        $body .= "<courseList />\r\n";
        lockdownbrowser_monitorserviceresponse("text/xml", $body, true);
    }

    $body .= "<courseList>\r\n";

    foreach ($courses as $c) {
        $body .= "\t<course>\r\n";

        $body .= "\t\t<courseRefId>";
        $body .= utf8_encode(htmlspecialchars(trim($c->id)));
        $body .= "</courseRefId>\r\n";

        $body .= "\t\t<courseId>";
        $body .= utf8_encode(htmlspecialchars(trim($c->shortname)));
        $body .= "</courseId>\r\n";

        $body .= "\t\t<courseDescription>";
        $body .= utf8_encode(htmlspecialchars(trim($c->fullname)));
        $body .= "</courseDescription>\r\n";

        $body .= "\t</course>\r\n";
    }

    $body .= "</courseList>\r\n";

    lockdownbrowser_monitorserviceresponse("text/xml", $body, true);
}

function lockdownbrowser_monitorfloatcompare($f1, $f2, $precision) {

    if (function_exists("bccomp")) {
        return bccomp($f1, $f2, $precision);
    }

    if ($precision < 0) {
        $precision = 0;
    }

    $epsilon = 1 / pow(10, $precision);
    $diff    = ($f1 - $f2);

    if (abs($diff) < $epsilon) {
        return 0;
    } else if ($diff < 0) {
        return -1;
    } else {
        return 1;
    }
}

function lockdownbrowser_monitortemppath() {

    global $CFG;

    if (lockdownbrowser_monitorfloatcompare(
            $CFG->version, 2011120500.00, 2) >= 0
    ) {
        // Moodle 2.2.0+
        if (isset($CFG->tempdir)) {
            $path = "$CFG->tempdir";
        } else {
            $path = "$CFG->dataroot/temp";
        }
    } else {
        // Moodle 2.0.x - 2.1.x
        $path = "$CFG->dataroot/temp";
    }

    return $path;
}

function lockdownbrowser_monitorlog($msg) {

    global $lockdownbrowser_monitor_enable_log;

    if ($lockdownbrowser_monitor_enable_log) {
        $entry  = date("m-d-Y H:i:s") . " - " . $msg . "\r\n";
        $path   = lockdownbrowser_monitortemppath()
            . "/" . LOCKDOWNBROWSER_MONITOR_LOG;
        $handle = fopen($path, "ab");
        if ($handle !== false) {
            fwrite($handle, $entry, strlen($entry));
            fclose($handle);
        }
    }
}

function lockdownbrowser_monitorexceptionhandler($ex) {

    abort_all_db_transactions();

    $info = get_exception_info($ex);

    $msg = "\r\n-- Exception occurred --"
        . "\r\nmessage: $info->message"
        . "\r\nerrorcode: $info->errorcode"
        . "\r\nbacktrace: $info->backtrace"
        . "\r\nlink: $info->link"
        . "\r\nmoreinfourl: $info->moreinfourl"
        . "\r\na: $info->a"
        . "\r\ndebuginfo: $info->debuginfo\r\n";

    lockdownbrowser_monitorlog($msg);
    lockdownbrowser_monitorlog("\r\nstacktrace: " . $ex->getTraceAsString());

    lockdownbrowser_monitorserviceerror(2003, "A Moodle or PHP server exception occurred: $info->errorcode");
}

function lockdownbrowser_monitorrequestparameters() {

    global $lockdownbrowser_monitor_enable_log;
    global $lockdownbrowser_monitor_enable_phpinfo;

    $parameters     = array();
    $request_method = $_SERVER["REQUEST_METHOD"];

    if ($request_method == "GET") {

        if (!isset($_GET["rp"])) { // direct access only for existence check
            lockdownbrowser_monitorserviceerror(2012, "No request parameters found");
        }

        $cleaned = optional_param("rp", false, PARAM_ALPHANUMEXT);
        if ($cleaned == "ping") { // unencrypted presence check
            lockdownbrowser_monitorserviceresponse("text/plain", "OK", false, false);
        }
        if ($cleaned == "log") { // get debug log contents
            if ($lockdownbrowser_monitor_enable_log) {
                $path = lockdownbrowser_monitortemppath() . "/" . LOCKDOWNBROWSER_MONITOR_LOG;
                $log = file_get_contents($path);
                if ($log === false) {
                    $log = "Cannot read log file: $path";
                }
                lockdownbrowser_monitorserviceresponse("text/plain", $log, false, false);
            }
        }
        if ($cleaned == "phpinfo") { // get PHP info
            if ($lockdownbrowser_monitor_enable_phpinfo) {
                phpinfo();
                exit;
            }
        }

        $cleaned = optional_param("rp", false, PARAM_NOTAGS); // cannot use PARAM_BASE64
        if ($cleaned === false) {
            lockdownbrowser_monitorserviceerror(2012, "No request parameters found");
        }
    } else if ($request_method == "POST") {

        if (isset($_POST["rp"])) { // direct access only for existence check

            $cleaned = optional_param("rp", false, PARAM_ALPHANUMEXT);
            if ($cleaned == "ping") { // unencrypted presence check
                lockdownbrowser_monitorserviceresponse("text/plain", "OK", false, false);
            }
            if ($cleaned == "log") { // get debug log contents
                if ($lockdownbrowser_monitor_enable_log) {
                    $path = lockdownbrowser_monitortemppath() . "/" . LOCKDOWNBROWSER_MONITOR_LOG;
                    $log = file_get_contents($path);
                    if ($log === false) {
                        $log = "Cannot read log file: $path";
                    }
                    lockdownbrowser_monitorserviceresponse("text/plain", $log, false, false);
                }
            }

            $cleaned = optional_param("rp", false, PARAM_NOTAGS); // cannot use PARAM_BASE64
            if ($cleaned === false) {
                lockdownbrowser_monitorserviceerror(2012, "No request parameters found");
            }
        } else { // direct access only for length check and url-decoding

            $body = file_get_contents("php://input");
            if (strlen($body) == 0) {
                lockdownbrowser_monitorserviceerror(2012, "No request parameters found");
            }

            $decoded = urldecode($body);

            $cleaned = clean_param($decoded, false, PARAM_ALPHANUMEXT);
            if ($cleaned == "ping") { // unencrypted presence check
                lockdownbrowser_monitorserviceresponse("text/plain", "OK", false, false);
            }
            if ($cleaned == "log") { // get debug log contents
                if ($lockdownbrowser_monitor_enable_log) {
                    $path = lockdownbrowser_monitortemppath() . "/" . LOCKDOWNBROWSER_MONITOR_LOG;
                    $log = file_get_contents($path);
                    if ($log === false) {
                        $log = "Cannot read log file: $path";
                    }
                    lockdownbrowser_monitorserviceresponse("text/plain", $log, false, false);
                }
            }

            $cleaned = clean_param($decoded, PARAM_NOTAGS); // cannot use PARAM_BASE64
            if ($cleaned === false) {
                lockdownbrowser_monitorserviceerror(2012, "No request parameters found");
            }
        }
    } else {
        lockdownbrowser_monitorserviceerror(2017, "Unsupported request method: $request_method");
    }

    // parse encrypted parameters
    $decrypted = lockdownbrowser_monitorbase64decrypt($cleaned, false);
    lockdownbrowser_monitorlog("service request: " . $decrypted);
    $nvpairs = explode("&", $decrypted);
    foreach ($nvpairs as $pair) {
        $parts = explode("=", $pair);
        $name  = urldecode($parts[0]);
        if (count($parts) == 2) {
            $value = urldecode($parts[1]);
        } else {
            $value = "";
        }
        $parameters[$name] = $value;
    }

    // check mac
    $pos = strpos($decrypted, "&mac=");
    if ($pos === false
        || !isset($parameters["mac"])
        || strlen($parameters["mac"]) == 0
    ) {
        lockdownbrowser_monitorserviceerror(2011, "MAC not found in request");
    }
    $sign = substr($decrypted, 0, $pos);
    $mac  = lockdownbrowser_monitorgeneratemac($sign);
    if (strcmp($mac, $parameters["mac"]) != 0) {
        lockdownbrowser_monitorserviceerror(2010, "Invalid MAC in request");
    }

    return $parameters;
}

function lockdownbrowser_monitorgeneratemac($input) {

    $secret = lockdownbrowser_monitorsharedsecret(false);

    $chararray = preg_split('//', $input, -1, PREG_SPLIT_NO_EMPTY);

    $strdatavalue = 0;
    foreach ($chararray as $char) {
        $strdatavalue += ord($char);
    }

    return md5($strdatavalue . $secret);
}

function lockdownbrowser_monitorbase64encrypt($input, $silent) {

    if (!extension_loaded("mcrypt")) {
        if ($silent === false) {
            lockdownbrowser_monitorserviceerror(2008, "The mcrypt library is not loaded", false);
        } else {
            return null;
        }
    }

    $secret = lockdownbrowser_monitorsharedsecret($silent);
    if (is_null($secret)) {
        return null;
    }

    $encrypted   = mcrypt_encrypt(MCRYPT_BLOWFISH, $secret, $input, MCRYPT_MODE_ECB);
    $b64_encoded = base64_encode($encrypted);

    return $b64_encoded;
}

function lockdownbrowser_monitorbase64decrypt($input, $silent) {

    $b64_decoded = base64_decode($input, true);

    if ($b64_decoded === false) {
        if ($silent === false) {
            lockdownbrowser_monitorserviceerror(2007, "Invalid base64 encoding of input data");
        } else {
            return null;
        }
    }
    if (!extension_loaded("mcrypt")) {
        if ($silent === false) {
            lockdownbrowser_monitorserviceerror(2008, "The mcrypt library is not loaded");
        } else {
            return null;
        }
    }

    $secret = lockdownbrowser_monitorsharedsecret($silent);
    if (is_null($secret)) {
        return null;
    }

    $decrypted = mcrypt_decrypt(MCRYPT_BLOWFISH, $secret, $b64_decoded, MCRYPT_MODE_ECB);
    return trim($decrypted);
}

function lockdownbrowser_monitorsharedsecret($silent) {

    global $CFG;

    if (!isset($CFG->block_lockdownbrowser_ldb_serversecret)
        || strlen($CFG->block_lockdownbrowser_ldb_serversecret) == 0
    ) {
        if ($silent === false) {
            lockdownbrowser_monitorserviceerror(2009, "Shared secret not found in settings", false);
        } else {
            return null;
        }
    }

    $secret = $CFG->block_lockdownbrowser_ldb_serversecret;

    return $secret;
}

function lockdownbrowser_monitorredeemtoken($parameters) {

    global $CFG;

    if (!isset($parameters["token"]) || strlen($parameters["token"]) == 0) {
        lockdownbrowser_monitorserviceerror(2018, "Login token not found in request");
    }
    $token = $parameters["token"];

    if (!isset($CFG->block_lockdownbrowser_ldb_serverid)
        || strlen($CFG->block_lockdownbrowser_ldb_serverid) == 0
    ) {
        lockdownbrowser_monitorserviceerror(2019, "Institution ID not found in settings");
    }
    $institution_id = $CFG->block_lockdownbrowser_ldb_serverid;

    if (!isset($CFG->block_lockdownbrowser_ldb_servername)
        || strlen($CFG->block_lockdownbrowser_ldb_servername) == 0
    ) {
        lockdownbrowser_monitorserviceerror(2037, "Server name not found in settings");
    }
    $server_name = $CFG->block_lockdownbrowser_ldb_servername;

    $redeem_time = time();
    $redeem_mac  = lockdownbrowser_monitorgeneratemac(
        urldecode($institution_id) . urldecode($server_name) . $token . $redeem_time
    );

    // we assume https, so no additional encryption is used

    $url = LOCKDOWNBROWSER_MONITOR_REDEEMURL
        . "?institutionId=" . $institution_id // assume url-encoded
        . "&serverName=" . $server_name // assume url-encoded
        . "&token=" . urlencode($token)
        . "&time=" . urlencode($redeem_time)
        . "&mac=" . urlencode($redeem_mac);

    if (!extension_loaded("curl")) {
        lockdownbrowser_monitorserviceerror(2020, "The curl library is not loaded");
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    $info   = curl_getinfo($ch);
    curl_close($ch);

    if ($result == false || ($info["http_code"] != 200)) {
        lockdownbrowser_monitorserviceerror(2021, "Could not redeem login token");
    }

    $receipt_mac = lockdownbrowser_monitorgeneratemac(
        $token . urldecode($server_name) . urldecode($institution_id) . $redeem_time
    );
    if (strcmp($result, $receipt_mac) != 0) {
        lockdownbrowser_monitorserviceerror(2022, "Received invalid token receipt");
    }
}

function lockdownbrowser_monitoractionlogin($parameters) {

    global $CFG;
    global $lockdownbrowser_ignore_https_login;
    global $lockdownbrowser_disable_callbacks;

    if (isloggedin()) {
        lockdownbrowser_monitorserviceerror(2015, "Session is already logged in");
    }

    if (!$lockdownbrowser_disable_callbacks) {
        lockdownbrowser_monitorredeemtoken($parameters);
    }

    if (!$lockdownbrowser_ignore_https_login) {
        if ($CFG->loginhttps && !$CFG->sslproxy) {
            if (!isset($_SERVER["HTTPS"])
                || empty($_SERVER["HTTPS"])
                || strcasecmp($_SERVER["HTTPS"], "off") == 0
            ) {
                lockdownbrowser_monitorserviceerror(2016, "HTTPS is required");
            }
        }
    }

    if (!isset($CFG->block_lockdownbrowser_monitor_username)
        || strlen($CFG->block_lockdownbrowser_monitor_username) == 0
        || !isset($CFG->block_lockdownbrowser_monitor_password)
        || strlen($CFG->block_lockdownbrowser_monitor_password) == 0
    ) {
        lockdownbrowser_monitorserviceerror(2014, "Login info not found in settings");
    }

    $user = authenticate_user_login(
        $CFG->block_lockdownbrowser_monitor_username,
        $CFG->block_lockdownbrowser_monitor_password
    );
    if ($user) {
        complete_user_login($user);
    }

    if (!isloggedin()) {
        lockdownbrowser_monitorserviceerror(2013, "Login attempt failed");
    }

    lockdownbrowser_monitorservicestatus(1002, "Login succeeded");
}

function lockdownbrowser_monitoractionuserlogin($parameters) {

    global $CFG;
    global $lockdownbrowser_ignore_https_login;

    if (isloggedin()) {
        lockdownbrowser_monitorserviceerror(2015, "Session is already logged in");
    }
    if (!isset($parameters["username"]) || strlen($parameters["username"]) == 0) {
        lockdownbrowser_monitorserviceerror(2031, "No username was specified");
    }
    if (!isset($parameters["password"]) || strlen($parameters["password"]) == 0) {
        lockdownbrowser_monitorserviceerror(2032, "No password was specified");
    }

    $username = $parameters["username"];
    $password = $parameters["password"];

    if (!$lockdownbrowser_ignore_https_login) {
        if ($CFG->loginhttps && !$CFG->sslproxy) {
            if (!isset($_SERVER["HTTPS"])
                || empty($_SERVER["HTTPS"])
                || strcasecmp($_SERVER["HTTPS"], "off") == 0
            ) {
                lockdownbrowser_monitorserviceerror(2016, "HTTPS is required");
            }
        }
    }

    $user = authenticate_user_login($username, $password);
    if ($user) {
        complete_user_login($user);
    }

    if (!isloggedin()) {
        lockdownbrowser_monitorserviceerror(2013, "Login attempt failed");
    }

    lockdownbrowser_monitorservicestatus(1002, "Login succeeded");
}

function lockdownbrowser_monitoractionlogout($parameters) {

    if (!isloggedin()) {
        lockdownbrowser_monitorserviceerror(2004, "Must be logged in to perform the requested action");
    }

    require_logout();

    lockdownbrowser_monitorservicestatus(1001, "Logout succeeded");
}

function lockdownbrowser_monitoractioncourselist($parameters) {

    if (!isloggedin()) {
        lockdownbrowser_monitorserviceerror(2004, "Must be logged in to perform the requested action");
    }
    if (!is_siteadmin()) {
        lockdownbrowser_monitorserviceerror(2024, "Must be logged in as admin to perform the requested action");
    }

    $courses = get_courses();
    if ($courses === false) {
        $courses = array();
    }

    $c2 = array();
    foreach ($courses as $c) {
        if ($c->id != SITEID) {
            $c2[] = $c;
        }
    }
    $courses = $c2;

    lockdownbrowser_monitorcourselistresponse($courses);
}

function lockdownbrowser_monitoractionchangesettings($parameters) {

    global $DB;

    if (!isloggedin()) {
        lockdownbrowser_monitorserviceerror(2004, "Must be logged in to perform the requested action");
    }
    if (!is_siteadmin()) {
        lockdownbrowser_monitorserviceerror(2024, "Must be logged in as admin to perform the requested action");
    }
    if (!isset($parameters["courseRefId"]) || strlen($parameters["courseRefId"]) == 0) {
        lockdownbrowser_monitorserviceerror(2025, "No courseRefId parameter was specified");
    }
    if (!isset($parameters["examId"]) || strlen($parameters["examId"]) == 0) {
        lockdownbrowser_monitorserviceerror(2026, "No examId parameter was specified");
    }
    if (!isset($parameters["enableLDB"]) || strlen($parameters["enableLDB"]) == 0) {
        lockdownbrowser_monitorserviceerror(2040, "No enableLDB parameter was specified");
    }
    if (!isset($parameters["enableMonitor"]) || strlen($parameters["enableMonitor"]) == 0) {
        lockdownbrowser_monitorserviceerror(2041, "No enableMonitor parameter was specified");
    }
    if (!isset($parameters["exitPassword"])) {
        lockdownbrowser_monitorserviceerror(2042, "No exitPassword parameter was specified");
    }
    if (!isset($parameters["xdata"])) {
        lockdownbrowser_monitorserviceerror(2043, "No xdata parameter was specified");
    }

    $course_id = intval($parameters["courseRefId"]);
    $exam_id   = intval($parameters["examId"]);

    $enable_ldb = $parameters["enableLDB"];
    if ($enable_ldb == "0" || strcasecmp($enable_ldb, "false") == 0) {
        $enable_ldb = false;
    } else {
        $enable_ldb = true;
    }

    $enable_monitor = $parameters["enableMonitor"];
    if ($enable_monitor == "0" || strcasecmp($enable_monitor, "false") == 0) {
        $enable_monitor = false;
    } else {
        $enable_monitor = true;
    }

    $exit_password = $parameters["exitPassword"];
    $xdata         = $parameters["xdata"];

    if ($enable_monitor) {
        $monitor = $xdata;
    } else {
        $monitor = "";
    }

    $course_module = $DB->get_record("course_modules", array("id" => $exam_id));
    if ($course_module === false) {
        lockdownbrowser_monitorserviceerror(2027, "The specified examId is invalid: $exam_id");
    }

    $modrec = $DB->get_record("modules", array("id" => $course_module->module));
    if ($modrec === false) {
        lockdownbrowser_monitorserviceerror(2034, "Could not find the specified quiz (module error)");
    }

    $quiz = $DB->get_record($modrec->name, array("id" => $course_module->instance));
    if ($quiz === false) {
        lockdownbrowser_monitorserviceerror(2035, "Could not find the specified quiz (instance error)");
    }

    // Moodle browser security
    //   popup (0=none, 1=full screen pop-up with some JavaScript security)
    // Moodle 2.2.0+ (quiz module 2011100600+)
    //   browsersecurity ('-', 'securewindow', 'safebrowser')
    // if this setting is not disabled, it will interfere with the LDB integration
    if ($enable_ldb) {
        $quiz->popup           = 0;
        $quiz->browsersecurity = "-";
    }

    $ldb_decoration     = get_string("requires_ldb", "block_lockdownbrowser");
    $monitor_decoration = get_string("requires_webcam", "block_lockdownbrowser");

    // must be in this order, since the first decoration usually contains the second
    $quiz->name = str_replace($monitor_decoration, "", $quiz->name);
    $quiz->name = str_replace($ldb_decoration, "", $quiz->name);

    if ($enable_ldb) {
        if ($enable_monitor) {
            $quiz->name .= $monitor_decoration;
        } else {
            $quiz->name .= $ldb_decoration;
        }
    }

    $settings = lockdownbrowser_get_quiz_options($quiz->id);

    if ($settings === false) {

        if ($enable_ldb) {
            $ok = lockdownbrowser_set_settings($quiz->id, 0, 0, $exit_password, $monitor);
            if (!$ok) {
                lockdownbrowser_monitorserviceerror(2036, "Quiz settings changes failed (block error)");
            }
        }
    } else { // settings found

        if ($enable_ldb) {
            $settings->password = $exit_password;
            $settings->monitor  = $monitor;
            $ok                 = lockdownbrowser_set_quiz_options($quiz->id, $settings);
            if (!$ok) {
                lockdownbrowser_monitorserviceerror(2036, "Quiz settings changes failed (block error)");
            }
        } else {
            lockdownbrowser_delete_options($quiz->id);
        }
    }

    $ok = $DB->update_record($modrec->name, $quiz);
    if (!$ok) {
        lockdownbrowser_monitorserviceerror(2036, "Quiz settings changes failed (module error)");
    }

    rebuild_course_cache($course_id);
    lockdownbrowser_monitorservicestatus(1003, "Quiz settings changes succeeded");
}

function lockdownbrowser_monitoractionexamroster($parameters) {

    global $DB;

    if (!isloggedin()) {
        lockdownbrowser_monitorserviceerror(2004, "Must be logged in to perform the requested action");
    }
    if (!is_siteadmin()) {
        lockdownbrowser_monitorserviceerror(2024, "Must be logged in as admin to perform the requested action");
    }
    if (!isset($parameters["courseRefId"]) || strlen($parameters["courseRefId"]) == 0) {
        lockdownbrowser_monitorserviceerror(2025, "No courseRefId parameter was specified");
    }
    if (!isset($parameters["examId"]) || strlen($parameters["examId"]) == 0) {
        lockdownbrowser_monitorserviceerror(2026, "No examId parameter was specified");
    }

    $course_id = intval($parameters["courseRefId"]);
    $exam_id   = intval($parameters["examId"]);

    $course_module = $DB->get_record("course_modules", array("id" => $exam_id));
    if ($course_module === false) {
        lockdownbrowser_monitorserviceerror(2027, "The specified examId is invalid: $exam_id");
    }
    $quiz_id = $course_module->instance;

    $context = get_context_instance(CONTEXT_COURSE, $course_id);
    if ($context === false) {
        lockdownbrowser_monitorserviceerror(2028, "The specified courseRefId is invalid: $course_id");
    }

    $roles = $DB->get_records("role", array("archetype" => "student"));
    if ($roles === false || count($roles) == 0) {
        lockdownbrowser_monitorserviceerror(2029, "The role archetype 'student' was not found");
    }

    $students = array();
    foreach ($roles as $role) {
        $users = get_role_users($role->id, $context);
        if ($users !== false && count($users) > 0) {
            $students = array_merge($students, $users);
        }
    }

    $body = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";

    if ($students === false || count($students) == 0) {
        $body .= "<studentList />\r\n";
        lockdownbrowser_monitorserviceresponse("text/xml", $body, true);
    }

    $body .= "<studentList>\r\n";

    foreach ($students as $s) {
        $body .= "\t<student>\r\n";

        $body .= "\t\t<userName>";
        $body .= utf8_encode(htmlspecialchars(trim($s->username)));
        $body .= "</userName>\r\n";

        $body .= "\t\t<firstName>";
        $body .= utf8_encode(htmlspecialchars(trim($s->firstname)));
        $body .= "</firstName>\r\n";

        $body .= "\t\t<lastName>";
        $body .= utf8_encode(htmlspecialchars(trim($s->lastname)));
        $body .= "</lastName>\r\n";

        $grade_info = grade_get_grades(
            $course_id, "mod", "quiz", $quiz_id, $s->id
        );
        if (!empty($grade_info)
            && !empty($grade_info->items)
            && !empty($grade_info->items[0]->grades)
            && !empty($grade_info->items[0]->grades[$s->id])
            && !empty($grade_info->items[0]->grades[$s->id]->grade)
        ) {
            $grade = $grade_info->items[0]->grades[$s->id]->str_grade;
            $body .= "\t\t<grade>";
            $body .= utf8_encode(htmlspecialchars(trim($grade)));
            $body .= "</grade>\r\n";
        }

        $body .= "\t</student>\r\n";
    }

    $body .= "</studentList>\r\n";

    lockdownbrowser_monitorserviceresponse("text/xml", $body, true);
}

function lockdownbrowser_monitoractionuserinfo2($parameters) {

    global $USER;

    if (!isloggedin()) {
        lockdownbrowser_monitorserviceerror(2004, "Must be logged in to perform the requested action");
    }

    $body = $USER->username . "\$%\$"
        . $USER->lastname . "\$%\$"
        . $USER->firstname;

    lockdownbrowser_monitorserviceresponse("text/plain", $body, true);
}

function lockdownbrowser_monitoractionusercourselist($parameters) {

    if (!isloggedin()) {
        lockdownbrowser_monitorserviceerror(2004, "Must be logged in to perform the requested action");
    }

    $courses = enrol_get_my_courses();
    if ($courses === false) {
        $courses = array();
    }

    $c2 = array();
    foreach ($courses as $c) {
        if ($c->id != SITEID) {
            $c2[] = $c;
        }
    }
    $courses = $c2;

    lockdownbrowser_monitorcourselistresponse($courses);
}

function lockdownbrowser_monitoractionexaminfo2($parameters) {

    global $DB;

    // login not required

    if (!isset($parameters["courseRefId"]) || strlen($parameters["courseRefId"]) == 0) {
        lockdownbrowser_monitorserviceerror(2025, "No courseRefId parameter was specified");
    }
    if (!isset($parameters["examId"]) || strlen($parameters["examId"]) == 0) {
        lockdownbrowser_monitorserviceerror(2026, "No examId parameter was specified");
    }

    $course_id = intval($parameters["courseRefId"]);
    $exam_id   = intval($parameters["examId"]);

    $course_module = $DB->get_record("course_modules", array("id" => $exam_id));
    if ($course_module === false) {
        lockdownbrowser_monitorserviceerror(2027, "The specified examId is invalid: $exam_id");
    }

    $modrec = $DB->get_record("modules", array("id" => $course_module->module));
    if ($modrec === false) {
        lockdownbrowser_monitorserviceerror(2034, "Could not find the specified quiz (module error)");
    }

    $quiz = $DB->get_record($modrec->name, array("id" => $course_module->instance));
    if ($quiz === false) {
        lockdownbrowser_monitorserviceerror(2035, "Could not find the specified quiz (instance error)");
    }

    $settings = lockdownbrowser_get_quiz_options($quiz->id);

    if ($settings === false
        || !isset($settings->password)
        || is_null($settings->password)
        || strlen($settings->password) == 0
    ) {
        $exit_pass_exists = "N";
        $exit_password    = "";
    } else {
        $exit_pass_exists = "Y";
        $exit_password    = $settings->password;
    }

    $body = "NONE\$:\$N\$:\$"
        . $exit_pass_exists
        . "\$:\$"
        . $exit_password
        . "\$:\$N\$:\$\$:\$"
        . $quiz->name;

    lockdownbrowser_monitorserviceresponse("text/plain", $body, true);
}

function lockdownbrowser_monitoractionexamsync($parameters) {

    global $DB;

    if (!isloggedin()) {
        lockdownbrowser_monitorserviceerror(2004, "Must be logged in to perform the requested action");
    }
    if (!is_siteadmin()) {
        lockdownbrowser_monitorserviceerror(2024, "Must be logged in as admin to perform the requested action");
    }
    if (!isset($parameters["courseRefId"]) || strlen($parameters["courseRefId"]) == 0) {
        lockdownbrowser_monitorserviceerror(2025, "No courseRefId parameter was specified");
    }

    $course_id = intval($parameters["courseRefId"]);

    $coursemodules = get_coursemodules_in_course("quiz", $course_id);
    if ($coursemodules === false) {
        $coursemodules = array();
    }

    $body = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";

    if (empty($coursemodules)) {
        $body .= "<assessmentList />\r\n";
        lockdownbrowser_monitorserviceresponse("text/xml", $body, true);
    }

    $body .= "<assessmentList>\r\n";

    foreach ($coursemodules as $cm) {

        $modrec = $DB->get_record("modules", array("id" => $cm->module));
        if ($modrec === false) {
            continue;
        }

        $quiz = $DB->get_record($modrec->name, array("id" => $cm->instance));
        if ($quiz === false) {
            continue;
        }

        $body .= "\t<assessment>\r\n";

        $body .= "\t\t<id>";
        $body .= utf8_encode(htmlspecialchars(trim($cm->id)));
        $body .= "</id>\r\n";

        $body .= "\t\t<title>";
        $body .= utf8_encode(htmlspecialchars(trim($cm->name)));
        $body .= "</title>\r\n";

        $settings = lockdownbrowser_get_quiz_options($cm->instance);

        if ($settings !== false) {
            $body .= "\t\t<ldbEnabled>true</ldbEnabled>\r\n";
        } else {
            $body .= "\t\t<ldbEnabled>false</ldbEnabled>\r\n";
        }

        if ($settings !== false
            && isset($settings->password)
            && !is_null($settings->password)
            && strlen($settings->password) > 0
        ) {
            $body .= "\t\t<exitPassword>";
            $body .= utf8_encode(htmlspecialchars($settings->password));
            $body .= "</exitPassword>\r\n";
        }

        if ($settings !== false
            && isset($settings->monitor)
            && !is_null($settings->monitor)
            && strlen($settings->monitor) > 0
        ) {
            $body .= "\t\t<monitorEnabled>true</monitorEnabled>\r\n";
            $body .= "\t\t<extendedData>";
            $body .= utf8_encode(htmlspecialchars($settings->monitor));
            $body .= "</extendedData>\r\n";
        } else {
            $body .= "\t\t<monitorEnabled>false</monitorEnabled>\r\n";
        }

        // Moodle browser security
        //   popup (0=none, 1=full screen pop-up with some JavaScript security)
        // Moodle 2.2.0+ (quiz module 2011100600+)
        //   browsersecurity ('-', 'securewindow', 'safebrowser')
        // if this setting is not disabled, it will interfere with the LDB integration
        if (isset($quiz->browsersecurity)) {
            if ($quiz->browsersecurity != "-") {
                $launch_in_new_window = true;
            } else {
                $launch_in_new_window = false;
            }
        } else {
            if ($quiz->popup != 0) {
                $launch_in_new_window = true;
            } else {
                $launch_in_new_window = false;
            }
        }

        if ($launch_in_new_window) {
            $body .= "\t\t<launchInNewWindow>true</launchInNewWindow>\r\n";
        } else {
            $body .= "\t\t<launchInNewWindow>false</launchInNewWindow>\r\n";
        }

        if ($settings !== false && $launch_in_new_window) {
            $body .= "\t\t<ok>false</ok>\r\n";
        } else {
            $body .= "\t\t<ok>true</ok>\r\n";
        }

        $body .= "\t</assessment>\r\n";
    }

    $body .= "</assessmentList>\r\n";

    lockdownbrowser_monitorserviceresponse("text/xml", $body, true);
}

function lockdownbrowser_monitoractionversioninfo($parameters) {

    global $CFG;

    if (!isloggedin()) {
        lockdownbrowser_monitorserviceerror(2004, "Must be logged in to perform the requested action");
    }

    $moodle_release = $CFG->release;
    $moodle_version = $CFG->version;

    $version_file = "$CFG->dirroot/blocks/lockdownbrowser/version.php";
    if (is_readable($version_file)) {
        include($version_file);
    } else {
        lockdownbrowser_monitorserviceerror(2038, "Block version file not found");
    }

    if (!isset($plugin->version)) {
        lockdownbrowser_monitorserviceerror(2039, "Block version info missing");
    }

    $block_version = $plugin->version;

    $body = $moodle_release . "\$%\$" . $moodle_version . "\$%\$" . $block_version;

    lockdownbrowser_monitorserviceresponse("text/plain", $body, true);
}

function lockdownbrowser_monitoractionusercourserole($parameters) {

    global $DB;

    if (!isloggedin()) {
        lockdownbrowser_monitorserviceerror(2004, "Must be logged in to perform the requested action");
    }
    if (!is_siteadmin()) {
        lockdownbrowser_monitorserviceerror(2024, "Must be logged in as admin to perform the requested action");
    }
    if (!isset($parameters["courseRefId"]) || strlen($parameters["courseRefId"]) == 0) {
        lockdownbrowser_monitorserviceerror(2025, "No courseRefId parameter was specified");
    }
    if (!isset($parameters["userId"]) || strlen($parameters["userId"]) == 0) {
        lockdownbrowser_monitorserviceerror(2044, "No userId parameter was specified");
    }

    $course_id = intval($parameters["courseRefId"]);
    $username  = $parameters["userId"]; // actually user login name

    $context = get_context_instance(CONTEXT_COURSE, $course_id);
    if ($context === false) {
        lockdownbrowser_monitorserviceerror(2028, "The specified courseRefId is invalid: $course_id");
    }

    $body = "";

    if (strlen($body) == 0) { // check managers
        $managers = array();
        $roles    = $DB->get_records("role", array("archetype" => "manager"));
        if ($roles === false || count($roles) == 0) {
            lockdownbrowser_monitorserviceerror(2045, "The role archetype 'manager' was not found");
        }
        foreach ($roles as $role) {
            $users = get_role_users($role->id, $context);
            if ($users !== false && count($users) > 0) {
                $managers = array_merge($managers, $users);
            }
        }
        if (count($managers) > 0) {
            foreach ($managers as $m) {
                if (strcasecmp($username, $m->username) == 0) {
                    $body = "ADMIN";
                    break;
                }
            }
        }
    }

    if (strlen($body) == 0) { // check editing teachers
        $editingteachers = array();
        $roles           = $DB->get_records("role", array("archetype" => "editingteacher"));
        if ($roles === false || count($roles) == 0) {
            lockdownbrowser_monitorserviceerror(2047, "The role archetype 'editingteacher' was not found");
        }
        foreach ($roles as $role) {
            $users = get_role_users($role->id, $context);
            if ($users !== false && count($users) > 0) {
                $editingteachers = array_merge($editingteachers, $users);
            }
        }
        if (count($editingteachers) > 0) {
            foreach ($editingteachers as $et) {
                if (strcasecmp($username, $et->username) == 0) {
                    $body = "INSTRUCTOR";
                    break;
                }
            }
        }
    }

    if (strlen($body) == 0) { // check non-editing teachers
        $teachers = array();
        $roles    = $DB->get_records("role", array("archetype" => "teacher"));
        if ($roles === false || count($roles) == 0) {
            lockdownbrowser_monitorserviceerror(2048, "The role archetype 'teacher' was not found");
        }
        foreach ($roles as $role) {
            $users = get_role_users($role->id, $context);
            if ($users !== false && count($users) > 0) {
                $teachers = array_merge($teachers, $users);
            }
        }
        if (count($teachers) > 0) {
            foreach ($teachers as $t) {
                if (strcasecmp($username, $t->username) == 0) {
                    $body = "STUDENT";
                    break;
                }
            }
        }
    }

    if (strlen($body) == 0) { // check students
        $students = array();
        $roles    = $DB->get_records("role", array("archetype" => "student"));
        if ($roles === false || count($roles) == 0) {
            lockdownbrowser_monitorserviceerror(2029, "The role archetype 'student' was not found");
        }
        foreach ($roles as $role) {
            $users = get_role_users($role->id, $context);
            if ($users !== false && count($users) > 0) {
                $students = array_merge($students, $users);
            }
        }
        if (count($students) > 0) {
            foreach ($students as $s) {
                if (strcasecmp($username, $s->username) == 0) {
                    $body = "STUDENT";
                    break;
                }
            }
        }
    }

    if (strlen($body) == 0) {
        lockdownbrowser_monitorserviceerror(2049,
          "The specified userId does not have at least STUDENT access to the specified course.");
    }

    lockdownbrowser_monitorserviceresponse("text/plain", $body, true);
}

function lockdownbrowser_monitoractionretrievecourse($parameters) {

    global $DB;

    if (!isloggedin()) {
        lockdownbrowser_monitorserviceerror(2004, "Must be logged in to perform the requested action");
    }
    if (!is_siteadmin()) {
        lockdownbrowser_monitorserviceerror(2024, "Must be logged in as admin to perform the requested action");
    }
    if (!isset($parameters["courseRefId"]) || strlen($parameters["courseRefId"]) == 0) {
        lockdownbrowser_monitorserviceerror(2025, "No courseRefId parameter was specified");
    }

    $course_id = intval($parameters["courseRefId"]);

    $record = $DB->get_record("course", array("id" => $course_id));
    if ($record === false) {
        lockdownbrowser_monitorserviceerror(2028, "The specified courseRefId is invalid: $course_id");
    }

    $body = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";

    $body .= "\t<course>\r\n";

    $body .= "\t\t<courseRefId>";
    $body .= utf8_encode(htmlspecialchars(trim($record->id)));
    $body .= "</courseRefId>\r\n";

    $body .= "\t\t<courseId>";
    $body .= utf8_encode(htmlspecialchars(trim($record->shortname)));
    $body .= "</courseId>\r\n";

    $body .= "\t\t<courseDescription>";
    $body .= utf8_encode(htmlspecialchars(trim($record->fullname)));
    $body .= "</courseDescription>\r\n";

    $body .= "\t</course>\r\n";

    lockdownbrowser_monitorserviceresponse("text/xml", $body, true);
}

function lockdownbrowser_monitoractiontestintegration($parameters) {

    if (!isloggedin()) {
        lockdownbrowser_monitorserviceerror(2004, "Must be logged in to perform the requested action");
    }
    if (!is_siteadmin()) {
        lockdownbrowser_monitorserviceerror(2024, "Must be logged in as admin to perform the requested action");
    }

    // currently no parameters are required; note that this call needs to remain context-free

    // currently no other testing is performed

    lockdownbrowser_monitorserviceresponse("text/plain", "OK", true);
}

function lockdownbrowser_monitorservicerequest() {

    $parameters = lockdownbrowser_monitorrequestparameters();

    if (!isset($parameters["action"]) || strlen($parameters["action"]) == 0) {
        lockdownbrowser_monitorserviceerror(2005, "No service action was specified");
    }
    $action = $parameters["action"];

    if ($action == "login") {
        lockdownbrowser_monitoractionlogin($parameters);
    } else if ($action == "userlogin") {
        lockdownbrowser_monitoractionuserlogin($parameters);
    } else if ($action == "logout") {
        lockdownbrowser_monitoractionlogout($parameters);
    } else if ($action == "courselist") {
        lockdownbrowser_monitoractioncourselist($parameters);
    } else if ($action == "changesettings") {
        lockdownbrowser_monitoractionchangesettings($parameters);
    } else if ($action == "examroster") {
        lockdownbrowser_monitoractionexamroster($parameters);
    } else if ($action == "userinfo2") {
        lockdownbrowser_monitoractionuserinfo2($parameters);
    } else if ($action == "usercourselist") {
        lockdownbrowser_monitoractionusercourselist($parameters);
    } else if ($action == "examinfo2") {
        lockdownbrowser_monitoractionexaminfo2($parameters);
    } else if ($action == "examsync") {
        lockdownbrowser_monitoractionexamsync($parameters);
    } else if ($action == "versioninfo") {
        lockdownbrowser_monitoractionversioninfo($parameters);
    } else if ($action == "usercourserole") {
        lockdownbrowser_monitoractionusercourserole($parameters);
    } else if ($action == "retrievecourse") {
        lockdownbrowser_monitoractionretrievecourse($parameters);
    } else if ($action == "testintegration") {
        lockdownbrowser_monitoractiontestintegration($parameters);
    } else {
        lockdownbrowser_monitorserviceerror(2006, "Unrecognized service action: $action");
    }
}

