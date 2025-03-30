<?php

namespace Deployer;

set('bin/php', function () {
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
            return which('php' . $phpVersionMajorMinor);
        } catch (\Throwable $e) {
            return which('php' . str_replace('.', '', $phpVersionMajorMinor));
        }
    }

    return which('php');
});