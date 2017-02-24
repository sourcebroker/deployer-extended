<?php

namespace Deployer;

task('db:pull', function () {
    $targetServer = get('db_get_local_server')->get('server')['name'];

    // export fresh database dump to remote database dump storage
    $databaseDumpResult = run("cd {{deploy_path}}/current && {{bin/php}} deployer.phar -q db:export " . get('server')['name']);
    $databaseDumpResponse = json_decode(trim($databaseDumpResult->toString()), true);

    // download the latest database dumps from remote database dump storage
    runLocally("{{deployer_exec}} db:download " . get('server')['name'] . " --dumpcode=" . $databaseDumpResponse['dumpCode'], 0);

    // processing downloaded dump files
    runLocally("{{deployer_exec}} db:process_dump " . $targetServer . " --dumpcode=" . $databaseDumpResponse['dumpCode'], 0);

    // import the latest database dumps from local database dump storage
    runLocally("{{deployer_exec}} db:import " . $targetServer . " --dumpcode=" . $databaseDumpResponse['dumpCode'], 0);
})->desc('Synchronize database from remote instance to local instance.');
