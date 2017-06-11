<?php

namespace Deployer;

use SourceBroker\DeployerExtended\Utility\FileUtility;

task('file:rm2steps:start', function () {
    $removeRecursiveAtomicItems = get('file_remove2steps_items');
    $random = get('random');
    // Set active_path so the task can be used before or after "symlink" task or standalone.
    $activePath = get('deploy_path') . '/' . (test('[ -L {{deploy_path}}/release ]') ? 'release' : 'current');
    foreach ($removeRecursiveAtomicItems as $removeRecursiveAtomicItem) {
        $removeRecursiveAtomicItem = escapeshellarg(FileUtility::normalizeFolder($removeRecursiveAtomicItem));
        if (test("[ -e '$activePath/$removeRecursiveAtomicItem$random' ]")) {
            run("cd '$activePath' && mv '$removeRecursiveAtomicItem' '$removeRecursiveAtomicItem$random'");
        }
    }
})->desc('Remove files and directories in two steps. First step: rename. Second step: remove.');
