<?php

namespace Deployer;

use SourceBroker\DeployerExtended\Utility\DatabaseUtility;
use SourceBroker\DeployerExtended\Utility\FileUtility;

task('db:import', function () {
    if (input()->getOption('dumpcode')) {
        $dumpCode = input()->getOption('dumpcode');
    } else {
        throw new \RuntimeException('No dumpCode set. [Error code: 1458937128560]');
    }
    if (null !== input()->getArgument('stage')) {
        throw new \RuntimeException("You can not set targt instance for db:import command.");
    }
    $currentInstanceDatabaseStoragePath = get('db_settings_storage_path');
    foreach (get('databases_config') as $databaseCode => $databaseConfig) {
        $link = mysqli_connect($databaseConfig['host'], $databaseConfig['user'], $databaseConfig['password'], $databaseConfig['dbname']);

        $glob = $currentInstanceDatabaseStoragePath . DIRECTORY_SEPARATOR
            . '*dbcode:' . FileUtility::normalizeFilename($databaseCode)
            . '*type:structure'
            . '*dumpcode:' . $dumpCode
            . '.sql';
        $structureSqlFile = glob($glob);

        if (empty($structureSqlFile)) {
            throw new \RuntimeException('No structure file for $dumpCode: ' . $dumpCode . '. Glob build: ' . $glob . '.  [Error code: 1458494633]');
        }

        $dataSqlFile = glob($currentInstanceDatabaseStoragePath . DIRECTORY_SEPARATOR
            . '*dbcode:' . FileUtility::normalizeFilename($databaseCode)
            . '*type:data'
            . '*dumpcode:' . $dumpCode
            . '.sql');

        if (empty($dataSqlFile)) {
            throw new \RuntimeException('No structure file for $dumpCode: ' . $dumpCode . '  [Error code: 1458494633]');
        }

        // drop all tables before import of database
        $allTables = DatabaseUtility::getTables($databaseConfig);
        $link->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($allTables as $table) {
            $link->query(/** @lang MySQL */
                'DROP TABLE ' . $table);
        }
        $link->query('SET FOREIGN_KEY_CHECKS = 1');

        runLocally(sprintf(
            'export MYSQL_PWD=%s && %s --default-character-set=utf8 -h%s -P%s -u%s -D%s -e "SOURCE %s" ',
            escapeshellarg($databaseConfig['password']),
            get('db_settings_mysql_path'),
            $databaseConfig['host'],
            (isset($databaseConfig['port']) && $databaseConfig['port']) ? $databaseConfig['port'] : 3306,
            $databaseConfig['user'],
            $databaseConfig['dbname'],
            $structureSqlFile[0]
        ), 0);

        runLocally(sprintf(
            'export MYSQL_PWD=%s && %s --default-character-set=utf8 -h%s -P%s -u%s -D%s -e "SOURCE %s" ',
            escapeshellarg($databaseConfig['password']),
            get('db_settings_mysql_path'),
            $databaseConfig['host'],
            (isset($databaseConfig['port']) && $databaseConfig['port']) ? $databaseConfig['port'] : 3306,
            $databaseConfig['user'],
            $databaseConfig['dbname'],
            $dataSqlFile[0]
        ), 0);

        $post_sql_in_with_markers = '';
        if (isset($databaseConfig['post_sql_in_with_markers'])) {

            // Prepare some markers to use in post_sql_in_markers:
            $markersArray = [];
            // 1. "public_urls" var separated by comma. To use in SQL IN
            if (is_array(get('public_urls'))) {
                $publicUrlCollected = [];
                foreach (get('public_urls') as $publicUrl) {
                    $publicUrlCollected[] = "'" . parse_url($publicUrl)['host'] . "'";
                }
                $markersArray['{{domainsSeparatedByComma}}'] = implode(',', $publicUrlCollected);
            }
            $post_sql_in_with_markers = str_replace(array_keys($markersArray), $markersArray, $databaseConfig['post_sql_in_with_markers']);
        }

        $importSql = $currentInstanceDatabaseStoragePath . DIRECTORY_SEPARATOR . $dumpCode . '.sql';
        file_put_contents($importSql, str_replace("\n", ' ', $databaseConfig['post_sql_in'] . ' ' . $post_sql_in_with_markers));
        runLocally(sprintf(
            'export MYSQL_PWD=%s && %s --default-character-set=utf8 -h%s -P%s -u%s -D%s -e "SOURCE %s" ',
            escapeshellarg($databaseConfig['password']),
            get('db_settings_mysql_path'),
            $databaseConfig['host'],
            (isset($databaseConfig['port']) && $databaseConfig['port']) ? $databaseConfig['port'] : 3306,
            $databaseConfig['user'],
            $databaseConfig['dbname'],
            $importSql
        ), 0);
        unlink($importSql);
    }
})->desc('Import the database with "dumpcode" from current database dumps storage to database.');
