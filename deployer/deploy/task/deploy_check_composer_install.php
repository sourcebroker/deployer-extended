<?php

namespace Deployer;

// Read more on https://github.com/sourcebroker/deployer-extended#deploy-check-composer-install
task('deploy:check_composer_install', function () {
    if (file_exists(get('current_dir') . '/composer.lock')) {
        $output = runLocally('{{local/bin/composer}} --ignore-platform-reqs install --dry-run 2>&1');
        if (strpos($output, 'Nothing to install') === false) {
            throw new \Exception('A composer.lock changes has been detected but you did not run "composer install". Please run composer install, then check if everything is working on your instance and do deploy after.');
        }
    }
})->desc('Check if composer install is needed before making deployment');
