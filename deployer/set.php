<?php

namespace Deployer;

use Deployer\Exception\GracefulShutdownException;

// Path to public when not in root of project. Must be like "pub/" so without starting slash and with ending slash.
set('web_path', '');

set('local/bin/php', function () {
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
