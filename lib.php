<?php

require_once $CFG->dirroot . '/blocks/ues_meta_viewer/classes/lib.php';

abstract class ues_data_viewer {
    public static function sql($handlers) {
        $flatten = function($dsl, $handler) {
            return $handler->sql($dsl);
        };

        // What I'd give for an optional here
        try {
            $filters = array_reduce($handlers, $flatten, ues::where());

            // Catch empty
            $filters->get();
            return $filters;
        } catch (Exception $e) {
            return array();
        }
    }

    public static function result_table($users, $handlers) {
        $table = new html_table();
        $table->head = array();
        $table->data = array();

        foreach ($handlers as $handler) {
            $table->head[] = $handler->name();
        }

        foreach ($users as $id => $user) {
            $format = function($handler) use ($user) {
                return $handler->format($user);
            };

            $table->data[] = array_map($format, $handlers);
        }

        return $table;
    }

    public static function handler($field) {
        $name = get_string($field);

        if ($name == "[[$field]]") {
            $name = $field;
        }

        $handler = new stdClass;
        $handler->ui_element = new meta_data_text_box($field, $name);

        events_trigger('user_data_ui_element', array($handler));
        return $handler->ui_element;
    }

    public static function generate_keys($user) {
        $fields = new stdClass;

        $fields->user = $user;
        $fields->keys = array(
            'username', 'idnumber', 'firstname', 'lastname'
        );

        // Auto fill based on system
        $additional_fields = ues_user::get_meta_names();
        foreach ($additional_fields as $field) {
            $fields->keys[] = $field;
        }

        // Should this user see appropriate fields?
        events_trigger('user_data_ui_keys', $fields);

        return $fields->keys;
    }
}
