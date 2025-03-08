<?php

namespace Deployer;

use Deployer\Exception\GracefulShutdownException;

// Read more on https://github.com/sourcebroker/deployer-extended#deploy-check-composer-validate
task('deploy:check_composer_validate', function () {
    if (file_exists(get('project_root') . '/composer.lock') && file_exists(get('project_root') . '/composer.json')) {
        $output = runLocally(
            get('local/bin/composer')
            . ' validate ' .
            get('check_composer_validate_options', ' --no-check-all --no-check-publish')
        );
        if (strpos($output, 'The composer.lock file is not up to date with current state of composer.json') !== false) {
            throw new GracefulShutdownException(
                'The composer.lock file is not in sync with composer.json. Run composer update and commit the changes before deploying.'
            );
        }
    }
})->desc('Validate composer')->hidden();
