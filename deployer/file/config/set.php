<?php

namespace Deployer;

use SourceBroker\DeployerInstance\Instance;
use SourceBroker\DeployerInstance\Configuration;

// Returns path to store file backups on remote instance.
set('file_backup_path', function () {
    $currentInstance = (new Instance)->getCurrentInstance();
    $environment = Configuration::getEnvironment($currentInstance);

    if (!$environment->get('file_backup_path_relative', false)) {
        $path = $environment->get('deploy_path') . '/.dep/backups';
    } else {
        $path = $environment->get('deploy_path') . '/' . $environment->get('file_backup_path_relative');
    }
    run('[ -d ' . $path . ' ] || mkdir -p ' . $path);
    return $path;
});
