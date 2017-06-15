<?php

namespace Deployer;

task('buffer:start', function () {
    $overwriteReleases = ['release', 'current'];
    // Overwrite should be done in /release and /current
    foreach ($overwriteReleases as $overwriteRelease) {
        $overwriteReleasePath = get('deploy_path') . '/' . $overwriteRelease;
        if (test("[ -L $overwriteReleasePath ]")) {
            $entrypointInjectStartComment = "\n\n// deployer extended buffering request code START\n";
            $entrypointInjectEndComment = "\n// deployer extended buffering request code END\n\n";

            foreach (get('buffer_config') as $key => $buffer) {
                if (!isset($buffer['entrypoint_filename'])) {
                    throw new \RuntimeException('entrypoint_filename not set for buffer_data');
                }
                $entrypointFilename = $buffer['entrypoint_filename'];
                if (isset($buffer['entrypoint_needle'])) {
                    $entrypointNeedle = $buffer['entrypoint_needle'];
                } else {
                    $entrypointNeedle = '<?php';
                }
                if (isset($buffer['locker_filename'])) {
                    $lockerFilename = $buffer['locker_filename'];
                } else {
                    $lockerFilename = 'buffer.lock';
                }
                if (isset($buffer['entrypoint_inject'])) {
                    $entrypointInject = $buffer['entrypoint_inject'];
                } else {
                    $entrypointInject =
                        "isset(\$_SERVER['HTTP_X_DEPLOYER_DEPLOYMENT']) && \$_SERVER['HTTP_X_DEPLOYER_DEPLOYMENT'] == '{{random}}' ? \$deployerExtendedEnableBufferLock = false: \$deployerExtendedEnableBufferLock = true;\n"
                        . "isset(\$_ENV['DEPLOYER_DEPLOYMENT']) && \$_ENV['DEPLOYER_DEPLOYMENT'] == '{{random}}' ? \$deployerExtendedEnableBufferLock = false: \$deployerExtendedEnableBufferLock = true;\n"
                        . "while (file_exists(__DIR__ . '/$lockerFilename') && \$deployerExtendedEnableBufferLock) {\n    usleep(200000);\n    clearstatcache(true, __DIR__ . '/$lockerFilename');\n}";
                }
                $entrypointFileContent = trim(run('cd {{release_path}} && [ -f {{web_path}}' . $entrypointFilename . ' ] && cat {{web_path}}' . $entrypointFilename . ' || echo ""')->toString());
                if (strpos($entrypointFileContent, $entrypointInjectStartComment) === false) {
                    if (!empty($entrypointFileContent)) {
                        $pos = strpos($entrypointFileContent, $entrypointNeedle);
                        if ($pos !== false) {
                            $content = substr_replace($entrypointFileContent,
                                $entrypointNeedle .
                                $entrypointInjectStartComment . $entrypointInject . $entrypointInjectEndComment, $pos,
                                strlen($entrypointNeedle));

                            run('cd ' . $overwriteReleasePath . '  && echo ' . escapeshellarg($content) . ' > {{web_path}}' . $entrypointFilename . '{{random}}');
                            run('mv -T ' . $overwriteReleasePath . '/{{web_path}}' . $entrypointFilename . '{{random}} ' . $overwriteReleasePath . '/{{web_path}}' . $entrypointFilename);
                        } else {
                            throw new \RuntimeException('Can not find needle to inject with inclusion');
                        }
                    } else {
                        throw new \RuntimeException('Can not find file to overwrite or the file is empty. File that was read is: ' . $overwriteReleasePath . '/' . get('web_path') . $entrypointFilename);
                    }
                }
                run('cd ' . $overwriteReleasePath . ' && touch ' . (dirname($entrypointFilename) ? dirname($entrypointFilename) . '/' : '') . $lockerFilename);
            }
        }
    }
})->desc('Start buffering reqests to application entrypoints.');
