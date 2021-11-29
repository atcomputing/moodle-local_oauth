<?php

defined('MOODLE_INTERNAL') || die();

/**
 * local_oauth upgrade
 * @param string $oldversion Oldversion
 * @return bool
 */
function xmldb_local_oauth_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    // Add a new column newcol to the mdl_myqtype_options.
    if ($oldversion < 2021112802) {

        $field = new xmldb_field('use_email_aliases', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table = new xmldb_table('oauth_clients');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2021112802, 'local', 'oauth');
    }
}
