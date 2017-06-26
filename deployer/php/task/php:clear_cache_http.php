<?php

namespace Deployer;

task('php:clear_cache_http', function () {
    $phpCode = "<?php\n"
        . "clearstatcache(true);\n"
        . "if(function_exists('opcache_reset')) opcache_reset();\n"
        . "if(function_exists('eaccelerator_clear')) eaccelerator_clear();";

    // Try to find fileName from previous release to prevent real_cache problems when "current" folder still points to
    // old release directory and Apache is giving 404 error becase clear_cache_* file does not exist in old release dir.
    $releasesList = get('releases_list');
    // get('releases_list') is cached by deployer on first call in other task so it does not have the latest release
    // this is why $releasesList[0] have last release and not current.
    $previosClearCacheFiles = null;
    if(isset($releasesList[0]) && test('[ -e {{deploy_path}}/releases/' . $releasesList[0] . ' ]')) {
        $previosClearCacheFiles = preg_split('/\R/',
            run("find {{deploy_path}}/releases/" . $releasesList[0] . " -name 'cache_clear_*'")->toString());
    }
    if (!empty($previosClearCacheFiles) && isset($previosClearCacheFiles[0]) && !empty($previosClearCacheFiles[0])) {
        $fileName = pathinfo($previosClearCacheFiles[0], PATHINFO_BASENAME);
    } else {
        $fileName = "cache_clear_" . get('random') . '.php';
    }
    if (run('if [ -L {{deploy_path}}/current ] ; then echo true; fi')->toBool()) {
        run('cd {{deploy_path}}/current && echo ' . escapeshellarg($phpCode) . ' > ' . $fileName);
    }

    $publicUrls = get('public_urls');
    if (!count($publicUrls)) {
        throw new \Deployer\Exception\ConfigurationException('You need at least one "public_url" to call task cache:frontendreset');
    }

    $defaultPublicUrl = rtrim(get('public_urls')[0], '/') . '/';

    switch (get('fetch_method')) {
        case 'wget':
            runLocally("wget --no-check-certificate --quiet --delete-after  '" . $defaultPublicUrl . $fileName . "'",
                15);
            break;

        case 'file_get_contents':
            runLocally('php -r \'file_get_contents("' . $defaultPublicUrl . $fileName . '");\'', 15);
            break;
    }
})->desc('Clear Apache/Nginx php caches for current release.');
