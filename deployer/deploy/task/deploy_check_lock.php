<?php

namespace Deployer;

use Deployer\Exception\GracefulShutdownException;

// Read more on https://github.com/sourcebroker/deployer-extended#deploy-check-lock
task('deploy:check_lock', function () {
    if (file_exists(get('current_dir') . '/deploy.lock')) {
        $deployLockFileContent = trim(file_get_contents(get('current_dir') . '/deploy.lock'));
        if (!empty($deployLockFileContent)) {
            $message = 'Deployment stopped! There is deploy.lock file with following message: ' . $deployLockFileContent;
        } else {
            $message = 'Deployment stopped! There is deploy.lock file.';
        }
        throw new GracefulShutdownException($message);
    }
})->desc('Check for deploy.lock file existence and stop deploy if there');
