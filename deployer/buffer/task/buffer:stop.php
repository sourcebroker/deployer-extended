<?php

namespace Deployer;

task('buffer:stop', function () {
    $releasesList = get('releases_list');
    // Remove lock files also from previous release because it can be still read by apache/nginx after switching.
    // get('releases_list') is cached by deployer on first call in other task so it does not have the latest release
    // this is why $releasesList[0] have last release and not current.
    $overwriteReleases = ['current'];
    if (!empty($releasesList[0])) {
        $overwriteReleases[] = $releasesList[0];
    }
    foreach ($overwriteReleases as $overwriteRelease) {
        $overwriteReleasePath = get('deploy_path') . '/' . $overwriteRelease;
        foreach (get('buffer_config') as $key => $buffer) {
            if (!isset($buffer['entrypoint_filename'])) {
                throw new \RuntimeException('entrypoint_filename not set for buffer_data');
            }
            $entrypointFilename = $buffer['entrypoint_filename'];
            if (isset($buffer['locker_filename'])) {
                $lockerFilename = $buffer['locker_filename'];
            } else {
                $lockerFilename = 'buffer.lock';
            }
            if (dirname($entrypointFilename) != '.') {
                $entrypointDirectory = dirname($entrypointFilename) . '/';
            } else {
                $entrypointDirectory = '';
            }
            run('cd ' . $overwriteReleasePath . ' && rm -f {{web_path}}' . $entrypointDirectory . $lockerFilename);
        }
    }
})->desc('Stop buffering reqests to application entrypoints.');
