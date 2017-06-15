<?php

namespace Deployer;

task('php:clear_cache_http', function () {
    $fileName = "cache_clear_" . get('random') . '.php';
    $phpCode = <<<EOT
<?php
clearstatcache(true);
if(function_exists('opcache_reset')) opcache_reset();
if(function_exists('eaccelerator_clear')) eaccelerator_clear();
EOT;

    $path = get('temp_dir') . md5($fileName . get('random'));
    file_put_contents($path, $phpCode);

    if (run('if [ -L {{deploy_path}}/current ] ; then echo true; fi')->toBool()) {
        upload($path, "{{deploy_path}}/current/" . $fileName);
        if (file_exists($path)) {
            unlink($path);
        }
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
    run('cd {{deploy_path}} && rm -f current/' . $fileName);
})->desc('Clear Apache/Nginx php caches for current release.');