<?php

namespace Deployer;

task('db:upload', function () {
    if (input()->hasOption('dumpcode')) {
        $dumpCode = input()->getOption('dumpcode');
    } else {
        throw new \InvalidArgumentException('No --dumpcode option set. [Error code: 1458937128560]');
    }

    $targetInstance = Task\Context::get()->getServer()->getConfiguration();

    $targetInstnceDatabaseStoragePath = get('db_settings_storage_path');

    $port = $targetInstance->getPort() ? ' -p' . $targetInstance->getPort() : '';
    $identityFile = $targetInstance->getPrivateKey() ? ' -i ' . $targetInstance->getPrivateKey() : '';
    $sshOptions = '';
    if ($port != '' || $identityFile != '') {
        $sshOptions = '-e ssh ' . escapeshellarg($port . $identityFile);
    }

    run("[ -d " . $targetInstnceDatabaseStoragePath . " ] || mkdir -p " . $targetInstnceDatabaseStoragePath);

    runLocally(sprintf(
        "rsync -rz --remove-source-files %s --include=*dumpcode:%s*.sql --exclude=* '%s/' '%s%s:%s/'",
        $sshOptions,
        $dumpCode,
        get('current_server')->get('db_settings_storage_path'),
        $targetInstance->getUser() ? $targetInstance->getUser() . '@' : '',
        $targetInstance->getHost(),
        $targetInstnceDatabaseStoragePath
    ), 0);
})->desc('Upload the latest database dump from target database dumps storage.');
