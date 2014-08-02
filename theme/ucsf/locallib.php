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

class theme_ucsf_add_category_customization extends admin_setting_configselect {

    public function write_setting ($data) {
        
        $categories = get_config('theme_ucsf');            

        if(!empty($categories->all_categories)) 
            set_config('all_categories', $categories->all_categories . ',' . $data, 'theme_ucsf');
        else 
            set_config('all_categories', $data, 'theme_ucsf');

        return parent::write_setting(0);

    }
}

class theme_ucsf_remove_category_customization extends admin_setting_configselect {

    public function write_setting ($data) {

    	$categories = get_config('theme_ucsf');            

        if(!empty($categories->all_categories)) {
            $temp_array = explode(",", $categories->all_categories);
            if(in_array($data, $temp_array)) {

                $arr = array_diff($temp_array, array($data));
                $fin = implode(",", $arr);

                set_config('all_categories', $fin, 'theme_ucsf');
            }
        } 

        return parent::write_setting(0);
    }
}

