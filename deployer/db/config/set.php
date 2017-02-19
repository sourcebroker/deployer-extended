<?php

namespace Deployer;

use SourceBroker\DeployerExtended\Utility\ArrayUtility;

// local means here the server where the command is run on (and not local development instance)
set('db_get_local_server', function () {
    try {
        $localServer = Deployer::get()->environments[get('instance')];
    } catch (\RuntimeException $e) {
        $servers = '';
        $i = 1;
        foreach (Deployer::get()->environments as $key => $server) {
            $servers .= "\n" . $i++ . '. ' . $key;
        }
        throw new \RuntimeException("Name of instance \"" . get('instance') . "\" is not on the server list:" . $servers . "\nPlease check case sensitive. [Error code: 1458412947]");
    }

    return $localServer;
});

set('db_settings_mysqldump_path', function () {
    if (runLocally('if hash mysqldump 2>/dev/null; then echo \'true\'; fi')->toBool()) {
        return 'mysqldump';
    } else {
        throw new \RuntimeException('The mysqldump path on server "' . get('server')['name'] . '" is unknown. You can set it in env var "db_settings_mysqldump_path" . [Error code: 1458412747]');
    }
});

set('db_settings_mysql_path', function () {
    if (runLocally('if hash mysql 2>/dev/null; then echo \'true\'; fi')->toBool()) {
        return 'mysql';
    } else {
        throw new \RuntimeException('The mysql path on server "' . get('server')['name'] . '" is unknown. You can set it in env var "db_settings_mysql_path" . [Error code: 1458412748]');
    }
});

set('database_env_config', function () {
    $databaseConfigsServerEnv = get('db_databases');
    $databaseConfigsMerged = [];
    foreach ($databaseConfigsServerEnv as $databaseConfigServerEnv) {

        if (is_array($databaseConfigServerEnv)) {
            $databaseConfigsMerged = ArrayUtility::arrayMergeRecursiveDistinct($databaseConfigsMerged, $databaseConfigServerEnv);
            continue;
        }

        if (file_exists(get('current_dir') . DIRECTORY_SEPARATOR . $databaseConfigServerEnv)) {
            $mergeArray = include(get('current_dir') . DIRECTORY_SEPARATOR . $databaseConfigServerEnv);
            $databaseConfigsMerged = ArrayUtility::arrayMergeRecursiveDistinct($databaseConfigsMerged, $mergeArray);
        } else {
            throw new \RuntimeException('The config file does not exists: ' . $databaseConfigServerEnv);
        }
    }
    return $databaseConfigsMerged;

});
