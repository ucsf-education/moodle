<?php
// Respondus LockDown Browser Extension for Moodle
// Copyright (c) 2011-2014 Respondus, Inc.  All Rights Reserved.
// Date: May 28, 2014.

class backup_lockdownbrowser_block_structure_step extends backup_block_structure_step {

    protected function define_structure() {

        $settings = new backup_nested_element(
            "settings", array("id"), array(
                                          "course",
                                          "quizid",
                                          "attempts",
                                          "reviews",
                                          "password",
                                          "monitor"
                                     ));

        $settings->set_source_table(
            "block_lockdownbrowser_sett", array("course" => backup::VAR_COURSEID));

        $settings->annotate_ids("quiz", "quizid");

        return $this->prepare_block_structure($settings);
    }
}
