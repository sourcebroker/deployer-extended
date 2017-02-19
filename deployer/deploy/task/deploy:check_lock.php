<?php

namespace Deployer;

task('deploy:check_lock', function() {

    if(file_exists('./deploy.lock')){
        throw new \RuntimeException('Please check deploy.lock file.');
    }

})->desc('Check lock file')->setPrivate();
