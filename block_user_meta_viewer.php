<?php

class block_ues_meta_viewer extends block_list {
    function init() {
        $this->title= get_string('pluginname', 'block_ues_meta_viewer');
    }

    function applicable_formats() {
        return array('site' => true, 'my' => true, 'course' => false);
    }

    function get_content() {
        if ($this->content !== NULL) {
            return $this->content;
        }

        global $USER;

        $content = new stdClass;

        $content->items = array();
        $content->icons = array();
        $content->footer = '';

        // User data query box links from this
        if (is_siteadmin($USER->id)) {
            $url = new moodle_url('/blocks/ues_meta_viewer/viewer.php');
            $str = get_string('viewer', 'block_ues_meta_viewer');

            $content->items[] = html_writer::link($url, $str);
        }

        $this->content = $content;

        return $this->content;
    }
}
