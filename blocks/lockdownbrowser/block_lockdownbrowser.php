<?php
// Respondus LockDown Browser Extension for Moodle
// Copyright (c) 2011-2014 Respondus, Inc.  All Rights Reserved.
// Date: May 28, 2014.

class block_lockdownbrowser extends block_base {

    public function init() {

        $this->content_type = BLOCK_TYPE_TEXT;

        // ensure title is unique even if string table is unavailable
        $this->title = get_string("lockdownbrowser", "block_lockdownbrowser");
    }

    public function get_content() {

        global $CFG, $COURSE;

        if ($this->content != null) {

            return $this->content;
        }

        $this->content       = new stdClass;
        $this->content->text = '';

        if (has_capability('moodle/course:manageactivities', get_context_instance(CONTEXT_COURSE, $COURSE->id))) {

            $this->content->footer = '<a href="' . $CFG->wwwroot . '/blocks/lockdownbrowser/dashboard.php?course=' .
                $COURSE->id . '">' . get_string('dashboard', 'block_lockdownbrowser') . ' ...</a>';
        } else {
            $this->content->footer = '';
        }

        return $this->content;
    }

    public function instance_allow_multiple() {

        return false;
    }

    public function applicable_formats() {

        return array(
            'site-index'         => false,
            'course-view'        => true,
            'course-view-social' => false,
            'mod'                => false,
            'mod-quiz'           => false
        );
    }

    public function has_config() {

        return true;
    }
}

