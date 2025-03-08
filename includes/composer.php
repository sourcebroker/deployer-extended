<?php

namespace Deployer;

set('bin/composer', function () {
    // Install specific composer version. Use tags. Valid tags are here https://github.com/composer/composer/tags
    $composerVersionToInstall = get('composer_version');

    // Install the latest version from channel. Set this variable to '1' or '2' (or 'stable', 'snapshot', 'preview'). Read more on composer docs.
    $composerChannelToInstall = get('composer_channel', 'stable');

    $composerBin = null;
    if (commandExist('composer')) {
        $composerBin = '{{bin/php}} ' . which('composer');
    }

    if (test('[ -f {{deploy_path}}/.dep/composer.phar ]')) {
        $composerBin = '{{bin/php}} {{deploy_path}}/.dep/composer.phar';
    }

    if ($composerBin !== null) {
        if (get('composer_channel_autoupdate', true) !== true) {
            return $composerBin;
        }
        // "composer --version" can return:
        // - "Composer version 1.10.17 2020-10-30 22:31:58" - for stable channel
        // - "Composer version 2.0.0-alpha3 2020-10-30 22:31:58" - alpha/beta/RC for preview channel
        // - "Composer version 2.0-dev (2.0-dev+378a5b72b9f81e8e919e41ecd3add6893d14b90e) 2020-12-04 09:50:19" - for snapshot channel
        $currentComposerVersionRaw = run($composerBin . ' --version');
        if (preg_match('/(\\d+\\.\\d+)(\\.\\d+)?-?(RC|alpha|beta|dev)?\d*/', $currentComposerVersionRaw,
            $composerVersionMatches)) {
            $currentComposerVersion = $composerVersionMatches[0] ?? null;
            if ($currentComposerVersion !== null) {
                // if we have exact version of composer to install (composer_version) and currently installed version match it then return
                if ($composerVersionToInstall && $currentComposerVersion === (string)$composerVersionToInstall) {
                    return $composerBin;
                }
                if ($composerChannelToInstall && !$composerVersionToInstall) {
                    // for snapshot channel the version is the git hash in "Composer version 2.0-dev (2.0-dev+378a5b72b9f81e8e919e41ecd3add6893d14b90e) 2020-12-04 09:50:19"
                    if (preg_match('/\\+(.*)\\)/', $currentComposerVersionRaw, $snapshotVersion)) {
                        $currentComposerVersion = $snapshotVersion[1] ?? null;
                    }
                    // Compare the latest version of composer channel with and currently installed version. If match then return.
                    $composerChannelData = json_decode(file_get_contents('https://getcomposer.org/versions'), true, 512,
                        JSON_THROW_ON_ERROR);
                    $latestComposerVersionFromChannel = $composerChannelData[$composerChannelToInstall][0]['version'] ?? null;
                    if ($latestComposerVersionFromChannel && $latestComposerVersionFromChannel === $currentComposerVersion) {
                        return $composerBin;
                    }
                }
            }
        }
    }

    $installCommand = "cd {{release_path}} && curl -sS https://getcomposer.org/installer | {{bin/php}}";
    if ($composerVersionToInstall) {
        $installCommand .= " -- --version=" . $composerVersionToInstall;
    } elseif ($composerChannelToInstall) {
        $installCommand .= " -- --" . $composerChannelToInstall;
    }

    run($installCommand);
    run('mv {{release_path}}/composer.phar {{deploy_path}}/.dep/composer.phar');
    return '{{bin/php}} {{deploy_path}}/.dep/composer.phar';
});
