<?php

namespace Deployer;

task('deploy:check_lock', function () {
    if (file_exists('./deploy.lock')) {
        $deployLockFileContent = trim(file_get_contents('./deploy.lock'));
        if (!empty($deployLockFileContent)) {
            $message = 'Deployment stopped! There is deploy.lock file with following message: ' . $deployLockFileContent;
        } else {
            $message = 'Deployment stopped! There is deploy.lock file.';
        }
        throw new \RuntimeException($message);
    }
})->desc('Check for deploy.lock file existence and stop deploy if there.');
