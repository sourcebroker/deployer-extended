<?php

namespace Deployer;

// Read more on https://github.com/sourcebroker/deployer-extended#cache-clear-php-http
task('cache:clear_php_http', function () {
    if (empty(get('random'))) {
        throw new \Exception('The "random" var is empty.');
    }
    if (preg_match('/^[a-zA-Z0-9]$/', get('random'))) {
        throw new \Exception('The "random" var should be only /^[a-zA-Z0-9]$/ because its used in filenames.');
    }
    // Try to find fileName from previous release to prevent real_cache problems when "current" folder still points to
    // old release directory and Apache is giving 404 error because clear_cache_* file does not exist in old release dir.
    $releasesList = get('releases_list');
    $previousClearCacheFiles = null;
    if (isset($releasesList[1]) && test('[ -e {{deploy_path}}/releases/' . $releasesList[1] . ' ]')) {
        $previousClearCacheFiles = preg_split('/\R/',
            run("find {{deploy_path}}/releases/" . $releasesList[1] . "/{{web_path}} -name 'cache_clear_random_*'"));
    }
    if (!empty($previousClearCacheFiles) && !empty($previousClearCacheFiles[0])) {
        $fileName = pathinfo($previousClearCacheFiles[0], PATHINFO_BASENAME);
    } else {
        $fileName = "cache_clear_random_" . get('random') . '.php';
    }
    if (test('[ -L {{deploy_path}}/current ]')) {
        run('cd {{deploy_path}}/current/{{web_path}} && echo ' . escapeshellarg(
                get('cache:clear_php_http:phpcontent', "<?php\n"
                    . "clearstatcache(true);\n"
                    . "if(function_exists('opcache_reset')) opcache_reset();\n"
                    . "if(function_exists('eaccelerator_clear')) eaccelerator_clear();"
                )) . ' > ' . $fileName);
    }

    if (empty(get('public_urls', []))) {
        throw new \Exception('You need at least one "public_url" to call task cache:clear_php_http');
    }
    $clearCacheUrl = rtrim(get('public_urls')[0], '/') . '/' . $fileName;

    switch (get('fetch_method', 'wget')) {
        case 'curl':
            runLocally(
                '{{local/bin/curl}} --insecure --silent --location ' . escapeshellarg($clearCacheUrl) . ' > /dev/null',
                ['timeout', get('cache:clear_php_http:timeout', 15)]
            );
            break;

        case 'file_get_contents':
            runLocally(
                '{{local/bin/php}} -r \'file_get_contents("' . escapeshellarg($clearCacheUrl) . '");\'',
                ['timeout', get('cache:clear_php_http:timeout', 15)]
            );
            break;

        case 'wget':
        default:
            runLocally(
                '{{local/bin/wget}} -q -O /dev/null ' . escapeshellarg($clearCacheUrl),
                ['timeout', get('cache:clear_php_http:timeout', 15)]
            );
            break;
    }
})->desc('Clear php caches for current release');
