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
 * Moodle's ucsf theme, an example of how to make a Bootstrap theme
 *
 * DO NOT MODIFY THIS THEME!
 * COPY IT FIRST, THEN RENAME THE COPY AND MODIFY IT INSTEAD.
 *
 * For full information about creating Moodle themes, see:
 * http://docs.moodle.org/dev/Themes_2.0
 *
 * @package    theme
 * @subpackage UCSF
 * @author     Lambda Soulutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Parses CSS before it is cached.
 *
 * This function can make alterations and replace patterns within the CSS.
 *
 * @param string $css The CSS
 * @param theme_config $theme The theme config object.
 * @return string The parsed CSS The parsed CSS.
 */


function theme_ucsf_process_css($css, $theme) {
    
    // Set the background image for the logo.
    $logo = $theme->setting_file_url('logo', 'logo');
    $css = theme_ucsf_set_logo($css, $logo);

    // Set custom CSS.
    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = theme_ucsf_set_customcss($css, $customcss);

    return $css;
}

/**
 * Adds the logo to CSS.
 *
 * @param string $css The CSS.
 * @param string $logo The URL of the logo.
 * @return string The parsed CSS
 */
function theme_ucsf_set_logo($css, $logo) {
    $tag = '[[setting:logo]]';
    $replacement = $logo;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_ucsf_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM) {
        $theme = theme_config::load('ucsf');
        if ($filearea === 'logo') {
            return $theme->setting_file_serve('logo', $args, $forcedownload, $options);
        } else if ($filearea === 'bannerimage') {
            return $theme->setting_file_serve('bannerimage', $args, $forcedownload, $options);
        } else if ($filearea === 'tile1image') {
            return $theme->setting_file_serve('tile1image', $args, $forcedownload, $options);
        } else if ($filearea === 'tile2image') {
            return $theme->setting_file_serve('tile2image', $args, $forcedownload, $options);
        } else if ($filearea === 'tile3image') {
            return $theme->setting_file_serve('tile3image', $args, $forcedownload, $options);
        } else if ($filearea === 'tile4image') {
            return $theme->setting_file_serve('tile4image', $args, $forcedownload, $options);
        } else if ($filearea === 'tile5image') {
            return $theme->setting_file_serve('tile5image', $args, $forcedownload, $options);
        } else if ($filearea === 'tile6image') {
            return $theme->setting_file_serve('tile6image', $args, $forcedownload, $options);
        } else {
            send_file_not_found();
        }
    } else {
        send_file_not_found();
    }
}

/**
 * Adds any custom CSS to the CSS before it is cached.
 *
 * @param string $css The original CSS.
 * @param string $customcss The custom CSS to add.
 * @return string The CSS which now contains our custom CSS.
 */
function theme_ucsf_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

/**
 * Returns an object containing HTML for the areas affected by settings.
 *
 * @param renderer_base $output Pass in $OUTPUT.
 * @param moodle_page $page Pass in $PAGE.
 * @return stdClass An object with the following properties:
 *      - navbarclass A CSS class to use on the navbar. By default ''.
 *      - heading HTML to use for the heading. A logo if one is selected or the default heading.
 *      - footnote HTML to use as a footnote. By default ''.
 */
function theme_ucsf_get_html_for_settings(renderer_base $output, moodle_page $page) {
    global $CFG;
    $return = new stdClass;

    $return->navbarclass = '';
    if (!empty($page->theme->settings->invert)) {
        $return->navbarclass .= ' navbar-inverse';
    }

    if (!empty($page->theme->settings->logo)) {
        $return->heading = html_writer::link($CFG->wwwroot, '', array('title' => get_string('home'), 'class' => 'logo'));
    } else {
        $return->heading = $output->page_heading();
    }

    $return->copyright = '';
    if (!empty($page->theme->settings->copyright)) {
        $return->copyright = $page->theme->settings->copyright;
    }

    $return->footnote = '';
    if (!empty($page->theme->settings->footnote)) {
        $return->footnote = $page->theme->settings->footnote;
    }

    return $return;
}


/**
 * Returns an object containing HTML for the areas affected by category customization settings.
 *
 * @param renderer_base $output Pass in $OUTPUT.
 * @param moodle_page $page Pass in $PAGE.
 * @return stdClass An object with the following properties:
 *      - enablecustomization: true if Category Customization is enabled. By default false.
 *      - categorylabel: String to use for the Top Level Category Label. By default ''.
 *      - displaycoursetitle: The course title will appear on the course page for all courses, 
*         unless the course title is set NOT to display on configured categories.
 *      - displaycustommenu: Hide Custom Menu when logged out. By default returns custom menu.
 */
