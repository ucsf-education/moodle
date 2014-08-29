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
 * Moodle's Clean theme, an example of how to make a Bootstrap theme
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

$settings=null;

require_once($CFG->dirroot.'/theme/ucsf/locallib.php');

defined('MOODLE_INTERNAL') || die;

    // Get all categories
    $categories = get_config('theme_ucsf');
    $all_categories = '';
    if(!empty($categories->all_categories))
        $all_categories = $categories->all_categories;
    $all_categories_array = explode(",", $all_categories);

    $sql = "SELECT cc.id, cc.name 
            FROM {course_categories} cc            
            WHERE cc.parent = 0             
            ORDER BY cc.sortorder";
    $course_categories =  $DB->get_records_sql($sql);

    $sql2 = "SELECT ccp.id, cc.name, ccp.name as parentname
            FROM {course_categories} cc  
            INNER JOIN {course_categories} ccp
            WHERE ccp.parent = cc.id            
            ORDER BY cc.sortorder";
    $course_subcategories =  $DB->get_records_sql($sql2);


    $choices = array();
    $choices[0]="None";

    $remove_categories_list = array();
    $remove_categories_list[0]="None";


    foreach ($course_categories as $cat) {
        foreach ($all_categories_array as $all_cats) {
            if ($all_cats == $cat->id) {
                $remove_categories_list[$cat->id]=$cat->name;
            }
        }
        if(!in_array($cat->id, $all_categories_array))
            $choices[$cat->id]=$cat->name;
    }

    foreach ($course_subcategories as $cat) {
        foreach ($all_categories_array as $all_cats) {
            if ($all_cats == $cat->id) {
                $remove_categories_list[$cat->id]=$cat->name . ' / ' .$cat->parentname ;
            }
        }
        if(!in_array($cat->id, $all_categories_array))
            $choices[$cat->id]=$cat->name . ' / ' .$cat->parentname;
    }



    $ADMIN->add('themes', new admin_category('theme_ucsf', 'UCSF'));


    /* GENERAL SETTINGS
    -------------------------------------------------------------------------------*/
    $settings_lr = new admin_settingpage('theme_ucsf_general_settings', get_string('generalheading', 'theme_ucsf'));

    //General settings
    $name = 'theme_ucsf/generalsettings';
    $heading = get_string('generalsettings', 'theme_ucsf');
    $information = get_string('generalsettingsdesc', 'theme_ucsf');
    $setting = new admin_setting_heading($name, $heading, $information);
    $settings_lr->add($setting);    

    //Enable category customizations
    $name = 'theme_ucsf/enablecustomization';
    $heading = get_string('enablecustomization', 'theme_ucsf');
    $information = get_string('enablecustomizationdesc', 'theme_ucsf');
    $default = '0';
    $setting = new admin_setting_configcheckbox($name, $heading, $information,$default);
    $settings_lr->add($setting);

    //Top-level category label
    $name = 'theme_ucsf/toplevelcategorylabel';
    $title = get_string('toplevelcategorylabel', 'theme_ucsf');
    $description = get_string('toplevelcategorylabeldesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    //Display course title
    $name = 'theme_ucsf/displaycoursetitle';
    $heading = get_string('displaycoursetitle', 'theme_ucsf');
    $information = get_string('displaycoursetitledesc', 'theme_ucsf');
    $default = 1;
    $setting = new admin_setting_configcheckbox($name, $heading, $information, $default,1);
    $settings_lr->add($setting);

    //Hide custom menu when logged out
    $name = 'theme_ucsf/hidecustommenuwhenloggedout';
    $heading = get_string('hidecustommenuwhenloggedout', 'theme_ucsf');
    $information = get_string('hidecustommenuwhenloggedoutdesc', 'theme_ucsf');
    $default = '0';
    $setting = new admin_setting_configcheckbox($name, $heading, $information, $default);
    $settings_lr->add($setting);

    // Help/Feedback link
    $name = 'theme_ucsf/helpfeedbacklink';
    $title = get_string('helpfeedbacklink', 'theme_ucsf');
    $description = get_string('helpfeedbacklinkdesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    // Help/Feedback link target
    $name = 'theme_ucsf/helpfeedbacklinktarget';
    $title = get_string('helpfeedbacklinktarget' , 'theme_ucsf');
    $description = get_string('helpfeedbacklinktargetdesc', 'theme_ucsf');
    $default = '0';
    $helpfeedbac_choices = array('0'=>"No", '1'=>'Yes');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $helpfeedbac_choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    // Copyright
    $name = 'theme_ucsf/copyright';
    $title = get_string('copyright', 'theme_ucsf');
    $description = get_string('copyrightdesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    // Footnote
    $name = 'theme_ucsf/footnote';
    $title = get_string('footnote', 'theme_ucsf');
    $description = get_string('footnotedesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    

    $ADMIN->add('theme_ucsf', $settings_lr);



    /* ALERTS SETTINGS 
    -------------------------------------------------------------------------------*/
    
    $settings_lr = new admin_settingpage('theme_ucsf_alerts', get_string('alertsheading', 'theme_ucsf'));
    
    // This is the descriptor for Alert One
    $name = 'theme_ucsf/alert1info';
    $heading = get_string('alert1', 'theme_ucsf');
    $information = "";
    $setting = new admin_setting_heading($name, $heading, $information);
    $settings_lr->add($setting);
    
    // Enable Alert
    $name = 'theme_ucsf/enable1alert';
    $title = get_string('enablealert', 'theme_ucsf');
    $description = get_string('enablealertdesc', 'theme_ucsf');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);
    
    // Alert Type.
    $name = 'theme_ucsf/alert1type';
    $title = get_string('alerttype' , 'theme_ucsf');
    $description = get_string('alerttypedesc', 'theme_ucsf');
    $alert_info = get_string('alert_info', 'theme_ucsf');
    $alert_warning = get_string('alert_warning', 'theme_ucsf');
    $alert_general = get_string('alert_general', 'theme_ucsf');
    $default = 'info';
    $alert_choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $alert_choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);
    
    // Alert Title.
    $name = 'theme_ucsf/alert1title';
    $title = get_string('alerttitle', 'theme_ucsf');
    $description = get_string('alerttitledesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);
    
    // Alert Text.
    $name = 'theme_ucsf/alert1text';
    $title = get_string('alerttext', 'theme_ucsf');
    $description = get_string('alerttextdesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);
    
    // This is the descriptor for Alert Two
    $name = 'theme_ucsf/alert2info';
    $heading = get_string('alert2', 'theme_ucsf');
    $information = "";
    $setting = new admin_setting_heading($name, $heading, $information);
    $settings_lr->add($setting);
    
    // Enable Alert
    $name = 'theme_ucsf/enable2alert';
    $title = get_string('enablealert', 'theme_ucsf');
    $description = get_string('enablealertdesc', 'theme_ucsf');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);
    
    // Alert Type.
    $name = 'theme_ucsf/alert2type';
    $title = get_string('alerttype' , 'theme_ucsf');
    $description = get_string('alerttypedesc', 'theme_ucsf');
    $alert_info = get_string('alert_info', 'theme_ucsf');
    $alert_warning = get_string('alert_warning', 'theme_ucsf');
    $alert_general = get_string('alert_general', 'theme_ucsf');
    $default = 'info';
    $alert_choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $alert_choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);
    
    // Alert Title.
    $name = 'theme_ucsf/alert2title';
    $title = get_string('alerttitle', 'theme_ucsf');
    $description = get_string('alerttitledesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);
    
    // Alert Text.
    $name = 'theme_ucsf/alert2text';
    $title = get_string('alerttext', 'theme_ucsf');
    $description = get_string('alerttextdesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);
    
    // This is the descriptor for Alert Three
    $name = 'theme_ucsf/alert3info';
    $heading = get_string('alert3', 'theme_ucsf');
    $information = "";
    $setting = new admin_setting_heading($name, $heading, $information);
    $settings_lr->add($setting);
    
    // Enable Alert
    $name = 'theme_ucsf/enable3alert';
    $title = get_string('enablealert', 'theme_ucsf');
    $description = get_string('enablealertdesc', 'theme_ucsf');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);
    
    // Alert Type.
    $name = 'theme_ucsf/alert3type';
    $title = get_string('alerttype' , 'theme_ucsf');
    $description = get_string('alerttypedesc', 'theme_ucsf');
    $alert_info = get_string('alert_info', 'theme_ucsf');
    $alert_warning = get_string('alert_warning', 'theme_ucsf');
    $alert_general = get_string('alert_general', 'theme_ucsf');
    $default = 'info';
    $alert_choices = array('info'=>$alert_info, 'error'=>$alert_warning, 'success'=>$alert_general);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $alert_choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);
    
    // Alert Title.
    $name = 'theme_ucsf/alert3title';
    $title = get_string('alerttitle', 'theme_ucsf');
    $description = get_string('alerttitledesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);
    
    // Alert Text.
    $name = 'theme_ucsf/alert3text';
    $title = get_string('alerttext', 'theme_ucsf');
    $description = get_string('alerttextdesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);
            
    
    $ADMIN->add('theme_ucsf', $settings_lr);



    /* TILES SETTINGS 
    -------------------------------------------------------------------------------*/
    
    $settings_lr = new admin_settingpage('theme_ucsf_tiles', get_string('tileheading', 'theme_ucsf'));
    
    // This is the descriptor for Tile
    $name = 'theme_ucsf/tileheadingsub';
    $heading = get_string('tileheadingsub', 'theme_ucsf');
    $information = "";
    $setting = new admin_setting_heading($name, $heading, $information);
    $settings_lr->add($setting);

    // This is the descriptor for Banner
    $name = 'theme_ucsf/banner';
    $title = get_string('banner', 'theme_ucsf');
    $description = get_string('bannerdesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/bannerimage';
    $title = get_string('bannerimage', 'theme_ucsf');
    $description = get_string('bannerimagedesc', 'theme_ucsf');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'bannerimage');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/bannerimagealt';
    $title = get_string('bannerimagealt', 'theme_ucsf');
    $description = get_string('bannerimagealtdesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/bannerimagetitle';
    $title = get_string('bannerimagetitle', 'theme_ucsf');
    $description = get_string('bannerimagetitledesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);


    // This is the descriptor for Tile One
    $name = 'theme_ucsf/tile1heading';
    $heading = get_string('tile1', 'theme_ucsf');
    $information = "";
    $setting = new admin_setting_heading($name, $heading, $information);
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile1content';
    $title = get_string('tilecontent', 'theme_ucsf');
    $description = get_string('tilecontentdesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile1image';
    $title = get_string('tileimage', 'theme_ucsf');
    $description = get_string('tileimagedesc', 'theme_ucsf');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'tile1image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile1imagealt';
    $title = get_string('tileimagealt', 'theme_ucsf');
    $description = get_string('tileimagealtdesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile1imagetitle';
    $title = get_string('tileimagetitle', 'theme_ucsf');
    $description = get_string('tileimagetitledesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    // This is the descriptor for Tile Two
    $name = 'theme_ucsf/tile2heading';
    $heading = get_string('tile2', 'theme_ucsf');
    $information = "";
    $setting = new admin_setting_heading($name, $heading, $information);
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile2content';
    $title = get_string('tilecontent', 'theme_ucsf');
    $description = get_string('tilecontentdesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile2image';
    $title = get_string('tileimage', 'theme_ucsf');
    $description = get_string('tileimagedesc', 'theme_ucsf');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'tile2image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile2imagealt';
    $title = get_string('tileimagealt', 'theme_ucsf');
    $description = get_string('tileimagealtdesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile2imagetitle';
    $title = get_string('tileimagetitle', 'theme_ucsf');
    $description = get_string('tileimagetitledesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    // This is the descriptor for Tile Three
    $name = 'theme_ucsf/tile3heading';
    $heading = get_string('tile3', 'theme_ucsf');
    $information = "";
    $setting = new admin_setting_heading($name, $heading, $information);
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile3content';
    $title = get_string('tilecontent', 'theme_ucsf');
    $description = get_string('tilecontentdesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile3image';
    $title = get_string('tileimage', 'theme_ucsf');
    $description = get_string('tileimagedesc', 'theme_ucsf');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'tile3image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile3imagealt';
    $title = get_string('tileimagealt', 'theme_ucsf');
    $description = get_string('tileimagealtdesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile3imagetitle';
    $title = get_string('tileimagetitle', 'theme_ucsf');
    $description = get_string('tileimagetitledesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    // This is the descriptor for Tile Four
    $name = 'theme_ucsf/tile4heading';
    $heading = get_string('tile4', 'theme_ucsf');
    $information = "";
    $setting = new admin_setting_heading($name, $heading, $information);
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile4content';
    $title = get_string('tilecontent', 'theme_ucsf');
    $description = get_string('tilecontentdesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile4image';
    $title = get_string('tileimage', 'theme_ucsf');
    $description = get_string('tileimagedesc', 'theme_ucsf');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'tile4image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile4imagealt';
    $title = get_string('tileimagealt', 'theme_ucsf');
    $description = get_string('tileimagealtdesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile4imagetitle';
    $title = get_string('tileimagetitle', 'theme_ucsf');
    $description = get_string('tileimagetitledesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    // This is the descriptor for Tile Five
    $name = 'theme_ucsf/tile5heading';
    $heading = get_string('tile5', 'theme_ucsf');
    $information = "";
    $setting = new admin_setting_heading($name, $heading, $information);
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile5content';
    $title = get_string('tilecontent', 'theme_ucsf');
    $description = get_string('tilecontentdesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile5image';
    $title = get_string('tileimage', 'theme_ucsf');
    $description = get_string('tileimagedesc', 'theme_ucsf');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'tile5image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile5imagealt';
    $title = get_string('tileimagealt', 'theme_ucsf');
    $description = get_string('tileimagealtdesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile5imagetitle';
    $title = get_string('tileimagetitle', 'theme_ucsf');
    $description = get_string('tileimagetitledesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    // This is the descriptor for Tile Six
    $name = 'theme_ucsf/tile6heading';
    $heading = get_string('tile6', 'theme_ucsf');
    $information = "";
    $setting = new admin_setting_heading($name, $heading, $information);
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile6content';
    $title = get_string('tilecontent', 'theme_ucsf');
    $description = get_string('tilecontentdesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile6image';
    $title = get_string('tileimage', 'theme_ucsf');
    $description = get_string('tileimagedesc', 'theme_ucsf');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'tile6image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile6imagealt';
    $title = get_string('tileimagealt', 'theme_ucsf');
    $description = get_string('tileimagealtdesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $name = 'theme_ucsf/tile6imagetitle';
    $title = get_string('tileimagetitle', 'theme_ucsf');
    $description = get_string('tileimagetitledesc', 'theme_ucsf');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);


    $ADMIN->add('theme_ucsf', $settings_lr);


    
    /* CATEGORY CUSTOMIZATION 
    -------------------------------------------------------------------------------*/
    $settings_lr = new admin_settingpage('theme_ucsf_category_customizations', get_string('categorycustomizationheading', 'theme_ucsf'));

    //Add category customization
    $name = 'theme_ucsf/addcategorycustomizationheading';
    $heading = get_string('addcategorycustomizationheading', 'theme_ucsf');
    $information = get_string('addcategorycustomizationheadingdesc', 'theme_ucsf');
    $setting = new admin_setting_heading($name, $heading, $information);
    $settings_lr->add($setting);

    //Add category - list.
    $name = 'theme_ucsf/categorieslist';
    $title = get_string('categorieslist' , 'theme_ucsf');
    $description = get_string('categorieslistdesc', 'theme_ucsf');
    $default = '0';
    $setting = new theme_ucsf_add_category_customization($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    //Add category customization
    $name = 'theme_ucsf/removecategorycustomizationheading';
    $heading = get_string('removecategorycustomizationheading', 'theme_ucsf');
    $information = get_string('removecategorycustomizationheadingdesc', 'theme_ucsf');
    $setting = new admin_setting_heading($name, $heading, $information);
    $settings_lr->add($setting);

    //Add category - list.
    $name = 'theme_ucsf/removecategorieslist';
    $title = get_string('removecategorieslist' , 'theme_ucsf');
    $description = get_string('removecategorieslistdesc', 'theme_ucsf');
    $default = '0';
    $setting = new theme_ucsf_remove_category_customization($name, $title, $description, $default, $remove_categories_list);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings_lr->add($setting);

    $ADMIN->add('theme_ucsf', $settings_lr);
    


    /* CATEGORIES & SUBCATEGORIES
    -------------------------------------------------------------------------------*/

    foreach ($all_categories_array as $allcats) {
        foreach ($course_categories as $cats) {
            if($allcats == $cats->id) {
                $settings_lr = new admin_settingpage('theme_ucsf_'.$cats->id, 'Cat - '. $cats->name);

                //Category label
                $name = 'theme_ucsf/categorylabel'.$cats->id;
                $title = get_string('categorylabel', 'theme_ucsf');
                $description = get_string('categorylabeldesc', 'theme_ucsf');
                $default = '';
                $setting = new admin_setting_configtext($name, $title, $description, $default);
                $setting->set_updatedcallback('theme_reset_all_caches');
                $settings_lr->add($setting);
                
                //Category label image
                $name = 'theme_ucsf/categorylabelimage'.$cats->id;
                $title = get_string('categorylabelimage', 'theme_ucsf');
                $description = get_string('categorylabelimagedesc', 'theme_ucsf');
                $setting = new admin_setting_configstoredfile($name, $title, $description, 'categorylabelimage'.$cats->id);
                $setting->set_updatedcallback('theme_reset_all_caches');
                $settings_lr->add($setting);

                //Category label image height
                $name = 'theme_ucsf/categorylabelimageheight'.$cats->id;
                $title = get_string('categorylabelimageheight', 'theme_ucsf');
                $description = get_string('categorylabelimageheightdesc', 'theme_ucsf');
                $default = '';
                $setting = new admin_setting_configtext($name, $title, $description, $default);
                $setting->set_updatedcallback('theme_reset_all_caches');
                $settings_lr->add($setting);

                //Category label image alt
                $name = 'theme_ucsf/categorylabelimagealt'.$cats->id;
                $title = get_string('categorylabelimagealt', 'theme_ucsf');
                $description = get_string('categorylabelimagealtdesc', 'theme_ucsf');
                $default = '';
                $setting = new admin_setting_configtext($name, $title, $description, $default);
                $setting->set_updatedcallback('theme_reset_all_caches');
                $settings_lr->add($setting);

                //Category label image title
                $name = 'theme_ucsf/categorylabelimagetitle'.$cats->id;
                $title = get_string('categorylabelimagetitle', 'theme_ucsf');
                $description = get_string('categorylabelimagetitledesc', 'theme_ucsf');
                $default = '';
                $setting = new admin_setting_configtext($name, $title, $description, $default);
                $setting->set_updatedcallback('theme_reset_all_caches');
                $settings_lr->add($setting);

                //Link label to category page
                $name = 'theme_ucsf/linklabeltocategorypage'.$cats->id;
                $heading = get_string('linklabeltocategorypage', 'theme_ucsf');
                $information = get_string('linklabeltocategorypagedesc', 'theme_ucsf');
                $default = 1;
                $setting = new admin_setting_configcheckbox($name, $heading, $information, 1,1);
                $settings_lr->add($setting);

                //Display course title
                $name = 'theme_ucsf/displaycoursetitle'.$cats->id;
                $heading = get_string('displaycoursetitle', 'theme_ucsf');
                $information = get_string('displaycoursetitledesc', 'theme_ucsf');
                $default = 1;
                $setting = new admin_setting_configcheckbox($name, $heading, $information, 1,1);
                $settings_lr->add($setting);

                //Custom menu
                $name = 'theme_ucsf/custommenu'.$cats->id;
                $heading = get_string('custommenu', 'theme_ucsf');
                $information = get_string('custommenudesc', 'theme_ucsf');
                $default = '';
                $setting = new admin_setting_configtextarea($name, $heading, $information,$default);
                $settings_lr->add($setting);



                $ADMIN->add('theme_ucsf', $settings_lr);
            }
        }
        
        foreach ($course_subcategories as $cats) {
            if($allcats == $cats->id) {
                $settings_lr = new admin_settingpage('theme_ucsf_'.$cats->id, 'Cat - '. $cats->name . ' / ' .$cats->parentname);

                //Category label
                $name = 'theme_ucsf/categorylabel'.$cats->id;
                $title = get_string('categorylabel', 'theme_ucsf');
                $description = get_string('categorylabeldesc', 'theme_ucsf');
                $default = '';
                $setting = new admin_setting_configtext($name, $title, $description, $default);
                $setting->set_updatedcallback('theme_reset_all_caches');
                $settings_lr->add($setting);

                //Category label image
                $name = 'theme_ucsf/categorylabelimage'.$cats->id;
                $title = get_string('categorylabelimage', 'theme_ucsf');
                $description = get_string('categorylabelimagedesc', 'theme_ucsf');
                $setting = new admin_setting_configstoredfile($name, $title, $description, 'categorylabelimage'.$cats->id);
                $setting->set_updatedcallback('theme_reset_all_caches');
                $settings_lr->add($setting);

                //Category label image height
                $name = 'theme_ucsf/categorylabelimageheight'.$cats->id;
                $title = get_string('categorylabelimageheight', 'theme_ucsf');
                $description = get_string('categorylabelimageheightdesc', 'theme_ucsf');
                $default = '';
                $setting = new admin_setting_configtext($name, $title, $description, $default);
                $setting->set_updatedcallback('theme_reset_all_caches');
                $settings_lr->add($setting);

                //Category label image alt
                $name = 'theme_ucsf/categorylabelimagealt'.$cats->id;
                $title = get_string('categorylabelimagealt', 'theme_ucsf');
                $description = get_string('categorylabelimagealtdesc', 'theme_ucsf');
                $default = '';
                $setting = new admin_setting_configtext($name, $title, $description, $default);
                $setting->set_updatedcallback('theme_reset_all_caches');
                $settings_lr->add($setting);

                //Category label image title
                $name = 'theme_ucsf/categorylabelimagetitle'.$cats->id;
                $title = get_string('categorylabelimagetitle', 'theme_ucsf');
                $description = get_string('categorylabelimagetitledesc', 'theme_ucsf');
                $default = '';
                $setting = new admin_setting_configtext($name, $title, $description, $default);
                $setting->set_updatedcallback('theme_reset_all_caches');
                $settings_lr->add($setting);

                //Link label to category page
                $name = 'theme_ucsf/linklabeltocategorypage'.$cats->id;
                $heading = get_string('linklabeltocategorypage', 'theme_ucsf');
                $information = get_string('linklabeltocategorypagedesc', 'theme_ucsf');
                $default = 1;
                $setting = new admin_setting_configcheckbox($name, $heading, $information, 1,1);
                $settings_lr->add($setting);

                //Display course title
                $name = 'theme_ucsf/displaycoursetitle'.$cats->id;
                $heading = get_string('displaycoursetitle', 'theme_ucsf');
                $information = get_string('displaycoursetitledesc', 'theme_ucsf');
                $default = 1;
                $setting = new admin_setting_configcheckbox($name, $heading, $information, 1,1);
                $settings_lr->add($setting);

                //Custom menu
                $name = 'theme_ucsf/custommenu'.$cats->id;
                $heading = get_string('custommenu', 'theme_ucsf');
                $information = get_string('custommenudesc', 'theme_ucsf');
                $default = '';
                $setting = new admin_setting_configtextarea($name, $heading, $information,$default);
                $settings_lr->add($setting);

                $ADMIN->add('theme_ucsf', $settings_lr);
            }
        }        
    }