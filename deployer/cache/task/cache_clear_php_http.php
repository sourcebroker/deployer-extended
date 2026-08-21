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
    $timeout = (int)get('cache:clear_php_http:timeout', 15);

    // runLocally() lost its $options array in Deployer 8, so the timeout must be passed as a named
    // argument. Literal named-arg syntax would not parse on PHP 7.4, and call_user_func_array() only
    // honours string keys as named arguments on PHP 8+, hence the split. The named-arg call is valid
    // for both Deployer 7 and 8; the $options fallback is only ever reached on PHP 7.4, where
    // Deployer 8 (which requires PHP ^8.3) cannot be installed.
    $localRunWithTimeout = PHP_VERSION_ID >= 80000
        ? static fn(string $cmd) => call_user_func_array('Deployer\runLocally', ['command' => $cmd, 'timeout' => $timeout])
        : static fn(string $cmd) => runLocally($cmd, ['timeout' => $timeout]);

    switch (get('fetch_method', 'wget')) {
        case 'curl':
            $localRunWithTimeout(
                '{{local/bin/curl}} ' . get('fetch_method_curl_options',
                    '--insecure --silent --location --output /dev/null --write-out "%{http_code}"') . ' ' . escapeshellarg($clearCacheUrl) . ' 2>/dev/null'
            );
            break;

        case 'file_get_contents':
            $localRunWithTimeout(
                '{{local/bin/php}} -r \'file_get_contents("' . escapeshellarg($clearCacheUrl) . '");\''
            );
            break;

        case 'wget':
        default:
            $localRunWithTimeout(
                '{{local/bin/wget}} ' . get('fetch_method_wget_options',
                    '--no-check-certificate -q -O /dev/null') . ' ' . escapeshellarg($clearCacheUrl)
            );
            break;
    }
})->desc('Clear php caches for current release')->hidden();
