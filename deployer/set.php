<?php

namespace Deployer;

// Common random that can be used between tasks. Must be in form that can be used directly in filename!
set('random', md5(time() . rand()));

// Path to public when not in root of project. Must be like "pub/" so without starting slash and with ending slash.
set('web_path', '');

// Return path to php on current instance
set('local/bin/php', function () {
    return locateLocalBinaryPath('php');
});

// Return path to curl on current instance
set('local/bin/curl', function () {
    return locateLocalBinaryPath('curl');
});

// Return path to wget on current instance
set('local/bin/wget', function () {
    return locateLocalBinaryPath('wget');
});

// Install specific composer version. Use tags. Valid tags are here https://github.com/composer/composer/tags
set('composer_version', null);

// Install latest version from channel. Set this variable to '1' or '2' (or 'stable', 'snapshot', 'preview'). Read more on composer docs.
set('composer_channel', 'stable');

// If set then on each deploy the composer is checked for latest version
set('composer_channel_autoupdate', true);

// Return path to composer on remote instance
set('bin/composer', function () {
    $composerVersionToInstall = get('composer_version');
    $composerChannelToInstall = get('composer_channel');

    $composerBin = null;
    if (commandExist('composer')) {
        $composerBin = '{{bin/php}} ' . locateBinaryPath('composer');
    }

    if (test('[ -f {{deploy_path}}/.dep/composer.phar ]')) {
        $composerBin = '{{bin/php}} {{deploy_path}}/.dep/composer.phar';
    }

    if ($composerBin) {
        if (get('composer_channel_autoupdate') !== true) {
            return $composerBin;
        }
        // "composer --version" can return:
        // - "Composer version 1.10.17 2020-10-30 22:31:58" - for stable channel
        // - "Composer version 2.0.0-alpha3 2020-10-30 22:31:58" - alpha/beta/RC for preview channel
        // - "Composer version 2.0-dev (2.0-dev+378a5b72b9f81e8e919e41ecd3add6893d14b90e) 2020-12-04 09:50:19" - for snapshot channel
        $currentComposerVersionRaw = run($composerBin . ' --version');
        if (preg_match('/(\\d+\\.\\d+)(\\.\\d+)?-?(RC|alpha|beta|dev)?\d*/', $currentComposerVersionRaw, $composerVersionMatches)) {
            $currentComposerVersion = $composerVersionMatches[0] ?? null;
            if ($currentComposerVersion) {
                // if we have exact version of composer to install (composer_version) and currently installed version match it then return
                if ($composerVersionToInstall && $currentComposerVersion === (string)$composerVersionToInstall) {
                    return $composerBin;
                }
                if ($composerChannelToInstall && !$composerVersionToInstall) {
                    // for snapshot channel the version is the git hash in "Composer version 2.0-dev (2.0-dev+378a5b72b9f81e8e919e41ecd3add6893d14b90e) 2020-12-04 09:50:19"
                    if (preg_match('/\\+(.*)\\)/', $currentComposerVersionRaw, $snapshotVersion)) {
                        $currentComposerVersion = $snapshotVersion[1] ?? null;
                    }
                    // Compare latest version of composer channel with and currently installed version. If match then return.
                    $composerChannelData = json_decode(file_get_contents('https://getcomposer.org/versions'), true);
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

set('local/bin/composer', function () {
    return locateLocalBinaryPath('composer');
});

// We assume deploy.php is in project root.
set('current_dir', function () {
    $current = getcwd();
    if (is_dir($current) && file_exists($current . '/deploy.php')) {
        return $current;
    } else {
        throw new \Exception('Can not set "current_dir" var. Are you in folder with deploy.php file?');
    }
});

set('branch', function () {
    $branch = null;
    if (get('branch_detect_to_deploy', true)) {
        try {
            $branch = runLocally('git rev-parse --abbrev-ref HEAD');
        } catch (\Throwable $exception) {
            // We do not care why it fails as branch can be set other way.
        }
    }
    if ($branch === 'HEAD') {
        $branch = null; // Travis-CI fix
    }
    if (input()->hasOption('branch') && !empty(input()->getOption('branch'))) {
        $branch = input()->getOption('branch');
    }
    return $branch;
});

function locateLocalBinaryPath($name)
{
    $nameEscaped = escapeshellarg($name);
    // Try `command`, should cover all Bourne-like shells
    // Try `which`, should cover most other cases
    // Fallback to `type` command, if the rest fails
    $path = runLocally("command -v $nameEscaped || which $nameEscaped || type -p $nameEscaped");
    if ($path) {
        // Deal with issue when `type -p` outputs something like `type -ap` in some implementations
        return trim(str_replace("$name is", "", $path));
    }
    throw new \RuntimeException("Can't locate [$nameEscaped] on instance '" . get('default_stage') . "' - neither of [command|which|type] commands are available");
}
