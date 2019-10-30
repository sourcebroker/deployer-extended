<?php

namespace Deployer;

// Read more on https://github.com/sourcebroker/deployer-extended#file-rm2steps-2
task('file:rm2steps:2', function () {
    $removeRecursiveAtomicItems = get('file_remove2steps_items');
    $random = get('random');
    $activePath = get('deploy_path') . '/' . (test('[ -L {{deploy_path}}/release ]') ? 'release' : 'current');
    foreach ($removeRecursiveAtomicItems as $removeRecursiveAtomicItem) {
        $removeRecursiveAtomicItem = rtrim(trim($removeRecursiveAtomicItem), '/');
        if (strlen($removeRecursiveAtomicItem)) {
            $itemToRemove = escapeshellarg("$activePath/$removeRecursiveAtomicItem$random");
            run("if [ -e $itemToRemove ]; then rm -rf $itemToRemove; fi");
        }
    }
})->desc('Remove files and directories in two steps - first step: rename - second step: remove');
