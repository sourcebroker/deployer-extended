<?php

namespace Deployer;

use Deployer\Type\Csv;

// Read more on https://github.com/sourcebroker/deployer-extended#deploy-extend-log
task('deploy:extend_log', function () {
    $type = 'branch';
    $typeValue = '';
    if (input()->hasOption('tag')) {
        $tag = input()->getOption('tag');
        if (!empty($tag)) {
            $type = 'tag';
            $typeValue = $tag;
        }
    }
    if (empty($tag) && input()->hasOption('revision')) {
        $revision = input()->getOption('revision');
        if (!empty($revision)) {
            $type = 'revision';
            $typeValue = $revision;
        }
    }
    cd('{{deploy_path}}');
    $csv = run('tail -n 1 .dep/releases');
    $metainfo = Csv::parse($csv)[0];
    $branchToBeDeployed = get('branch');
    $metainfo[2] = $branchToBeDeployed;
    try {
        $userName = runLocally('git config user.name');
    } catch (\Throwable $exception) {
        $userName = 'Unknown';
    }
    $metainfo[3] = $userName;
    $repository = get('repository');
    $branchHead = run(sprintf('%s ls-remote %s refs/heads/' . $branchToBeDeployed, get('bin/git'), $repository));
    $branchHead = explode("\t", $branchHead)[0];
    $metainfo[4] = $branchHead;
    $metainfo[5] = $type;
    $metainfo[6] = $typeValue;

    // create a well formatted csv line
    $handle = fopen('php://memory', 'r+');
    if (fputcsv($handle, $metainfo) === false) {
        throw new \Exception('Error formatting csv line.');
    }
    rewind($handle);
    $csvLine = trim(stream_get_contents($handle));

    run("echo " . escapeshellarg($csvLine) . " >> .dep/releases.extended");
})->desc('Add custom information to the log file');
