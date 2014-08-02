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
 * Strings for component 'theme_ucsf', language 'en'
 *
 * @package    theme
 * @subpackage UCSF
 * @author     Lambda Soulutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['choosereadme'] = '
<div class="clearfix">
<div class="well">
<h2>UCSF</h2>
<p><img class=img-polaroid src="ucsf/pix/screenshot.jpg" /></p>
</div>
<div class="well">
<h3>About</h3>
<p>UCSF is a modified Moodle bootstrap theme which inherits styles and renderers from its parent theme.</p>
<h3>Theme Credits</h3>
<p>Lambdasolutions</p>
</div></div>';

$string['configtitle'] = 'UCSF';

$string['customcss'] = 'Custom CSS';
$string['customcssdesc'] = 'Whatever CSS rules you add to this textarea will be reflected in every page, making for easier customization of this theme.';

$string['footnote'] = 'Footnote';
$string['footnotedesc'] = 'Whatever you add to this textarea will be displayed in the footer throughout your Moodle site.';

$string['invert'] = 'Invert navbar';
$string['invertdesc'] = 'Swaps text and background for the navbar at the top of the page between black and white.';

$string['logo'] = 'Logo';
$string['logodesc'] = 'Please upload your custom logo here if you want to add it to the header.<br>
If the height of your logo is more than 75px add the following CSS rule to the Custom CSS box below.<br>
a.logo {height: 100px;} or whatever height in pixels the logo is.';

$string['pluginname'] = 'UCSF';

$string['region-side-post'] = 'Right';
$string['region-side-pre'] = 'Left';



$string['theme_ucsf'] = 'UCSF';

/* General settings */
$string['generalheading'] = 'General settings';
$string['generalsettings'] = 'General settings';
$string['generalsettingsdesc'] = '';

$string['categorycustomizationheading'] = 'Category Customizations';
$string['enablecustomization'] = 'Enable category customizations';
$string['enablecustomizationdesc'] = 'If "Enable category customizations" is checked, then Moodle will use these customizations. If it is NOT checked, then the regular site-level theme settings are used and all customizations here are ignored (though they remain). It is possible to configure custom categories yet not use them (if the enable checkbox is unchecked).';

$string['toplevelcategorylabel'] = 'Top-level category label';
$string['toplevelcategorylabeldesc'] = 'Top-level = Default. All other categories get this label and menu, unless customizations are specified below.';

$string['displaycoursetitle'] = 'Display course title';
$string['displaycoursetitledesc'] = 'If checked, the course title will appear on the course page for all courses, unless the course title is set NOT to display on configured categories.';

$string['hidecustommenuwhenloggedout'] = 'Hide custom menu when logged out';
$string['hidecustommenuwhenloggedoutdesc'] = 'Hide custom menu when logged out.';

$string['helpfeedbacklink'] = 'Help/Feedback link to a static page';
$string['helpfeedbacklinkdesc'] = 'Help/Feedback link to a static page. Example: http://www.ucsf.edu/static/help.html';

$string['helpfeedbacklinklabel'] = 'Help/Feedback';
$string['helpfeedbacklinktarget'] = 'Open Help/Feedback link in new window';
$string['helpfeedbacklinktargetdesc'] = 'Should Help/Feedback link opens in a new window';

$string['copyright'] = 'Copyright';
$string['copyrightdesc'] = 'UCSF Copyright.';



/* Category Customizations */
$string['addcategorycustomizationheading'] = 'Add category';
$string['addcategorycustomizationheadingdesc'] = '';

$string['categorieslist'] = 'Add category / subcategory';
$string['categorieslistdesc'] = "Adds category for customization. If category is already added to customization, it won't be displayed in the list.";

$string['removecategorycustomizationheading'] = 'Remove category';
$string['removecategorycustomizationheadingdesc'] = '';

$string['removecategorieslist'] = 'Remove category / subcategory';
$string['removecategorieslistdesc'] = 'List of all categories / subcategories currently available for customization / removal';


/* Alerts */
$string['alertsheading'] = 'User Alerts';
$string['alertsheadingsub'] = 'Display important messages to your users on the frontpage';
$string['alertsdesc'] = 'This will display an alert (or multiple) in three different styles to your users on the Moodle frontpage. Please remember to disable these when no longer needed.';

$string['enablealert'] = 'Enable Alert';
$string['enablealertdesc'] = 'Enable or disable alerts';

$string['alert1'] = 'First Alert';
$string['alert2'] = 'Second Alert';
$string['alert3'] = 'Third Alert';

$string['alerttitle'] = 'Title';
$string['alerttitledesc'] = 'Main title/heading for your alert';

$string['alerttype'] = 'Level';
$string['alerttypedesc'] = 'Set the appropriate alert level/type to best inform your users';

$string['alerttext'] = 'Alert Text';
$string['alerttextdesc'] = 'What is the text you wish to display in your alert';

$string['alert_info'] = 'Information';
$string['alert_warning'] = 'Warning';
$string['alert_general'] = 'Announcement';

$string['alertbgcolor'] = 'Alert Background Color';
$string['alertbgcolordesc'] = 'Set the custom alert background color';
/* end of alerts */


/* Tiles & Banner*/
$string['tileheading'] = 'Tiles';
$string['tileheadingsub'] = 'Six locations on the front page to add information and links';

$string['tilecontent'] = 'Content';
$string['tilecontentdesc'] = 'Content to display in the marketing box. Keep it short and sweet.';

$string['tile1'] = 'Tile One';
$string['tile2'] = 'Tile Two';
$string['tile3'] = 'Tile Three';
$string['tile4'] = 'Tile Four';
$string['tile5'] = 'Tile Five';
$string['tile6'] = 'Tile Six';

$string['tileimage'] = 'Tile image';
$string['tileimagedesc'] = 'Tile image to display in the tile box at the homepage';

$string['tileimagealt'] = 'Tile image alt';
$string['tileimagealtdesc'] = 'HTML image alt Attribute';

$string['tileimagetitle'] = 'Tile image title';
$string['tileimagetitledesc'] = 'HTML image title Attribute';

$string['banner'] = 'Banner';
$string['bannerdesc'] = 'Content to display in the banner box at the homepage';

$string['bannerimage'] = 'Banner image';
$string['bannerimagedesc'] = 'Banner image to display in the banner box at the homepage';

$string['bannerimagealt'] = 'Banner image alt';
$string['bannerimagealtdesc'] = 'HTML image alt Attribute';

$string['bannerimagetitle'] = 'Banner image title';
$string['bannerimagetitledesc'] = 'HTML image title Attribute';
/* end of tiles */



$string['categorylabel'] = 'Category label';
$string['categorylabeldesc'] = 'Category label';

$string['linklabeltocategorypage'] = 'Link label to category page';
$string['linklabeltocategorypagedesc'] = 'Link label to category page';

$string['custommenu'] = 'Custom menu';
$string['custommenudesc'] = 'Custom menu';

$string['logoinfo'] = 'Logo';
$string['logoinfodesc'] = 'Logo desc';

