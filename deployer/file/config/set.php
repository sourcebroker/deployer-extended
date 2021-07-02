<?php

namespace Deployer;

use SourceBroker\DeployerInstance\Instance;
use SourceBroker\DeployerInstance\Configuration;

// Returns path to store file backups.
set('file_backup_path', function () {
    if (!get('file_backup_path_relative', false)) {
        $path = get('deploy_path') . '/.dep/backups';
    } else {
        $path = get('deploy_path') . '/' . get('file_backup_path_relative');
    }
    if (empty(get('argument_stage'))) {
        runLocally('[ -d ' . $path . ' ] || mkdir -p ' . $path);
    } else {
        run('[ -d ' . $path . ' ] || mkdir -p ' . $path);
    }
    return $path;
});
