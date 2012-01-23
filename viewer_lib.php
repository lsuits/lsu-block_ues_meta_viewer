<?php

abstract class user_data_ui_element {
    protected $name;
    protected $key;
    protected $value;

    function __construct($field, $name) {
        $this->key = $field;

        if ($field == $name) {
            $name = get_string($field, 'block_cps');
        }

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
        if (!isset($user->{$this->key()})) {
            return get_string('not_available', 'block_cps');
        }

        return $user->{$this->key()};
    }

    public function translate_value($dsl) {
        $value = trim($this->value());
        $strip = function ($what) use ($value) {
            return preg_replace('/%/', '', $value);
        };

        if (strpos($value, ',')) {
            return $dsl->in(explode(',', $value));
        } else if (strpos($value, '%') === 0 and strpos($value, '%', 1) > 0) {
            return $dsl->like($strip('%'));
        } else if (strpos($value, '%') === 0) {
            return $dsl->ends_with($strip('%'));
        } else if (strpos($value, '%') > 0) {
            return $dsl->starts_with($strip('%'));
        } else if (strpos($value, '<') === 0) {
            return $dsl->less($strip('<'));
        } else if (strpos($value, '>') === 0) {
            return $dsl->greater($strip('>'));
        } else if (strtolower($value) == 'null') {
            return $dsl->is(NULL)->equal('');
        } else if (strtolower($value) == 'not null') {
            return $dsl->not_equal('');
        } else {
            return $dsl->equal($value);
        }
    }

    public abstract function html();
    public abstract function sql($dsl);
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

    public function sql($dsl) {
        $key = $this->key();
        $value = $this->value();

        if (empty($value)) {
            return $dsl;
        }

        return $this->translate_value($dsl->{$key});
    }
}

abstract class ues_data_viewer {
    public static function users($handlers) {
        $flatten = function($dsl, $handler) {
            return $handler->sql($dsl);
        };

        // What I'd give for an optional here
        try {
            $res = array_reduce($handlers, $flatten, ues::where());

            return ues_user::get_all($res, true);
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
