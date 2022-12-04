<?php

namespace Deployer;

// Read more on https://github.com/sourcebroker/deployer-extended#buffer-stop
use Deployer\Exception\GracefulShutdownException;

task('buffer:stop', function () {
    if (empty(get('deploy_path'))) {
        throw new GracefulShutdownException('The "deploy_path" var is empty but its used for file operations so its dangerous state.');
    }
    $overwriteReleases = ['current'];
    $releasesList = get('releases_list');
    // Add .flag.oldrelease for cases when php cache will decide to run code in old release path
    if (isset($releasesList[1])) {
        $overwriteReleases[] = 'releases/' . $releasesList[1];
        foreach (get('buffer_config') as $inject) {
            if (empty($inject['entrypoint_filename'])) {
                throw new GracefulShutdownException('entrypoint_filename not set for buffer_data');
            }
            $overwriteReleasePath = get('deploy_path') . '/releases/' . $releasesList[1];
            $oldReleaseFlagFilename = empty($inject['oldrelease_flag__filename']) ?
                '.flag.oldrelease' : $inject['oldrelease_flag_filename'];
            $entrypointDirectory = dirname($inject['entrypoint_filename']) === '.' ? '' : dirname($inject['entrypoint_filename']);
            if (test('[ -e ' . $overwriteReleasePath . '/' . $entrypointDirectory . ' ]')) {
                run('touch ' . $overwriteReleasePath . '/' . $entrypointDirectory . '/' . $oldReleaseFlagFilename);
            }
        }
    }
    // Remove .flag.requestbuffer also from previous release because it can be still read because php cache
    foreach ($overwriteReleases as $overwriteRelease) {
        $overwriteReleasePath = get('deploy_path') . '/' . $overwriteRelease;
        foreach (get('buffer_config') as $inject) {
            if (empty($inject['entrypoint_filename'])) {
                throw new GracefulShutdownException('entrypoint_filename not set for buffer_data');
            }
            $activateBufferFlagFilename = empty($inject['requestbuffer_flag_filename']) ?
                '.flag.requestbuffer' : $inject['requestbuffer_flag_filename'];
            $entrypointDirectory = dirname($inject['entrypoint_filename']) === '.' ? '' : dirname($inject['entrypoint_filename']) . '/';
            run('cd ' . $overwriteReleasePath . ' && rm -f ' . $entrypointDirectory . $activateBufferFlagFilename);
        }
    }
})->desc('Stop buffering requests to application entry points');
