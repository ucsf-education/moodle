<?php

require_once('../config.php');
require_once($CFG->libdir.'/adminlib.php');

//global $PAGE;
$PAGE->set_url('/admin/nightlyimport.php');
$PAGE->set_context(context_system::instance());

require_login();
require_capability('moodle/site:config', context_system::instance());

$stopfile = $CFG->dataroot.'/stop-import-from-production';

$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add("Nightly import");

echo $OUTPUT->header();
echo $OUTPUT->heading("Nightly import from production site");

$isthispageavailable = strstr($CFG->wwwroot, 'yester');
$enable = optional_param('enable', 0, PARAM_BOOL);
$disable = optional_param('disable', 0, PARAM_BOOL);

if ($isthispageavailable && ($enable or $disable) && isloggedin() && confirm_sesskey()) {
    if (file_exists($stopfile)) {
        if ($enable) {
            unlink($stopfile);
        }
    } else {
        if ($disable) {
            file_put_contents($stopfile, date("F j, Y, g:i a"));
        }
    }
}

if ($isthispageavailable && file_exists($stopfile)) {
    $stoppedtime = file_get_contents($stopfile);
    $message = "The process to import from production site has been STOPPED as of <em>$stoppedtime.</em>";
    $messageicon = "notifyproblem";
    $question = "Do you want to <strong>re-enable</strong> the schedule for tonight's import?";
    $actionurl = new moodle_url('/admin/nightlyimport.php', array('sesskey'=>sesskey(), 'enable'=>1));
} elseif ($isthispageavailable) {
    $message = 'This site is currently scheduled to import from production at <em>mid-night</em> tonight.';
    $messageicon = "notifysuccess";
    $question = "Do you want to <strong>stop</strong> the scheduled import?";
    $actionurl = new moodle_url('/admin/nightlyimport.php', array('sesskey'=>sesskey(), 'disable'=>1));
} else {
    $message = 'This feature is not available on this site.';
    $messageicon = "notifyproblem";
}


echo $OUTPUT->notification($message, $messageicon);

if ($isthispageavailable) {
    echo $OUTPUT->confirm($question, $actionurl, new moodle_url('/'));
}
    
echo $OUTPUT->footer();

