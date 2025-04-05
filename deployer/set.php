<?php

namespace Deployer;

use Deployer\Exception\GracefulShutdownException;

set('local/bin/php', function () {
    if (currentHost()->hasOwn('php_version')) {
        $rawPhpVersion = get('php_version');
    }

    if (empty($rawPhpVersion) && file_exists('composer.json')) {
        $composerJson = json_decode(file_get_contents('composer.json'), true);

        if (isset($composerJson['config']['platform']['php'])) {
            $rawPhpVersion = $composerJson['config']['platform']['php'];
        }
        if (empty($rawPhpVersion) && isset($composerJson['require']['php'])) {
            $rawPhpVersion = $composerJson['require']['php'];
        }
    }

    $phpVersionMajorMinor = null;
    if (!empty($rawPhpVersion) && preg_match('/[^0-9]*(\d+)(?:\.(\d+))?(?:\.\d+)?/', $rawPhpVersion, $matches)) {
        if (isset($matches[1]) && is_numeric($matches[1])) {
            $phpVersionMajorMinor = $matches[1] . (isset($matches[2]) && is_numeric($matches[2]) ? '.' . $matches[2] : '.0');
        }
    }

    if ($phpVersionMajorMinor) {
        try {
            return locateLocalBinaryPath('php' . $phpVersionMajorMinor);
        } catch (\Throwable $e) {
            return locateLocalBinaryPath('php' . str_replace('.', '', $phpVersionMajorMinor));
        }
    }

    return locateLocalBinaryPath('php');
});

set('local/bin/curl', function () {
    return locateLocalBinaryPath('curl');
});

set('local/bin/wget', function () {
    return locateLocalBinaryPath('wget');
});

set('local/bin/composer', function () {
    return locateLocalBinaryPath('composer');
});

set('project_root', function () {
    $dir = __DIR__;
    while ((!is_file($dir . '/composer.json') && !is_file($dir . '/deploy.php')) || basename($dir) === 'deployer-extended') {
        if ($dir === \dirname($dir)) {
            break;
        }
        $dir = \dirname($dir);
    }
    return $dir;
});

function locateLocalBinaryPath($name): string
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
    throw new GracefulShutdownException("Can't locate [$nameEscaped] - neither of [command|which|type] commands are available");
}