function theme_ucsf_get_global_settings(renderer_base $output, moodle_page $page) {
    global $CFG, $COURSE, $CATEGORIES;
    $return = new stdClass;

    $return->categorylabel = '';
    $return->coursetitle = '';
    $return->displaycustommenu = $output->custom_menu();

    // help/feedback link
    $pipeseparator = "";
    if(isloggedin()){
        $pipeseparator = '<div class="pipe-separator">|</div>';
    }
    $helpfeedbacklinklabel = get_string('helpfeedbacklinklabel', 'theme_ucsf');

    $target = '';
    if($page->theme->settings->helpfeedbacklinktarget==1)
        $target = 'target = \"_blank\"';
    
    if (!empty($page->theme->settings->helpfeedbacklink)) {
        $return->helpfeedbacklink = $pipeseparator.'<div class="help-feedback"><a href="'.$page->theme->settings->helpfeedbacklink.'" '.$target.'>'.$helpfeedbacklinklabel.'</a></div>';
    } else {
        $return->helpfeedbacklink ='';
    }

    // customization enable
    $return->enablecustomization = false;
    if ($page->theme->settings->enablecustomization) {
        $return->enablecustomization = true;
    }

    // category customization enabled
    if($return->enablecustomization) {

        // set toplevel category label
        $return->categorylabel = '';
        if (!empty($page->theme->settings->toplevelcategorylabel)) {
            $return->categorylabel = '<div class="category-label pull-left">'.$page->theme->settings->toplevelcategorylabel.'</div>';;
        }

        // get course category id
        $COURSECATEGORY = 0;
        if ($page->pagelayout=="coursecategory" && isset($_REQUEST["categoryid"]))
            $COURSECATEGORY = $_REQUEST["categoryid"];
        else
            $COURSECATEGORY = $COURSE->category;

        // set course title
        $return->coursetitle = '';
        if(!empty($page->theme->settings->displaycoursetitle))
            if ($page->theme->settings->displaycoursetitle)
                if(!empty($COURSE->fullname))
                    $return->coursetitle = '<div class="custom_course_title">'. $COURSE->fullname . '</div>';
            
        if(!is_null($COURSECATEGORY && $COURSECATEGORY!=0)) {
            $displaycustomcoursetitle = "displaycoursetitle".$COURSECATEGORY;
            if(isset($page->theme->settings->$displaycustomcoursetitle))
                if(!$page->theme->settings->$displaycustomcoursetitle)             
                    $return->coursetitle = '';
        }

        // category labels
        theme_ucsf_get_category_roots($COURSECATEGORY);
        $COURSECATEGORY = theme_ucsf_get_first_category_customization($page);

        // override top level category label with custom category label
        if(!is_null($COURSECATEGORY && $COURSECATEGORY!=0)) {
            $categorylabelcustom = "categorylabel".$COURSECATEGORY;
            if (!empty($page->theme->settings->$categorylabelcustom)) {
                $return->categorylabel = '<div class="category-label pull-left">'.$page->theme->settings->$categorylabelcustom.'</div>';
            }
        }

        // set link label to category page
        $linklabeltocategorypage = "linklabeltocategorypage".$COURSECATEGORY;
        if (isset($page->theme->settings->$linklabeltocategorypage))
            if($page->theme->settings->$linklabeltocategorypage)
                $return->categorylabel = '<a href="'.$CFG->wwwroot.'/course/index.php?categoryid='.$COURSECATEGORY.'"">'.$return->categorylabel.'</a>';
            else 
                $return->categorylabel = $return->categorylabel;

    }    

    // display custom menu
    $return->displaycustommenu = $output->custom_menu();
    if ($page->theme->settings->hidecustommenuwhenloggedout) {
        if(!isloggedin())
            $return->displaycustommenu = '';
    }

    // logo
    $return->logo = '<img title="UCSF | CLE" src="'.$output->pix_url('ucsf-logo', 'theme_ucsf').'"/>';

    // menu background clean css
    $menubackgroundcleen = ""; 

    if($return->categorylabel == '') {
        $menubackgroundcleen = "menu-background-cleen";
    }
    
    $return->menubackgroundcleen = $menubackgroundcleen;

    return $return;
}

function theme_ucsf_get_category_roots($categoryid) {
    global $CATEGORIES, $DB;

    $sql = "SELECT cc.parent, cc.name 
        FROM {course_categories} cc            
        WHERE cc.id = ".$categoryid."";

    $course_categories =  $DB->get_records_sql($sql);
    foreach ($course_categories as $cat) {
        $CATEGORIES[]= $categoryid;
        theme_ucsf_get_category_roots($cat->parent);
    }
}

