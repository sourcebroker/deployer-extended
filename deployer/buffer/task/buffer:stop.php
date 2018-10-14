<?php

namespace Deployer;

task('buffer:stop', function () {
    if (empty(get('deploy_path'))) {
        throw new \Exception('The "deploy_path" var is empty but its used for file operations so its dangerous state.');
    }
    $overwriteReleases = ['current'];
    $releasesList = get('releases_list');
    if (isset($releasesList[0])) {
        $oldRelease = '/releases/' . $releasesList[0];
        $overwriteReleases[] = $oldRelease;
        // Add flag '.flag.oldrelease' to redirect if php choosed old release to serve request
        $oldReleaseFlagFilename = empty($inject['oldrelease_flag__filename']) ?
            '.flag.oldrelease' : $inject['oldrelease_flag_filename'];
        if (test('[ -e ' . get('deploy_path') . $oldRelease . ' ]')) {
            run('touch ' . get('deploy_path') . $oldRelease . '/' . $oldReleaseFlagFilename);
        }
    }
    // Remove .flag.requestbuffer lock files also from previous release because it can be still read by httpd after
    // switching. get('releases_list') is cached by deployer on first call in other task so it does not have the
    // latest release this is why $releasesList[0] have last release and not current.
    foreach ($overwriteReleases as $overwriteRelease) {
        $overwriteReleasePath = get('deploy_path') . '/' . $overwriteRelease;
        foreach (get('buffer_config') as $key => $inject) {
            if (empty($inject['entrypoint_filename'])) {
                throw new \Exception('entrypoint_filename not set for buffer_data');
            }
            $entrypointFilename = $inject['entrypoint_filename'];
            $activateBufferFlagFilename = empty($inject['requestbuffer_flag_filename']) ?
                '.flag.requestbuffer' : $inject['requestbuffer_flag_filename'];
            $entrypointDirectory = dirname($entrypointFilename) === '.' ? '' : dirname($entrypointFilename) . '/';
            run('cd ' . $overwriteReleasePath . ' && rm -f ' . $entrypointDirectory . $activateBufferFlagFilename);
        }
    }
})->desc('Stop buffering requests to application entrypoints.');
