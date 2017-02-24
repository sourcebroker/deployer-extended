<?php

namespace Deployer;

use SourceBroker\DeployerExtended\Utility\ArrayUtility;
use SourceBroker\DeployerExtended\Utility\FileUtility;
use SourceBroker\DeployerExtended\Utility\DatabaseUtility;

task('db:export', function () {
    $dumpCode = md5(microtime(true) . rand(0, 10000));
    $dateTime = date('Y-m-d_H:i:s');

    $databasesEnvConfigs = get('database_env_config');
    foreach ($databasesEnvConfigs as $databaseCode => $databasesEnvConfig) {
        $filenameParts = [
            $dateTime,
            'server:' . FileUtility::normalizeFilename(get('server')['name']),
            'dbcode:' . FileUtility::normalizeFilename($databaseCode),
            'type',
            'dumpcode:' . $dumpCode,

        ];

        if (!file_exists(get('db_settings_storage_path'))) {
            mkdir(get('db_settings_storage_path'), 0755, true);
        }

        // dump database structure
        $filenameParts[3] = 'type:structure';
        $outputFileDatabaseStructure = get('db_settings_storage_path') . DIRECTORY_SEPARATOR . implode('#', $filenameParts) . '.sql';
        runLocally(sprintf(
            'export MYSQL_PWD=%s && %s --no-data=true --default-character-set=utf8 -h%s -P%s -u%s %s -r %s',
            escapeshellarg($databasesEnvConfig['password']),
            get('db_settings_mysqldump_path'),
            escapeshellarg($databasesEnvConfig['host']),
            escapeshellarg((isset($databasesEnvConfig['port']) && $databasesEnvConfig['port']) ? $databasesEnvConfig['port'] : 3306),
            escapeshellarg($databasesEnvConfig['user']),
            escapeshellarg($databasesEnvConfig['dbname']),
            $outputFileDatabaseStructure
        ), 0);


        // dump database data
        $ignoreTablesWithPatterns = $databasesEnvConfig['ignore_tables_out'];
        $allTables = DatabaseUtility::getTables($databasesEnvConfig);
        $ignoreTables = ArrayUtility::filterWithRegexp($ignoreTablesWithPatterns, $allTables);

        $ignoreTablesCmd = ($ignoreTables) ? '--ignore-table=' . $databasesEnvConfig['dbname'] . '.%s' : '%s';
        //print_r(implode(' --ignore-table=' . $databasesEnvConfig['dbname'] . '.', $ignoreTables)); die('$ignoreTablesCmd at 20.03.2016 19:54');
        $filenameParts[3] = 'type:data';
        $outputFileDatabaseData = get('db_settings_storage_path') . DIRECTORY_SEPARATOR . implode('#', $filenameParts) . '.sql';
        runLocally(sprintf(
            'export MYSQL_PWD=%s && %s --create-options -e -K -q -n --default-character-set=utf8 -h%s -P%s -u%s %s -r %s ' . $ignoreTablesCmd,
            escapeshellarg($databasesEnvConfig['password']),
            get('db_settings_mysqldump_path'),
            escapeshellarg($databasesEnvConfig['host']),
            escapeshellarg((isset($databasesEnvConfig['port']) && $databasesEnvConfig['port']) ? $databasesEnvConfig['port'] : 3306),
            escapeshellarg($databasesEnvConfig['user']),
            escapeshellarg($databasesEnvConfig['dbname']),
            $outputFileDatabaseData,
            implode(' --ignore-table=' . $databasesEnvConfig['dbname'] . '.', $ignoreTables)
        ), 0);
    }
    $json['dumpCode'] = $dumpCode;
    echo json_encode($json);
})->desc('Export database dump to local database dumps storage. Returns dumpcode as json.');
