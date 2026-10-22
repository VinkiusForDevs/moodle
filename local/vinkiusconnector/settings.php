<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_externalpage(
        'local_vinkiusconnector',
        get_string('pluginname', 'local_vinkiusconnector'),
        $CFG->wwwroot . '/local/vinkiusconnector/index.php',
        'local/vinkiusconnector:manage'
    ));
}