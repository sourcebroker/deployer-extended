<?php

namespace Deployer;

task('cache:frontendreset', function () {

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
        @unlink($path);
    }

    $loop = 0;
    while ($loop < get('opcache_loop')) {
        try {
            if ('wget' == get('fetch_method')) {
                runLocally("wget --no-check-certificate --quiet --delete-after  '{{stage_url}}" . $fileName . "'", 15);
            } elseif ('file_get_contents' == get('fetch_method')) {
                runLocally('php -r \'file_get_contents("{{stage_url}}' . $fileName . '");\'', 15);
            }
            writeln('Loop: ' . $loop);
            $loop = get('opcache_loop');
        } catch (\RuntimeException $e) {
            $loop++;
        }
    }

    run('cd {{deploy_path}} && rm -f current/' . $fileName);

})->desc('Clear Apache/Nginx php caches for current.');