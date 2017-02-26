<?php

namespace Deployer;

use SourceBroker\DeployerExtended\Utility\ArrayUtility;

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

set('databases_config', function () {
    $databaseConfigsMerged = [];
    foreach (get('db_databases') as $databaseConfigServer) {
        if (is_array($databaseConfigServer)) {
            $databaseConfigsMerged = ArrayUtility::arrayMergeRecursiveDistinct($databaseConfigsMerged, $databaseConfigServer);
            continue;
        }

        if (file_exists(get('current_dir') . DIRECTORY_SEPARATOR . $databaseConfigServer)) {
            $mergeArray = include(get('current_dir') . DIRECTORY_SEPARATOR . $databaseConfigServer);
            $databaseConfigsMerged = ArrayUtility::arrayMergeRecursiveDistinct($databaseConfigsMerged, $mergeArray);
        } else {
            throw new \RuntimeException('The config file does not exists: ' . $databaseConfigServer);
        }
    }
    return $databaseConfigsMerged;
});