function theme_ucsf_get_first_category_customization(moodle_page $page) {
    global $CATEGORIES, $DB;

    $categories = get_config('theme_ucsf');
    $all_categories = '';
    $all_categories_array = array();
    if(!empty($categories->all_categories)){
        $all_categories = $categories->all_categories;
        $all_categories_array = explode(",", $all_categories);
    }

    if(is_array($CATEGORIES)) {    
        foreach ($CATEGORIES as $cat) {
            if(in_array($cat, $all_categories_array)) {
                $categorylabelcustom = "categorylabel".$cat;
                if (!empty($page->theme->settings->$categorylabelcustom)) {
                    return $cat;
                }            
            }
        }
    }

    return 0;
}

function theme_ucsf_get_first_category_customization_menu(moodle_page $page) {
    global $CATEGORIES, $DB;

    $categories = get_config('theme_ucsf');
    $all_categories = '';
    $all_categories_array = array();
    if(!empty($categories->all_categories)){
        $all_categories = $categories->all_categories;
        $all_categories_array = explode(",", $all_categories);
    }

    if(is_array($CATEGORIES)) {    
        foreach ($CATEGORIES as $cat) {
            if(in_array($cat, $all_categories_array)) {
                $categorycustommenu = "custommenu".$cat;
                if (!empty($page->theme->settings->$categorycustommenu)) {
                    return $cat;
                }            
            }
        }
    }

    return 0;
}



function theme_ucsf_get_alerts(renderer_base $output, moodle_page $page) {
    global $CFG;
    $hasalert1 = false;
    $hasalert2 = false;
    $hasalert3 = false;

    if($page->theme->settings->enable1alert &&(!isset($_SESSION["alerts"]["alert1"]) || $_SESSION["alerts"]["alert1"] != 0)) {
        $_SESSION["alerts"]["alert1"] = 1;
        $hasalert1 = true;
    }

    if($page->theme->settings->enable2alert && (!isset($_SESSION["alerts"]["alert2"]) || $_SESSION["alerts"]["alert2"] != 0)) {
        $_SESSION["alerts"]["alert2"] = 1;
        $hasalert2 = true;
    }

    if($page->theme->settings->enable3alert && (!isset($_SESSION["alerts"]["alert3"]) || $_SESSION["alerts"]["alert3"] != 0)) {
        $_SESSION["alerts"]["alert3"] = 1;
        $hasalert3 = true;
    }
    
    $alert=null;

    if ($hasalert1) {
        $alert.= '<div class="useralerts alert alert-'.$page->theme->settings->alert1type.' alert1">';
        $alert.='<a class="close" data-dismiss="alert" data-target-url="'.$CFG->wwwroot.'" href="#">×</a>';
        $alert.='<span class="title">'.$page->theme->settings->alert1title.'</span>'.$page->theme->settings->alert1text;
        $alert.='</div>';
    }

    if ($hasalert2) {
        $alert.= '<div class="useralerts alert alert-'.$page->theme->settings->alert2type.' alert2">';
        $alert.='<a class="close" data-dismiss="alert" data-target-url="'.$CFG->wwwroot.'" href="#">×</a>';
        $alert.='<span class="title">'.$page->theme->settings->alert2title.'</span>'.$page->theme->settings->alert2text;
        $alert.='</div>';
    }

    if ($hasalert3) {
        $alert.= '<div class="useralerts alert alert-'.$page->theme->settings->alert3type.' alert3">';
        $alert.='<a class="close" data-dismiss="alert" data-target-url="'.$CFG->wwwroot.'" href="#">×</a>';
        $alert.='<span class="title">'.$page->theme->settings->alert3title.'</span>'.$page->theme->settings->alert3text;
        $alert.='</div>';
    }

    if( $hasalert1 || $hasalert2 || $hasalert3 ) {
        $alert = '<div class="alerts">'. $alert . '</div>';
    } else if ($page->pagelayout=="frontpage") {
        $alert = '<div class="alerts"></div>';
    }

    return $alert;
}

function theme_ucsf_get_banner(renderer_base $output, moodle_page $page) {
    $banner = null;


    $bannerimage = "";
    if(!empty($page->theme->settings->bannerimage))
        $bannerimage = '<img src="'.$page->theme->setting_file_url('bannerimage', 'bannerimage').'" alt="'.$page->theme->settings->bannerimagealt.'" title="'.$page->theme->settings->bannerimagetitle.'" class="banner-image">';

    $bannertext = "";
    if(!empty($page->theme->settings->banner))
        $bannertext = $page->theme->settings->banner;

    if(!empty($page->theme->settings->bannerimage) || !empty($page->theme->settings->banner))
        $banner = '<div class="banner">'.$bannertext.'<div class="banner-image-container">'.$bannerimage.'</div></div>';

    return $banner;
}

