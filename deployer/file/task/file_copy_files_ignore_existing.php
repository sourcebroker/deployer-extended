<?php

namespace Deployer;

task('file:copy_files_ignore_existing', function () {
    if (has('previous_release')) {
        foreach (get('copy_files_ignore_existing') as $dir) {
            if (test("[ -d {{previous_release}}/$dir ] ")) {
                run("mkdir -p {{release_path}}/$dir");
                run("rsync -av --ignore-existing {{previous_release}}/$dir/ {{release_path}}/$dir");
            }
        }
    }
})
    ->desc('Copy files from previous release except for those files which already exists')->hidden();
