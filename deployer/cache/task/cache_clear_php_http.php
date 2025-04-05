<?php

namespace Deployer;

use Deployer\Exception\GracefulShutdownException;

// Read more on https://github.com/sourcebroker/deployer-extended#cache-clear-php-http
task('cache:clear_php_http', function () {
    $random = md5(time() . mt_rand());
    // Try to find fileName from previous release to prevent real_cache problems when "current" folder still points to
    // old release directory and Apache is giving 404 error because clear_cache_* file does not exist in old release dir.
    $releasesList = get('releases_list');
    $webPath = get('web_path', '');
    $previousClearCacheFiles = null;
    if (isset($releasesList[1]) && test('[ -e {{deploy_path}}/releases/' . $releasesList[1] . ' ]')) {
        $previousClearCacheFiles = preg_split(
            '/\R/',
            run("find {{deploy_path}}/releases/$releasesList[1]/$webPath -name 'cache_clear_random_*'")
        );
    }
    if (!empty($previousClearCacheFiles) && !empty($previousClearCacheFiles[0])) {
        $fileName = pathinfo($previousClearCacheFiles[0], PATHINFO_BASENAME);
    } else {
        $fileName = "cache_clear_random_" . $random . '.php';
    }
    if (test('[ -L {{deploy_path}}/current ]')) {
        run('cd {{deploy_path}}/current/' . $webPath . ' && echo ' . escapeshellarg(
                get(
                    'cache:clear_php_http:phpcontent',
                    "<?php\n"
                    . "clearstatcache(true);\n"
                    . "if(function_exists('opcache_reset')) {opcache_reset();}\n"
                )
            ) . ' > ' . $fileName);
    }

    if (empty(get('public_urls', []))) {
        throw new GracefulShutdownException('You need at least one "public_url" to call task cache:clear_php_http');
    }
    $clearCacheUrl = rtrim(get('public_urls')[0], '/') . '/' . $fileName;

    switch (get('fetch_method', 'wget')) {
        case 'curl':
            runLocally(
                '{{local/bin/curl}} ' . get('fetch_method_curl_options',
                    '--insecure --silent --location') . ' ' . escapeshellarg($clearCacheUrl) . ' > /dev/null',
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
                '{{local/bin/wget}} ' . get('fetch_method_wget_options',
                    '--no-check-certificate -q -O /dev/null') . ' ' . escapeshellarg($clearCacheUrl),
                ['timeout', get('cache:clear_php_http:timeout', 15)]
            );
            break;
    }
})->desc('Clear php caches for current release')->hidden();