function theme_ucsf_get_tiles(renderer_base $output, moodle_page $page) {
    $tiles = null;

    $setting = 'tile1image';
    $tile1image = $page->theme->setting_file_url($setting, $setting);

    $tile1image = "";
    if(!empty($page->theme->settings->tile1image))
        $tile1image = '<img src="'.$page->theme->setting_file_url('tile1image', 'tile1image').'" alt="'.$page->theme->settings->tile1imagealt.'" title="'.$page->theme->settings->tile1imagetitle.'" class="tile-image">';

    $tile2image = "";
    if(!empty($page->theme->settings->tile2image))
        $tile2image = '<img src="'.$page->theme->setting_file_url('tile2image', 'tile2image').'" alt="'.$page->theme->settings->tile2imagealt.'" title="'.$page->theme->settings->tile2imagetitle.'" class="tile-image">';

    $tile3image = "";
    if(!empty($page->theme->settings->tile3image))
        $tile3image = '<img src="'.$page->theme->setting_file_url('tile3image', 'tile3image').'" alt="'.$page->theme->settings->tile3imagealt.'" title="'.$page->theme->settings->tile3imagetitle.'" class="tile-image">';

    $tile4image = "";
    if(!empty($page->theme->settings->tile4image))
        $tile4image = '<img src="'.$page->theme->setting_file_url('tile4image', 'tile4image').'" alt="'.$page->theme->settings->tile4imagealt.'" title="'.$page->theme->settings->tile4imagetitle.'" class="tile-image">';

    $tile5image = "";
    if(!empty($page->theme->settings->tile5image))
        $tile5image = '<img src="'.$page->theme->setting_file_url('tile5image', 'tile5image').'" alt="'.$page->theme->settings->tile5imagealt.'" title="'.$page->theme->settings->tile5imagetitle.'" class="tile-image">';

    $tile6image = "";
    if(!empty($page->theme->settings->tile6image))
        $tile6image = '<img src="'.$page->theme->setting_file_url('tile6image', 'tile6image').'" alt="'.$page->theme->settings->tile6imagealt.'" title="'.$page->theme->settings->tile6imagetitle.'" class="tile-image">';

    // empty tiles borderless
    $tile1border = "";
    if(!empty($page->theme->settings->tile1content) || !empty($page->theme->settings->tile1image))
        $tile1border = "tile-border";

    $tile2border = "";
    if(!empty($page->theme->settings->tile2content) || !empty($page->theme->settings->tile2image))
        $tile2border = "tile-border";

    $tile3border = "";
    if(!empty($page->theme->settings->tile3content) || !empty($page->theme->settings->tile3image))
        $tile3border = "tile-border";

    $tile4border = "";
    if(!empty($page->theme->settings->tile4content) || !empty($page->theme->settings->tile4image))
        $tile4border = "tile-border";

    $tile5border = "";
    if(!empty($page->theme->settings->tile5content) || !empty($page->theme->settings->tile5image))
        $tile5border = "tile-border";

    $tile6border = "";
    if(!empty($page->theme->settings->tile6content) || !empty($page->theme->settings->tile6image))
        $tile6border = "tile-border";

    $tiles.='<div class="row-fluid">
        <div class="span4 tile '.$tile1border.'">'.$page->theme->settings->tile1content.'<div class="tile-image-container">'.$tile1image.'</div></div>
        <div class="span4 tile '.$tile2border.'">'.$page->theme->settings->tile2content.'<div class="tile-image-container">'.$tile2image.'</div></div>
        <div class="span4 tile '.$tile3border.'">'.$page->theme->settings->tile3content.'<div class="tile-image-container">'.$tile3image.'</div></div>
    </div>
    <div class="row-fluid">                    
        <div class="span4 tile '.$tile4border.'">'.$page->theme->settings->tile4content.'<div class="tile-image-container">'.$tile4image.'</div></div>
        <div class="span4 tile '.$tile5border.'">'.$page->theme->settings->tile5content.'<div class="tile-image-container">'.$tile5image.'</div></div>
        <div class="span4 tile '.$tile6border.'">'.$page->theme->settings->tile6content.'<div class="tile-image-container">'.$tile6image.'</div></div>
    </div>';

    return $tiles;
}


/**
 * All theme functions should start with theme_ucsf_
 * @deprecated since 2.5.1
 */
function ucsf_process_css() {
    throw new coding_exception('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__);
}

/**
 * All theme functions should start with theme_ucsf_
 * @deprecated since 2.5.1
 */
function ucsf_set_logo() {
    throw new coding_exception('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__);
}

/**
 * All theme functions should start with theme_ucsf_
 * @deprecated since 2.5.1
 */
function ucsf_set_customcss() {
    throw new coding_exception('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__);
}

function theme_ucsf_page_init(moodle_page $page) {
    $page->requires->jquery();
    $page->requires->jquery_plugin('alert', 'theme_ucsf');  
}