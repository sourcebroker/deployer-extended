<?php

namespace Deployer;

set('bin/php', function () {
    $rawPhpVersion = null;
    if (currentHost()->hasOwn('php_version')) {
        $rawPhpVersion = get('php_version');
    }

    if (empty($rawPhpVersion) && file_exists('composer.json')) {
        try {
            $composerJson = json_decode(file_get_contents('composer.json'), true);
            if (is_array($composerJson)) {
                if (isset($composerJson['config']['platform']['php'])) {
                    $rawPhpVersion = $composerJson['config']['platform']['php'];
                }
                if (empty($rawPhpVersion) && isset($composerJson['require']['php'])) {
                    $rawPhpVersion = $composerJson['require']['php'];
                }
            }
        } catch (\Throwable $e) {
            // Silently handle any errors reading composer.json
        }
    }

    $phpVersionMajorMinor = null;
    if (!empty($rawPhpVersion) && preg_match('/[^0-9]*(\d+)(?:\.(\d+))?(?:\.\d+)?/', $rawPhpVersion, $matches)) {
        if (isset($matches[1]) && is_numeric($matches[1])) {
            $phpVersionMajorMinor = $matches[1] . (isset($matches[2]) && is_numeric($matches[2]) ? '.' . $matches[2] : '.0');
        }
    }

    if ($phpVersionMajorMinor !== null) {
        try {
            return which('php' . $phpVersionMajorMinor);
        } catch (\Throwable $e) {
            try {
                return which('php' . str_replace('.', '', $phpVersionMajorMinor));
            } catch (\Throwable $e) {
                output()->writeln(
                    '<comment>PHP binary with version ' . $phpVersionMajorMinor . ' not found, falling back to search for "php"</comment>',
                    \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERBOSE
                );
            }
        }
    }

    try {
        $phpBinaryPath = which('php');
        $actualVersionMajorMinor = trim(run($phpBinaryPath . ' -r "echo PHP_MAJOR_VERSION.\".\" . PHP_MINOR_VERSION;"'));

        if ($phpVersionMajorMinor !== null && $actualVersionMajorMinor !== $phpVersionMajorMinor) {
            $phpVersionStrict = get('php_version_strict', false);
            if ($phpVersionStrict) {
                throw new \RuntimeException(sprintf(
                    'PHP version mismatch: required %s, found %s',
                    $phpVersionMajorMinor,
                    $actualVersionMajorMinor
                ), 1715438658);
            }
            output()->writeln(
                '<warning>Found PHP binary version (' . $phpBinaryPath . ' ' . $actualVersionMajorMinor . ') does not match required version (' . $phpVersionMajorMinor . ')</warning>',
                \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL
            );
        }
        return $phpBinaryPath;
    } catch (\Throwable $e) {
        if ($e->getCode() === 1715438658) {
            throw $e;
        }

        output()->writeln(
            '<comment>"php" command not found in PATH, using just "php" directly</comment>',
            \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERBOSE
        );
        $phpBinaryPath = 'php';
        if ($phpVersionMajorMinor !== null) {
            $actualVersionMajorMinor = trim(run($phpBinaryPath . ' -r "echo PHP_MAJOR_VERSION.\".\" . PHP_MINOR_VERSION;"'));

            if ($actualVersionMajorMinor !== $phpVersionMajorMinor) {
                $phpVersionStrict = get('php_version_strict', false);
                if ($phpVersionStrict) {
                    throw new \RuntimeException(sprintf(
                        'PHP version mismatch: required %s, found %s',
                        $phpVersionMajorMinor,
                        $actualVersionMajorMinor
                    ), 1715438658);
                }
                output()->writeln(
                    '<warning>PHP version found when running just "php" directly ( ' . $actualVersionMajorMinor . ') does not match required version (' . $phpVersionMajorMinor . ')</warning>',
                    \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL
                );
            }
        }
        return $phpBinaryPath;
    }
});
