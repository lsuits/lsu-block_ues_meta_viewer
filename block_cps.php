<?php

class block_cps extends block_list {
    function init() {
        $this->title= get_string('pluginname', 'block_cps');
    }

    function applicable_formats() {
        return array('site' => true, 'my' => true, 'course' => false);
    }

    function get_content() {
        if ($this->content !== NULL) {
            return $this->content;
        }

        global $CFG, $USER;

        require_once $CFG->dirroot . '/blocks/cps/classes/lib.php';

        $content = new stdClass;

        $content->items = array();
        $content->icons = array();
        $content->footer = '';

        // User data query box links from this
        if (is_siteadmin($USER->id)) {
            $url = new moodle_url('/blocks/cps/viewer.php');
            $str = get_string('viewer', 'block_cps');

            $content->items[] = html_writer::link($url, $str);
        }

        $this->content = $content;

        if (!ues_user::is_teacher()) {
            return $this->content;
        }

        $sections = ues_user::sections(true);
        $courses = ues_course::merge_sections($sections);

        $preferences = cps_preferences::settings();

        foreach ($preferences as $setting => $name) {
            $url = new moodle_url("/blocks/cps/$setting.php");

            $obj = 'cps_' . $setting;

            if (method_exists($obj, 'is_valid') and !$obj::is_valid($courses)) {
                continue;
            }

            $this->content->items[] = html_writer::link($url, $name);
        }

        return $this->content;
    }
}
