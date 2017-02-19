<?php

namespace Deployer;

task('lock:create_lock_files', function(){

    $random = get('random');
    $phpCode = <<<EOT
<?php 
\$enableLock = true;
if (isset(\$_SERVER['HTTP_X_DEPLOYMENT']) && \$_SERVER['HTTP_X_DEPLOYMENT'] == '$random') {
    \$enableLock = false;
} 
while (file_exists(__DIR__ . '/deployment.lock') && \$enableLock) {
    usleep(200000); clearstatcache();
}
EOT;

    $path = get('temp_dir').md5(get('deploy_lock_filename').get('random'));
    file_put_contents($path,$phpCode);

    # add lock files to new release
    upload($path, "{{release_path}}/{{web_path}}{{deploy_lock_filename}}");
    run('cd {{release_path}} && touch {{web_path}}deployment.lock');

    # add lock files to current release (check for dir because on first deploy there is no current yet)
    if (run('if [ -L {{deploy_path}}/current ] ; then echo true; fi')->toBool()) {
        upload($path, "{{deploy_path}}/current/{{deploy_lock_filename}}");
        run('cd {{deploy_path}} && touch current/deployment.lock');
    }

    @unlink($path);

})->desc('Create lock files on DocumentRoot');
