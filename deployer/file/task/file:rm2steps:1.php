<?php

namespace Deployer;

task('file:rm2steps:1', function () {
    $removeRecursiveAtomicItems = get('file_remove2steps_items');
    $random = get('random');
    // Set active_path so the task can be used before or after "symlink" task or standalone.
    $activePath = get('deploy_path') . '/' . (test('[ -L {{deploy_path}}/release ]') ? 'release' : 'current');
    foreach ($removeRecursiveAtomicItems as $removeRecursiveAtomicItem) {
        $removeRecursiveAtomicItem = rtrim(trim($removeRecursiveAtomicItem), '/');
        if (strlen($removeRecursiveAtomicItem)) {
            $itemToRename = escapeshellarg("$activePath/$removeRecursiveAtomicItem");
            $itemToRenameNewName = escapeshellarg("$activePath/$removeRecursiveAtomicItem$random");
            run("if [ -e $itemToRename ]; then mv -f $itemToRename $itemToRenameNewName; fi");
        }
    }
})->desc('Remove files and directories in two steps. First step: rename. Second step: remove.');
