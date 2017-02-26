<?php

namespace Deployer;

task('db:download', function () {
    if (input()->getOption('dumpcode')) {
        $dumpCode = input()->getOption('dumpcode');
    } else {
        throw new \InvalidArgumentException('No --dumpcode option set. [Error code: 1458937128561]');
    }

    if (null === input()->getArgument('stage')) {
        throw new \RuntimeException("The target instance is required for db:download command.");
    }

    $currentInstanceStoragePath = get('current_server')->get('db_settings_storage_path');
    if (!file_exists($currentInstanceStoragePath)) {
        mkdir($currentInstanceStoragePath, 0755, true);
    }
    $targetInstance = Task\Context::get()->getServer()->getConfiguration();

    $port = $targetInstance->getPort() ? ' -p' . $targetInstance->getPort() : '';
    $identityFile = $targetInstance->getPrivateKey() ? ' -i ' . $targetInstance->getPrivateKey() : '';
    if ($port !== '' || $identityFile !== '') {
        $sshOptions = '-e ' . escapeshellarg('ssh ' . $port . $identityFile);
    } else {
        $sshOptions = '';
    }

    runLocally(sprintf(
        "rsync -rz --remove-source-files %s --include=*dumpcode:%s*.sql --exclude=* '%s%s:%s/' '%s/'",
        $sshOptions,
        $dumpCode,
        $targetInstance->getUser() ? $targetInstance->getUser() . '@' : '',
        $targetInstance->getHost(),
        get('db_settings_storage_path'),
        $currentInstanceStoragePath
    ), 0);
})->desc('Download the database dumps with dumpcode from target database dumps storage.');
