<?php

/*
 * @author    Shaun Daubney
 * @package   theme_aardvark
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function aardvark_process_css($css, $theme) {
	
    // Set the menu background color
    if (!empty($theme->settings->menubackcolor)) {
        $menubackcolor = $theme->settings->menubackcolor;
    } else {
        $menubackcolor = null;
    }
    $css = aardvark_set_menubackcolor($css, $menubackcolor);
	
	    // Set the menu hover color
    if (!empty($theme->settings->menuhovercolor)) {
        $menuhovercolor = $theme->settings->menuhovercolor;
    } else {
        $menuhovercolor = null;
    }
    $css = aardvark_set_menuhovercolor($css, $menuhovercolor);

    
	// Set the background image for the graphic wrap 
    if (!empty($theme->settings->backimage)) {
        $backimage = $theme->settings->backimage;
    } else {
        $backimage = null;
    }
    $css = aardvark_set_backimage($css, $backimage);
	
	// Set the graphic position
    if (!empty($theme->settings->backposition)) {
       $backposition = $theme->settings->backposition;
    } else {
       $backposition = null;
    }
    $css = aardvark_set_backposition($css,$backposition);
	
	// Set the background color
    if (!empty($theme->settings->backcolor)) {
        $backcolor = $theme->settings->backcolor;
    } else {
        $backcolor = null;
    }
    $css = aardvark_set_backcolor($css, $backcolor);
	
	// Set the background image for the logo 
    if (!empty($theme->settings->logo)) {
        $logo = $theme->settings->logo;
    } else {
        $logo = null;
    }
    $css = aardvark_set_logo($css, $logo);
	
	    // Set custom CSS
    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = aardvark_set_customcss($css, $customcss);
    
    return $css;
}

function aardvark_set_menubackcolor($css, $menubackcolor) {
    $tag = '[[setting:menubackcolor]]';
    $replacement = $menubackcolor;
    if (is_null($replacement)) {
        $replacement = '#333333';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function aardvark_set_menuhovercolor($css, $menuhovercolor) {
    $tag = '[[setting:menuhovercolor]]';
    $replacement = $menuhovercolor;
    if (is_null($replacement)) {
        $replacement = '#f42941';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function aardvark_set_backimage($css, $backimage) {
	global $OUTPUT;  
	$tag = '[[setting:backimage]]';
	$replacement = $backimage;
	if (is_null($replacement)) {
 		$replacement = '';
 	}
	$css = str_replace($tag, $replacement, $css);
	return $css;
}

function aardvark_set_backposition($css, $backposition = 'no-repeat', $tag = '[[setting:backposition]]'){
if($backposition == "no-repeat" || $backposition == "no-repeat fixed" || $backposition == "repeat" || $backposition == "repeat-x"){
$css = str_replace($tag, $backposition, $css);
}
return $css;
}

function aardvark_set_backcolor($css, $backcolor) {
    $tag = '[[setting:backcolor]]';
    $replacement = $backcolor;
    if (is_null($replacement)) {
        $replacement = '#ffffff';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function aardvark_set_logo($css, $logo) {
	global $OUTPUT;  
	$tag = '[[setting:logo]]';
	$replacement = $logo;
	$css = str_replace($tag, $replacement, $css);
	return $css;
}

function aardvark_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}


