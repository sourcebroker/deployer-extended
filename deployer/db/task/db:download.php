<?php

namespace Deployer;

task('db:download', function () {
    $localStoragePath = get('db_get_local_server')->get('db_settings_storage_path');
    if (!file_exists($localStoragePath)) {
        mkdir($localStoragePath, 0755, true);
    }
    $remoteStoragePath = get('db_settings_storage_path');

    $dumpCode = null;
    if (input()->hasOption('dumpcode')) {
        $dumpCode = input()->getOption('dumpcode');
    }

    $server = Task\Context::get()->getServer()->getConfiguration();
    $host = $server->getHost();
    $port = $server->getPort() ? ' -p' . $server->getPort() : '';
    $identityFile = $server->getPrivateKey() ? ' -i ' . $server->getPrivateKey() : '';
    $user = !$server->getUser() ? '' : $server->getUser() . '@';

    runLocally("rsync -rz --remove-source-files -e 'ssh$port$identityFile' --include=*dumpcode:$dumpCode*.sql --exclude=* '$user$host:$remoteStoragePath/' '$localStoragePath/'", 0);
})->desc('Download the database dumps with dumpcode from remote database dumps storage.');
