<?php

namespace Deployer;

use Deployer\Type\Csv;

// Read more on https://github.com/sourcebroker/deployer-extended#deploy-extend-log
task('deploy:extend_log', function () {
    cd('{{deploy_path}}');
    $csv = run('tail -n 1 .dep/releases');
    $metainfo = Csv::parse($csv)[0];
    try {
        $branch = runLocally('git rev-parse --abbrev-ref HEAD');
    } catch (\Throwable $exception) {
        $branch = '';
    }
    $branch = get('branch') ?: $branch;
    // If option `branch` is set.
    if (input()->hasOption('branch')) {
        $inputBranch = input()->getOption('branch');
        if (!empty($inputBranch)) {
            $branch = $inputBranch;
        }
    }
    // TODO: consider also following options later: --tag, --revision.
    $metainfo[2] = $branch;
    $metainfo[3] = runLocally('git config user.name');
    $repository = get('repository');
    $branchHead = run(sprintf('%s ls-remote %s refs/heads/' . $branch, get('bin/git'), $repository));
    $branchHead = explode("\t", $branchHead)[0];
    $metainfo[4] = $branchHead;

    // create a well formatted csv line
    $handle = fopen('php://memory', 'r+');
    if (fputcsv($handle, $metainfo) === false) {
        throw new \Exception('Error formatting csv line.');
    }
    rewind($handle);
    $csvLine = trim(stream_get_contents($handle));

    run("echo " . escapeshellarg($csvLine) . " >> .dep/releases.extended");
})->desc('Add custom information to the log file');
