<?php

namespace Deployer;

task('buffer:stop', function () {
    if (empty(get('deploy_path'))) {
        throw new \Exception('The "deploy_path" var is empty but its used for file operations so its dangerous state.');
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
                throw new \Exception('entrypoint_filename not set for buffer_data');
            }
            $entrypointFilename = $buffer['entrypoint_filename'];
            $activateBufferFlagFilename = empty($buffer['activate_buffer_flag_filename']) ?
                '.activatebuffer.flag' : $buffer['activate_buffer_flag_filename'];
            $entrypointDirectory = dirname($entrypointFilename) === '.' ? '' : dirname($entrypointFilename) . '/';
            run('cd ' . $overwriteReleasePath . ' && rm -f ' . $entrypointDirectory . $activateBufferFlagFilename);
        }
    }
})->desc('Stop buffering requests to application entrypoints.');
