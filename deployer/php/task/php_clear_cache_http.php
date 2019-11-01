<?php

namespace Deployer;

// Read more on https://github.com/sourcebroker/deployer-extended#php-clear-cache-http
task('php:clear_cache_http', function () {
    if (empty(get('random'))) {
        throw new \Exception('The "random" var is empty.');
    }
    if (preg_match('/^[a-zA-Z0-9]$/', get('random'))) {
        throw new \Exception('The "random" var should be only /^[a-zA-Z0-9]$/ because its used in filenames.');
    }
    // Try to find fileName from previous release to prevent real_cache problems when "current" folder still points to
    // old release directory and Apache is giving 404 error because clear_cache_* file does not exist in old release dir.
    $releasesList = get('releases_list');
    // get('releases_list') is cached by deployer on first call in other task so it does not have the latest release
    // this is why $releasesList[0] have last release and not current.
    $previousClearCacheFiles = null;
    if (isset($releasesList[0]) && test('[ -e {{deploy_path}}/releases/' . $releasesList[0] . ' ]')) {
        $previousClearCacheFiles = preg_split('/\R/',
            run("find {{deploy_path}}/releases/" . $releasesList[0] . "/{{web_path}} -name 'cache_clear_*'"));
    }
    if (!empty($previousClearCacheFiles) && !empty($previousClearCacheFiles[0])) {
        $fileName = pathinfo($previousClearCacheFiles[0], PATHINFO_BASENAME);
    } else {
        $fileName = "cache_clear_" . get('random') . '.php';
    }
    if (test('[ -L {{deploy_path}}/current ]')) {
        run('cd {{deploy_path}}/current/{{web_path}} && echo ' . escapeshellarg(
                get('php:clear_cache_http:phpcontent', "<?php\n"
                    . "clearstatcache(true);\n"
                    . "if(function_exists('opcache_reset')) opcache_reset();\n"
                    . "if(function_exists('eaccelerator_clear')) eaccelerator_clear();"
                )) . ' > ' . $fileName);
    }

    if (empty(get('public_urls', []))) {
        throw new \Exception('You need at least one "public_url" to call task php:clear_cache_http');
    }
    $clearCacheUrl = rtrim(get('public_urls')[0], '/') . '/' . $fileName;

    switch (get('fetch_method', 'wget')) {
        case 'curl':
            runLocally(
                '{{local/bin/curl}} --insecure --silent --location ' . escapeshellarg($clearCacheUrl) . ' > /dev/null',
                ['timeout', get('php:clear_cache_http:timeout', 15)]
            );
            break;

        case 'file_get_contents':
            runLocally(
                '{{local/bin/php}} -r \'file_get_contents("' . escapeshellarg($clearCacheUrl) . '");\'',
                ['timeout', get('php:clear_cache_http:timeout', 15)]
            );
            break;

        case 'wget':
        default:
            runLocally(
                '{{local/bin/wget}} -q -O /dev/null ' . escapeshellarg($clearCacheUrl),
                ['timeout', get('php:clear_cache_http:timeout', 15)]
            );
            break;
    }
})->desc('Clear php caches for current release');
