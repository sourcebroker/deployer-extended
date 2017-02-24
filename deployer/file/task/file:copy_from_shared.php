<?php

namespace Deployer;

/**
 * Copy files from shared directory into
 */
task('file:copy_from_shared', function () {
    if (!get('shared_files_to_copy', false)) {
        return;
    }

    $sharedPath = "{{deploy_path}}/shared";
    $filesToCopy = array_merge(get('shared_files_to_copy_default', []), get('shared_files_to_copy'));

    foreach ($filesToCopy as $file) {
        $dirname = dirname($file);

        // Remove from source.
        run("if [ -f $(echo {{release_path}}/$file) ]; then rm -rf {{release_path}}/$file; fi");

        // Ensure dir is available in release
        run("if [ ! -d $(echo {{release_path}}/$dirname) ]; then mkdir -p {{release_path}}/$dirname;fi");

        // Create dir of shared file
        run("mkdir -p $sharedPath/" . $dirname);

        // Touch shared
        run("touch $sharedPath/$file");

        // Symlink shared dir to release dir
        run("cp -r $sharedPath/$file {{release_path}}/$file");
    }
})->desc('Copying shared files')->addAfter('deploy:shared');
