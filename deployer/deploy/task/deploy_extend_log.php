<?php

namespace Deployer;

use Deployer\Type\Csv;

// Read more on https://github.com/sourcebroker/deployer-extended#deploy-extend-log
task('deploy:extend_log', function () {
    cd('{{deploy_path}}');
    $csv = run('tail -n 1 .dep/releases');
    $metainfo = Csv::parse($csv)[0];
    $metainfo[2] = get('branch') ?: 'master';
    $metainfo[3] = runLocally('git config user.name')->toString();
    run("echo '" . implode(',', $metainfo) . "' >> .dep/branch");
})->desc('Add custom information to the log file.');
