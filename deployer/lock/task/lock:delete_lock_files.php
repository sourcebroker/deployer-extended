<?php

namespace Deployer;

task('lock:delete_lock_files', function (){
    # remove files from new release
    run('cd {{release_path}} && rm -f {{web_path}}deployment.lock');
    run('cd {{release_path}} && rm -f {{web_path}}{{deploy_lock_filename}}');

    # remove files from previous release
    $releasesList = get('releases_list');
    if (isset($releasesList[1])) {
        run('cd {{deploy_path}} && rm -f releases/' . $releasesList[1] . '/{{web_path}}deployment.lock');
        run('cd {{deploy_path}} && rm -f releases/' . $releasesList[1] . '/{{web_path}}{{deploy_lock_filename}}');
    }
})->desc('Delete lock files from DocumentRoot');