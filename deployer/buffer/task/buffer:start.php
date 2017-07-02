<?php

namespace Deployer;

task('buffer:start', function () {
    if (empty(get('deploy_path'))) {
        throw new \RuntimeException('The "deploy_path" var is empty. [Error: 1498989780841]');
    }
    if (empty(get('random'))) {
        throw new \RuntimeException('The "random" var is empty. [Error: 1498989790330]');
    }
    if (preg_match('/^[a-zA-Z0-9]$/', get('random'))) {
        throw new \RuntimeException('The "random" var should be only /^[a-zA-Z0-9]$/ because its used in filenames. 
        [Error: 1498989790330]');
    }
    $overwriteReleases = ['release', 'current'];
    foreach ($overwriteReleases as $overwriteRelease) {
        $overwriteReleasePath = get('deploy_path') . '/' . $overwriteRelease . '/';
        if (test("[ -e $overwriteReleasePath ]")) {
            $tempPath = $overwriteReleasePath . get('random');
            run('[ -e ' . $tempPath . ' ] || mkdir -p ' . $tempPath);
            $entrypointInjectStartComment = "\n\n// deployer extended buffering request code START\n";
            $entrypointInjectEndComment = "\n// deployer extended buffering request code END\n\n";
            foreach ((array)get('buffer_config') as $key => $buffer) {
                if (empty($buffer['entrypoint_filename'])) {
                    throw new \RuntimeException('entrypoint_filename not set for buffer_data [Error: 1498990095089]');
                }
                $entrypointFilename = $buffer['entrypoint_filename'];
                if (empty($buffer['entrypoint_needle'])) {
                    $entrypointNeedle = '<?php';
                } else {
                    $entrypointNeedle = $buffer['entrypoint_needle'];
                }
                if (empty($buffer['locker_filename'])) {
                    $lockerFilename = 'buffer.lock';
                } else {
                    $lockerFilename = $buffer['locker_filename'];
                }
                if (empty($buffer['entrypoint_inject'])) {
                    $entrypointInject =
                        "isset(\$_SERVER['HTTP_X_DEPLOYER_DEPLOYMENT']) && \$_SERVER['HTTP_X_DEPLOYER_DEPLOYMENT'] == '{{random}}' ? \$deployerExtendedEnableBufferLock = false: \$deployerExtendedEnableBufferLock = true;\n"
                        . "isset(\$_ENV['DEPLOYER_DEPLOYMENT']) && \$_ENV['DEPLOYER_DEPLOYMENT'] == '{{random}}' ? \$deployerExtendedEnableBufferLock = false: \$deployerExtendedEnableBufferLock = true;\n"
                        . "while (file_exists(__DIR__ . '/$lockerFilename') && \$deployerExtendedEnableBufferLock) {\n    usleep(200000);\n    clearstatcache(true, __DIR__ . '/$lockerFilename');\n}";
                } else {
                    $entrypointInject = $buffer['entrypoint_inject'];
                }
                $entrypointFileContent = trim(run('cd {{release_path}} && [ -f ' . $entrypointFilename . ' ] && cat ' . $entrypointFilename . ' || echo ""')->toString());
                if (strpos($entrypointFileContent, $entrypointInjectStartComment) === false) {
                    if (!empty($entrypointFileContent)) {
                        $pos = strpos($entrypointFileContent, $entrypointNeedle);
                        if ($pos !== false) {
                            $content = substr_replace($entrypointFileContent,
                                $entrypointNeedle .
                                $entrypointInjectStartComment . $entrypointInject . $entrypointInjectEndComment, $pos,
                                strlen($entrypointNeedle));
                            $entrypointAbsolutePath = $overwriteReleasePath . $entrypointFilename;
                            run('cp -p ' . $entrypointAbsolutePath . ' ' . $tempPath . '/');
                            run('echo ' . escapeshellarg($content) . ' > ' . $tempPath . '/' . basename($entrypointAbsolutePath));
                            run('mv -T ' . $tempPath . '/' . basename($entrypointAbsolutePath) . ' ' . $entrypointAbsolutePath);
                        } else {
                            throw new \RuntimeException('Can not find needle to inject with inclusion. [Error: 1498990135243]');
                        }
                    } else {
                        throw new \RuntimeException('Can not find file to overwrite or the file is empty. File that was read is: ' . $overwriteReleasePath . '/' . $entrypointFilename . '. [Error: 1498990146197]');
                    }
                }
                run('cd ' . $overwriteReleasePath . ' && touch ' . (dirname($entrypointFilename) ? dirname($entrypointFilename) . '/' : '') . $lockerFilename);
            }
            run('rmdir ' . $tempPath);
        }
    }
})->desc('Start buffering reqests to application entrypoints.');
