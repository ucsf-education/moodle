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

/*
 * @author    Shaun Daubney
 * @package   theme_aardvark
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hasheader = (empty($PAGE->layout_options['noheader']));

$hassidepre = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-pre', $OUTPUT));
$hassidepost = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-post', $OUTPUT));

$showsidepre = ($hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT));
$showsidepost = ($hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT));

$isfrontpage = $PAGE->bodyid == "page-site-index";

$haslogo = (!empty($PAGE->theme->settings->logo));
$hastitledate = (!empty($PAGE->theme->settings->titledate));
$hasemailurl = (!empty($PAGE->theme->settings->emailurl));

$hasgeneralalert = (!empty($PAGE->theme->settings->generalalert));
$hassnowalert = (!empty($PAGE->theme->settings->snowalert));


$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));
$hashidemenu = (!empty($PAGE->theme->settings->hidemenu));

$courseheader = $coursecontentheader = $coursecontentfooter = $coursefooter = '';

?>

<header role="banner" class="navbar navbar-fixed-top">
    <nav role="navigation" class="navbar-inner">
        <div class="container-fluid">
		
            <a href="<?php echo $CFG->wwwroot;?>"><?php if ($haslogo) {
 echo html_writer::empty_tag('img', array('src'=>$PAGE->theme->settings->logo, 'class'=>'logo')); }

 else { ?><a class="brand" href="<?php echo $CFG->wwwroot;?>"><?php echo $SITE->shortname; }?></a>
			
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <div class="nav-collapse collapse">
          <?php  if ((!isloggedin()) && ($hashidemenu)){}

		  else if ($hascustommenu) {
                echo $custommenu;
				
            } ?>
            <ul class="nav pull-right">
            <li><?php echo $PAGE->headingmenu;
			include('profileblock.php');?></li>
            </ul>
            </div>
        </div>
    </nav>
</header>

<div id="page" class="container-fluid">

<header id="page-header" class="clearfix">
    <?php if ($hasnavbar) { ?>
        <nav class="breadcrumb-button"><?php echo $PAGE->button; ?></nav>
        <?php echo $OUTPUT->navbar(); ?>
    <?php } ?>
    <h1><?php echo $PAGE->heading ?></h1>

    <?php if (!empty($courseheader)) { ?>
        <div id="course-header"><?php echo $courseheader; ?></div>
    <?php } ?>
	
<?php  if (($isfrontpage) && ($hastitledate)) {?>
	<div id="page-header-date"><h1><?php echo strftime("%A %d %B %Y"); ?></h1></div>
	<?php } ?>
	<?php if (($isfrontpage) && $hasgeneralalert) {?>
	<div id="page-header-generalalert">
	<?php echo $PAGE->theme->settings->generalalert; ?>
	</div>
	<?php } ?>
	
		<?php if (($isfrontpage) && $hassnowalert) {?>
	<div id="page-header-snowalert">
	<?php echo $PAGE->theme->settings->snowalert; ?>
	</div>
	<?php } ?>

</header>
