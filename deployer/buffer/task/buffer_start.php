<?php

namespace Deployer;

// Read more on https://github.com/sourcebroker/deployer-extended#buffer-start
task('buffer:start', function () {
    if (empty(get('deploy_path'))) {
        throw new \Exception('The "deploy_path" var is empty but its used for file operations so its dangerous state.');
    }
    if (empty(get('random'))) {
        throw new \Exception('The "random" config var is empty. Its needed for file operation.');
    }
    if (preg_match('/^[a-zA-Z0-9]$/', get('random'))) {
        throw new \Exception('The "random" config var should be only /^[a-zA-Z0-9]$/ because its used in filenames.');
    }
    foreach (['release', 'current'] as $overwriteRelease) {
        $overwriteReleasePath = get('deploy_path') . '/' . $overwriteRelease . '/';
        if (test("[ -e $overwriteReleasePath ]")) {
            $tempPath = $overwriteReleasePath . get('random');
            run('[ -e ' . $tempPath . ' ] || mkdir -p ' . $tempPath);
            $entrypointInjectStartComment = "\n\n// deployer extended buffering request code START\n";
            $entrypointInjectEndComment = "\n// deployer extended buffering request code END\n\n";
            foreach ((array)get('buffer_config') as $key => $inject) {
                if (empty($inject['entrypoint_filename'])) {
                    throw new \Exception('entrypoint_filename not set for buffer_data');
                }
                // general inject settings
                $entrypointFilename = $inject['entrypoint_filename'];
                if (empty($inject['entrypoint_needle'])) {
                    $entrypointNeedle = '<?php';
                } else {
                    $entrypointNeedle = $inject['entrypoint_needle'];
                }

                // Buffering php requests
                if (empty($inject['requestbuffer_sleep'])) {
                    $requestBufferSleep = 200000; // 200ms
                } else {
                    $requestBufferSleep = intval($inject['requestbuffer_sleep']);
                }
                if (empty($inject['requestbuffer_flag_filename'])) {
                    $requestBufferFlagFilename = '.flag.requestbuffer';
                } else {
                    $requestBufferFlagFilename = $inject['requestbuffer_flag_filename'];
                }
                if (empty($inject['requestbuffer_duration'])) {
                    $requestBufferDuration = 60;
                } else {
                    $requestBufferDuration = intval($inject['requestbuffer_duration']);
                }

                // Action if php choosed old release to serve request
                if (empty($inject['oldrelease_flag__filename'])) {
                    $oldReleaseFlagFilename = '.flag.oldrelease';
                } else {
                    $oldReleaseFlagFilename = $inject['oldrelease_flag_filename'];
                }
                if (empty($inject['oldrelease_redirect_sleep'])) {
                    $oldReleaseRedirectSleep = 1000000; // 1s
                } else {
                    $oldReleaseRedirectSleep = intval($inject['oldrelease_redirect_sleep']);
                }
                // Clearstatcache for n seconds after deploy
                if (empty($inject['clearstatcache_flag_filename'])) {
                    $clearStatCacheFlagFilename = '.flag.clearstatcache';
                } else {
                    $clearStatCacheFlagFilename = $inject['clearstatcache_flag_filename'];
                }
                if (empty($inject['clearstatcache_duration'])) {
                    $clearStatCacheDuration = 150; // 120s is php default -> http://php.net/realpath-cache-ttl
                } else {
                    $clearStatCacheDuration = intval($inject['clearstatcache_duration']);
                }

                if (empty($inject['entrypoint_inject'])) {
                    $entrypointInject =
                        "// Buffering php requests\n" .
                        "isset(\$_SERVER['HTTP_X_DEPLOYER_DEPLOYMENT']) && \$_SERVER['HTTP_X_DEPLOYER_DEPLOYMENT'] == '{{random}}' ? \$deployerExtendedEnableBufferLock = false: \$deployerExtendedEnableBufferLock = true;\n" .
                        "isset(\$_ENV['DEPLOYER_DEPLOYMENT']) && \$_ENV['DEPLOYER_DEPLOYMENT'] == '{{random}}' ? \$deployerExtendedEnableBufferLock = false: \$deployerExtendedEnableBufferLock = true;\n" .
                        "clearstatcache(true, __DIR__ . '/$requestBufferFlagFilename');\n" .
                        "while (file_exists(__DIR__ . '/$requestBufferFlagFilename') && \$deployerExtendedEnableBufferLock) {\n" .
                        "    usleep($requestBufferSleep);\n" .
                        "    clearstatcache(true);\n" .
                        "    if(time() - @filectime(__DIR__ . '/$requestBufferFlagFilename') > $requestBufferDuration) @unlink(__DIR__ . '/$requestBufferFlagFilename');\n" .
                        "}\n" .
                        "// Clearstatcache for n seconds after deploy" .
                        "clearstatcache(true, __DIR__ . '/$clearStatCacheFlagFilename')\n" .
                        "if (file_exists(__DIR__ . '/$clearStatCacheFlagFilename')) {\n" .
                        "    clearstatcache(true);\n" .
                        "    if(time() - @filectime(__DIR__ . '/$clearStatCacheFlagFilename') > $clearStatCacheDuration) @unlink(__DIR__ . '/$clearStatCacheFlagFilename');\n" .
                        "}\n" .
                        "// Action if php choosed old release to serve request" .
                        "clearstatcache(true, __DIR__ . '/$oldReleaseFlagFilename')\n" .
                        "if(file_exists(__DIR__ . '/$oldReleaseFlagFilename')) {\n" .
                        "    clearstatcache(true);\n" .
                        "    usleep($oldReleaseRedirectSleep);\n" .
                        "    if(!empty(\$_SERVER['REQUEST_SCHEME']) && !empty(\$_SERVER['SERVER_NAME'])) {\n" .
                        "      header('Location: ' . \$_SERVER['REQUEST_SCHEME'] . '://' . \$_SERVER['SERVER_NAME'] . !empty(\$_SERVER['REQUEST_URI']) ? \$_SERVER['REQUEST_URI'] : '', true, 307);\n" .
                        "    } else {\n" .
                        "      exit();\n" .
                        "    }\n" .
                        "}";
                } else {
                    $entrypointInject = $inject['entrypoint_inject'];
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
                            run('mv -fT ' . $tempPath . '/' . basename($entrypointAbsolutePath) . ' ' . $entrypointAbsolutePath);
                        } else {
                            throw new \Exception('Can not find needle to inject with inclusion.');
                        }
                    } else {
                        throw new \Exception('Can not find file to overwrite or the file is empty. File that was read is: ' . $overwriteReleasePath . $entrypointFilename);
                    }
                }
                if (test('[ -e ' . $overwriteReleasePath . dirname($entrypointFilename) . ' ]')) {
                    run('cd ' . $overwriteReleasePath . ' && touch ' . (dirname($entrypointFilename) ? dirname($entrypointFilename) . '/' : '') . $requestBufferFlagFilename);
                    run('cd ' . $overwriteReleasePath . ' && touch ' . (dirname($entrypointFilename) ? dirname($entrypointFilename) . '/' : '') . $clearStatCacheFlagFilename);
                }
            }
            run('rmdir ' . $tempPath);
        }
    }
})->desc('Start buffering reqests to application entrypoints');
