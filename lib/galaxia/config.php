<?php

/**
 * Configuration of the Galaxia Workflow Engine for Xaraya
 */

// Common prefix used for all database table names, e.g. xar_workflow_
if (!defined('GALAXIA_TABLE_PREFIX')) {
    define('GALAXIA_TABLE_PREFIX', xarDB::getPrefix() . '_workflow_');
}

// Directory containing the Galaxia library, e.g. lib/galaxia
if (!defined('GALAXIA_LIBRARY')) {
    define('GALAXIA_LIBRARY', dirname(__FILE__));
}

// Directory where the galaxia processes will be stored, e.g. lib/galaxia/processes
if (!defined('GALAXIA_PROCESSES')) {
    // Note: this directory must be writeable by the webserver !
    //define('GALAXIA_PROCESSES', GALAXIA_LIBRARY . '/processes');
    define('GALAXIA_PROCESSES', 'var/processes');
}

// Directory where a *copy* of the Galaxia activity templates will be stored, e.g. templates
// Define as '' if you don't want to copy templates elsewhere
if (!defined('GALAXIA_TEMPLATES')) {
    // Note: this directory must be writeable by the webserver !
    //define('GALAXIA_TEMPLATES', 'templates');
    define('GALAXIA_TEMPLATES', '');
}

// Default header to be added to new activity templates
if (!defined('GALAXIA_TEMPLATE_HEADER')) {
    //define('GALAXIA_TEMPLATE_HEADER', '{*Smarty template*}');
    define('GALAXIA_TEMPLATE_HEADER', '');
}

// File where the ProcessManager logs for Galaxia will be saved, e.g. lib/galaxia/log/pm.log
// Define as '' if you don't want to use logging
if (!defined('GALAXIA_LOGFILE')) {
    // Note: this file must be writeable by the webserver !
    //define('GALAXIA_LOGFILE', GALAXIA_LIBRARY . '/log/pm.log');
    define('GALAXIA_LOGFILE', '');
}

// Directory containing the GraphViz 'dot' and 'neato' programs, in case
// your webserver can't find them via its PATH environment variable
if (!defined('GRAPHVIZ_BIN_DIR')) {
    //define('GRAPHVIZ_BIN_DIR', 'c:/Program\ Files/ATT/GraphViz/bin');
    //define('GRAPHVIZ_BIN_DIR', 'd:/wintools/ATT/GraphViz/bin');
}

/**
 * Xaraya-specific adaptations
 */

// FIXME: this does not work (yet) with PDO
// Database handler
if (!isset($GLOBALS['dbGalaxia'])) {
    if(defined('xarCore::GENERATION') && xarCore::GENERATION == 2) {

    // CHECKME: we need a connection *without* COMPAT_ASSOC_LOWER flags here, but xaraya sets this
    //          by default now. So we get another connection with the same DSN and without the flags
        $conn = xarDB::getConn();
        $dsn = $conn->getDSN();
        $flags = 0;
        $GLOBALS['dbGalaxia'] = xarDB::getConnection($dsn, $flags);

        // This means we're in the 2 series of Xaraya
        define('GALAXIA_FETCHMODE',ResultSet::FETCHMODE_ASSOC);
    } else {
        // Hope that everything works out :-)
    }
}
assert(isset($GLOBALS['dbGalaxia']));

// Specify how to execute a non-interactive activity (for use in /api/instance.php)
if (!function_exists('galaxia_execute_activity')) {
    function galaxia_execute_activity($activityId = 0, $iid = 0, $auto = 1)
    {
        $result = xarModAPIFunc('workflow','user','run_activity',
                                array('activityId' => $activityId,
                                      'iid' => $iid,
                                      'auto' => $auto));
    }
}

?>
