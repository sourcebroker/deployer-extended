<?php

namespace Deployer;

task('deploy:composer_install_check', function(){

    exec('composer install --dry-run 2>&1 ', $output);
    $outputInline = join(' ', $output);
    if(strpos($outputInline, 'Nothing to install or update') === FALSE){
        throw new \RuntimeException('Please run composer install command');
    }

})->desc('Check composer install output');