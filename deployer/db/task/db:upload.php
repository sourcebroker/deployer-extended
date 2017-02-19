<?php

namespace Deployer;

task('db:upload', function () {

    $dumpCode = null;
    if (input()->hasOption('dumpcode')) {
        $dumpCode = input()->getOption('dumpcode');
    } else {
        throw new \RuntimeException('No --dumpcode option set. [Error code: 1458937128560]');
    }

    $stage = input()->getArgument('stage');
    $targetEnv = Deployer::get()->environments[$stage];

    $locaDatabaselStoragePath = get('db_get_local_server')->get('db_settings_storage_path');
    $remoteDatsbaseStoragePath = $targetEnv->get('db_settings_storage_path');

    $targetServer = Task\Context::get()->getServer()->getConfiguration();
    $host = $targetServer->getHost();
    $port = $targetServer->getPort() ? ' -p' . $targetServer->getPort() : '';
    $identityFile = $targetServer->getPrivateKey() ? ' -i ' . $targetServer->getPrivateKey() : '';
    $user = !$targetServer->getUser() ? '' : $targetServer->getUser() . '@';
    run("[ -d " . $remoteDatsbaseStoragePath . " ] || mkdir -p " . $remoteDatsbaseStoragePath);
    runLocally("rsync -rz --remove-source-files -e 'ssh$port$identityFile' --include=*dumpcode:$dumpCode*.sql --exclude=* '$locaDatabaselStoragePath/' '$user$host:$remoteDatsbaseStoragePath/'", 0);

})->desc('Upload the latest database dump from remote database dumps storage.');