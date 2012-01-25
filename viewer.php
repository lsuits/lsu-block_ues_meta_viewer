<?php

require_once '../../config.php';
require_once $CFG->dirroot . '/enrol/ues/publiclib.php';
ues::require_daos();

require_once 'viewer_lib.php';
require_once $CFG->libdir . '/quick_template/lib.php';

require_login();

// Non-admins shouldn't need to see this
if (!is_siteadmin($USER->id)) {
    redirect('/my');
}

$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 100, PARAM_INT);

$_s = ues::gen_str('block_cps');

$blockname = $_s('pluginname');
$heading = $_s('viewer');

$context = get_context_instance(CONTEXT_SYSTEM);

$PAGE->set_context($context);
$PAGE->set_heading($blockname . ': '. $heading);
$PAGE->navbar->add($blockname);
$PAGE->navbar->add($heading);
$PAGE->set_url('/blocks/cps/viewer.php');

echo $OUTPUT->header();
echo $OUTPUT->heading($heading);

$fields = ues_data_viewer::generate_keys($USER);

$head = array();
$search = array();
$handlers = array();
$select = array();
$params = array();

foreach ($fields as $field) {
    $handler = ues_data_viewer::handler($field);

    $head[] = $handler->name();
    $search[] = $handler->html();
    $handlers[] = $handler;

    $value = $handler->value();
    // Only add searched fields as GET param
    if (!empty($value)) {
        $params[$field] = $value;
    }
}

$search_table = new html_table();
$search_table->head = $head;
$search_table->data = array(new html_table_row($search));

if (!empty($_REQUEST['search'])) {
    $by_filters = ues_data_viewer::sql($handlers);

    $count = ues_user::count($by_filters);
    $users = ues_user::get_all($by_filters, true, 'lastname ASC', '*', $page, $perpage);

    $params['search'] = get_string('search');

    $result = $count ?
        ues_data_viewer::result_table($users, $handlers) :
        null;
    $posted = true;
} else {
    $count = 0;
    $result = $_s('search');
    $posted = false;
}

$baseurl = new moodle_url('viewer.php', $params);

$data = array(
    'search' => $search_table,
    'posted' => $posted,
    'result' => $result,
    'count' => $count,
    'paging' => $count ? $OUTPUT->paging_bar($count, $page, $perpage, $baseurl->out()) : 0
);

$registers = array(
    'function' => array(
        'print' => function ($params, &$smarty) {
            return html_writer::table($params['table']);
        }
    )
);

quick_template::render('viewer.tpl', $data, 'block_cps', $registers);

echo $OUTPUT->footer();
