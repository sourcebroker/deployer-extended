<?php

namespace Deployer;

use Deployer\Type\Csv;

// Read more on https://github.com/sourcebroker/deployer-extended#deploy-extend-log
task('deploy:extend_log', function () {
    $branchToBeDeployed = get('branch');
    $type = 'branch';
    $typeValue = $branchToBeDeployed;
    $hash = '';
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
            $hash = $revision;
        }
    }
    cd('{{deploy_path}}');
    $csv = run('tail -n 1 .dep/releases');
    $metainfo = Csv::parse($csv)[0];
    $metainfo[2] = $typeValue;
    try {
        $userName = runLocally('git config user.name');
    } catch (\Throwable $exception) {
        $userName = 'unknown';
    }
    $metainfo[3] = $userName;
    if ($type !== 'revision') {
        $hash = run(sprintf('%s ls-remote %s ' . $typeValue, get('bin/git'), get('repository')));
        $hash = explode("\t", $hash)[0];
    }
    $metainfo[4] = $hash;
    $metainfo[5] = $type;

    // create a well formatted csv line
    $handle = fopen('php://memory', 'r+');
    if (fputcsv($handle, $metainfo) === false) {
        throw new \Exception('Error formatting csv line.');
    }
    rewind($handle);
    $csvLine = trim(stream_get_contents($handle));

    run("echo " . escapeshellarg($csvLine) . " >> .dep/releases.extended");
})->desc('Add custom information to the log file');
