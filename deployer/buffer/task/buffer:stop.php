<?php

namespace Deployer;

task('buffer:stop', function () {
    if (empty(get('deploy_path'))) {
        throw new \RuntimeException('The "deploy_path" var is empty.');
    }
    $releasesList = get('releases_list');
    // Remove lock files also from previous release because it can be still read by apache/nginx after switching.
    // get('releases_list') is cached by deployer on first call in other task so it does not have the latest release
    // this is why $releasesList[0] have last release and not current.
    $overwriteReleases = ['current'];
    if (isset($releasesList[0])) {
        $overwriteReleases[] = 'releases/' . $releasesList[0];
    }
    foreach ($overwriteReleases as $overwriteRelease) {
        $overwriteReleasePath = get('deploy_path') . '/' . $overwriteRelease;
        foreach (get('buffer_config') as $key => $buffer) {
            if (empty($buffer['entrypoint_filename'])) {
                throw new \RuntimeException('entrypoint_filename not set for buffer_data [Error: 1498988796776]');
            }
            $entrypointFilename = $buffer['entrypoint_filename'];
            if (empty($buffer['locker_filename'])) {
                $lockerFilename = 'buffer.lock';
            } else {
                $lockerFilename = $buffer['locker_filename'];
            }
            $entrypointDirectory = dirname($entrypointFilename) === '.' ? '' : dirname($entrypointFilename) . '/';
            run('cd ' . $overwriteReleasePath . ' && rm -f ' . $entrypointDirectory . $lockerFilename);
        }
    }
})->desc('Stop buffering reqests to application entrypoints.');
