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

// Get the HTML for the settings bits.

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

 
$html = theme_ucsf_get_html_for_settings($OUTPUT, $PAGE);
$globalsettings = theme_ucsf_get_global_settings($OUTPUT, $PAGE);
$alerts = theme_ucsf_get_alerts($OUTPUT, $PAGE);

$left = (!right_to_left());  // To know if to add 'pull-right' and 'desktop-first-column' classes in the layout for LTR.
echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google fonts -->
    <link href="//fonts.googleapis.com/css?family=Open Sans:400,600,700,Bold,italic" rel="stylesheet" type="text/css"/>
    <!-- Awesome fonts -->
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
</head>

<body <?php echo $OUTPUT->body_attributes('two-column'); ?>>

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<header role="banner" class="navbar '.$return->navbarclass.'">
    <nav role="navigation" class="navbar-inner">            
        <div class="container-fluid top-header">
            <a class="brand pull-left" href="http://courses.ucsf.edu"><?php echo $globalsettings->logo;?></a>
            <a class="btn btn-navbar pull-right" data-toggle="workaround-collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>                
            <ul class="nav pull-right">
                <li><?php echo $OUTPUT->page_heading_menu(); ?></li>
                <li class="navbar-text">
                    <?php
                        if(isloggedin()) { 
                            echo $OUTPUT->login_info(); echo $globalsettings->helpfeedbacklink;   
                        } else {
                            echo $globalsettings->helpfeedbacklink; echo $OUTPUT->login_info();  
                        }
                    ?>
                </li>
            </ul>
            <div class="cle-text">Collaborative Learning Environment</div>
        </div>

        <div class="container-fluid menu-background <?php echo $globalsettings->menubackgroundcleen; ?>">
            <div class="category-label-container pull-left">
                <?php echo $globalsettings->categorylabel; ?>
            </div>

            <div class="nav-collapse collapse ucsf-custom-menu">
                <?php echo $globalsettings->displaycustommenu; ?>
                <div class="navbar-text-responsive">
                    <?php
                        if(isloggedin()) { 
                            echo $OUTPUT->login_info(); echo $globalsettings->helpfeedbacklink;   
                        } else {
                            echo $OUTPUT->login_info();  
                        }
                    ?>
                </div>
            </div>
        </div>
    </nav>
</header>

<div id="page" class="container-fluid">

    <?php echo $alerts ?>

    <header id="page-header" class="clearfix">
        <div id="page-navbar" class="clearfix">
            <nav class="breadcrumb-nav"><?php echo $OUTPUT->navbar(); ?></nav>
            <div class="breadcrumb-button"><?php echo $OUTPUT->page_heading_button(); ?></div>
        </div>
        <div id="course-header">
            <?php echo $OUTPUT->course_header(); ?>
        </div>
    </header>

    <div id="page-content" class="row-fluid">
        <section id="region-main" class="span9<?php if ($left) { echo ' pull-right'; } ?>">
            <?php
            echo $OUTPUT->course_content_header();
            echo $OUTPUT->main_content();
            echo $OUTPUT->course_content_footer();
            ?>
        </section>
        <?php
        $classextra = '';
        if ($left) {
            $classextra = ' desktop-first-column';
        }
        echo $OUTPUT->blocks('side-pre', 'span3'.$classextra);
        ?>
    </div>

    <div class="ucsf_logininfo"><?php echo $OUTPUT->login_info();?></div>

</div>

<div class="main-footer">
    <footer id="page-footer" class="container-fluid ">
        <div class=""></div>
        <div class="ucsf_footer_text"><?php echo $html->copyright; ?></div>
        <div class="ucsf_footer_links_container"><?php echo $html->footnote; ?></a></div>
        <div id="course-footer"><?php echo $OUTPUT->course_footer(); ?></div>
        <?php
        echo $OUTPUT->standard_footer_html();
        ?>
    </footer>
    <?php echo $OUTPUT->standard_end_of_body_html() ?>
</div>
</body>
</html>