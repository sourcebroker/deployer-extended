<?php

namespace Deployer;

task('deploy:composer_install_check', function () {
    $composerBin = runLocally('which composer')->toString();
    if (runLocally("if hash $composerBin 2>/dev/null; then echo 'true'; fi")->toBool()) {
        $output = runLocally($composerBin . ' --ignore-platform-reqs install --dry-run 2>&1');
        if (strpos($output, 'Nothing to install or update') === false) {
            throw new \RuntimeException('A composer.lock changes has been detected but you did not run "composer install". Please run composer install, then check if everything is working on your instance and do deploy after.');
        }
    }
})->desc('Check if composer install is needed.');
