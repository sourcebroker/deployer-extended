<?php

namespace Deployer;

use SourceBroker\DeployerExtended\Utility\ArrayUtility;
use SourceBroker\DeployerExtended\Utility\DatabaseUtility;

task('db:truncate', function () {
    if (get('instance') == get('server')['name']) {
        foreach (get('databases_config') as $databaseCode => $databaseConfig) {
            $cachingTablesWithPatterns = $databaseConfig['caching_tables'];
            $cachingTables = ArrayUtility::filterWithRegexp($cachingTablesWithPatterns, DatabaseUtility::getTables($databaseConfig));
            foreach ($cachingTables as $cachingTable) {
                runLocally(sprintf(
                    'export MYSQL_PWD=%s && %s --default-character-set=utf8 -h%s -P%s -u%s -D%s -e \'%s\' ',
                    escapeshellarg($databaseConfig['password']),
                    get('db_settings_mysql_path'),
                    $databaseConfig['host'],
                    (isset($databaseConfig['port']) && $databaseConfig['port']) ? $databaseConfig['port'] : 3306,
                    $databaseConfig['user'],
                    $databaseConfig['dbname'],
                    'TRUNCATE ' . $cachingTable), 0);
            }
            writeln('<info>Truncated tables: ' . implode(',', $cachingTables) . '</info>');
        };
    } else {
        run("cd {{deploy_path}}/current && {{bin/php}} deployer.phar -q db:truncate");
    }
})->desc('Truncate tables defined as caching tables.');
