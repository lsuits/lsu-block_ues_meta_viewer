<?php

require_once '../../config.php';
require_once $CFG->dirroot . '/enrol/ues/publiclib.php';
ues::require_daos();

require_once 'lib.php';

require_login();

$supported_types = ues_meta_viewer::supported_types();

$type = required_param('type', PARAM_TEXT);

if (!isset($supported_types[$type])) {
    print_error('unsupported_type', 'block_ues_meta_viewer', '', $type);
}

$supported_type = $supported_types[$type];

if (!$supported_type->can_use()) {
    print_error('unsupported_type', 'block_ues_meta_viewer', '', $type);
}

$class = $supported_type->wrapped_class();

$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 100, PARAM_INT);

$_s = ues::gen_str('block_ues_meta_viewer');

$blockname = $_s('pluginname');
$heading = $_s('viewer', $supported_type->name());

$context = get_context_instance(CONTEXT_SYSTEM);

$PAGE->set_context($context);
$PAGE->set_heading($blockname . ': '. $heading);
$PAGE->set_title($heading);
$PAGE->navbar->add($blockname);
$PAGE->navbar->add($heading);
$PAGE->set_title($heading);
$PAGE->set_url('/blocks/ues_meta_viewer/viewer.php', array('type' => $type));

echo $OUTPUT->header();
echo $OUTPUT->heading($heading);

$fields = ues_meta_viewer::generate_keys($type, $class, $USER);

$head = array();
$search = array();
$handlers = array();
$select = array();
$params = array('type' => $type);

foreach ($fields as $field) {
    $handler = ues_meta_viewer::handler($type, $field);

    $head[] = $handler->name();
    $search[] = $handler->html();
    $handlers[] = $handler;

    $value = $handler->value();
    // Only add searched fields as GET param
    if (trim($value) !== '') {
        $params[$field] = $value;
    }
}

$search_table = new html_table();
$search_table->head = $head;
$search_table->data = array(new html_table_row($search));

if (!empty($_REQUEST['search'])) {
    $by_filters = ues_meta_viewer::sql($handlers);

    $count = $class::count($by_filters);
    $res = $class::get_all($by_filters, true, '', '*', $page + ($page * $perpage), $perpage);

    $params['search'] = get_string('search');

    $result = $count ?
        ues_meta_viewer::result_table($res, $handlers) :
        null;
    $posted = true;
} else {
    $count = 0;
    $posted = false;
}

$baseurl = new moodle_url('viewer.php', $params);

$hidden = function($name, $value) {
    return html_writer::empty_tag('input', array(
        'name' => $name, 'value' => $value, 'type' => 'hidden'
    ));
};

echo html_writer::start_tag('form', array('method' => 'POST', 'class' => 'search-form'));
echo html_writer::tag('div', html_writer::table($search_table), array('class' => 'search-table'));
echo html_writer::start_tag('div', array('class' => 'search-buttons center padded'));
echo $hidden('page', 0) . $hidden('type', $type);
echo html_writer::empty_tag('input', array(
    'type' => 'submit', 'name' => 'search', 'value' => get_string('search')
));
echo html_writer::end_tag('div');
echo html_writer::end_tag('form');

if ($posted) {
    echo html_writer::start_tag('div', array('class' => 'results'));
    if (empty($result)) {
        echo html_writer::start_tag('div', array('class' => 'no-results center padded'));
        echo $_s('no_results');
        echo html_writer::end_tag('div');
    } else {
        echo html_writer::tag('div', $_s('found_results') . ' ' . $count,
            array('class' => 'count-results center padded'));
        echo $OUTPUT->paging_bar($count, $page, $perpage, $baseurl->out());
        echo html_writer::tag('div', html_writer::table($result),
            array('class' => 'results-table margin-center'));
    }
    echo html_writer::end_tag('div');
}

echo $OUTPUT->footer();
