<?php

namespace Deployer;

/**
 * Copy content from directories from previous release to the current one
 * Have to be called before "deploy:symlink"
 */
task('file:copy_from_previous', function () {
    if (!get('previous_release_dirs_to_copy', false)) {
        return;
    }

    $pathsToCopy = array_merge(get('previous_release_dirs_to_copy_default', []), get('previous_release_dirs_to_copy'));

    // check if {{deploy_path}}/current link exists. if not, it means that this is the initial deploy and we
    // can't copy data from previous release
    $currentPathExists = !!run("if [ -h $(echo {{deploy_path}}/current) ]; then echo 1; else echo 0; fi")->toString();

    if (!$currentPathExists) {
        writeln('<info>Symlink "current" does not exists, so it looks that this is the initial deploy. That means it is not possible to copy files from previous release and you will need to add them manaully after first deploy.</info>');
        writeln('<comment>Here is the list of the file and directories which you should copy to "current" after deploy:</comment>');
        foreach ($pathsToCopy as $path) {
            writeln('<comment>' . $path . '</comment>');
        }
        return;
    }

    // check if {{deploy_path}}/release link exists. if not, it means that task was executed after "deploy:symlink" task, so we can't proceed,
    // because we don't know the path to the previous release
    $releasePathExists = !!run("if [ -h $(echo {{deploy_path}}/release) ]; then echo 1; else echo 0; fi")->toString();

    if (!$releasePathExists) {
        throw new \RuntimeException('Task "deploy:copy_from_previous_release" can not be called after "deploy:symlink" [Error code: 1826354895]');
    }

    // make sure that all paths are without slash at the end
    $pathsToCopy = array_map('rtrim', $pathsToCopy, ['/']);

    foreach ($pathsToCopy as $path) {
        // check if copy source path exist and if it is directory
        $copySourcePathExists = !!run("if [ -d $(echo {{deploy_path}}/current/" . $path . ") ]; then echo 1; else echo 0; fi")->toString();

        if (!$copySourcePathExists) {
            continue;
        }

        // get all items inside specified path in old release
        $copySources = explode(PHP_EOL, run('ls {{deploy_path}}/current/' . $path)->toString());

        if (!count($copySources)) {
            continue;
        }

        // Ensure dir is available in release
        run("if [ ! -d $(echo {{release_path}}/$path) ]; then mkdir -p {{release_path}}/$path;fi");

        // @todo  probably it will be better to change it to rsync with "--ignore-existing" option as it is done in "deploy:wp:core" task, but need to think about that
        foreach ($copySources as $copySource) {
            // copy files and directories from previous release if they don't exists in the new one
            run("if [ ! -e $(echo {{release_path}}/$path/$copySource) ]; then cp -r $(echo {{deploy_path}}/current/$path/$copySource) $(echo {{release_path}}/$path/$copySource); fi");
        }
    }
})->desc('Copying directories from previous release')->addAfter('deploy:shared');
