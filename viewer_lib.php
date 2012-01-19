<?php

abstract class user_data_ui_element {
    protected $name;
    protected $key;
    protected $value;

    function __construct($field, $name) {
        $this->key = $field;
        $this->name = $name;

        $this->value = optional_param($this->key, null, PARAM_TEXT);
    }

    public function key() {
        return $this->key;
    }

    public function name() {
        return $this->name;
    }

    public function value() {
        return $this->value;
    }

    public function format($user) {
        return $user->{$this->key()};
    }

    public abstract function html();
    public abstract function sql();
}

class user_data_text_box extends user_data_ui_element {
    public function html() {
        $html = '<input type="text" placeholder="'. $this->name() .'" name="'.$this->key().'"';

        if (!is_null($this->value())) {
            $html .= ' value="'. $this->value().'"';
        }

        $html .= '/>';

        return $html;
    }

    public function sql() {
        if (is_null($this->value())) {
            return array();
        }

        // This will change
        return array($this->key() => $this->value());
    }
}

abstract class ues_data_viewer {
    public static function handler($field) {
        try {
            $name = get_string($field);
        } catch (Exception $e) {
            $name = $field;
        }

        $handler = new stdClass;
        $handler->ui_element = new user_data_text_box($field, $name);
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
