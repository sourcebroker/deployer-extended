<?php

namespace Deployer;

task('deployer:download', function () {
    switch ((get('deployer_version'))) {
        case 4:
            set('download_link', 'https://deployer.org/releases/v4.1.0/deployer.phar');
            break;
        default:
            set('download_link', 'https://deployer.org/releases/v3.3.0/deployer.phar');
    }

    if (run('if [ -L {{deploy_path}}/release ] ; then echo true; fi')->toBool()) {
        set('download_path', get('deploy_path') . '/release');
    } else {
        set('download_path', get('deploy_path') . '/current');
    }

    run("if [ ! -f {{deploy_path}}/shared/deployer.phar ]; then cd {{deploy_path}}/shared && curl -OL {{download_link}} && chmod 775 deployer.phar; fi");
    run("if [ ! -f {{download_path}}/deployer.phar ]; then cd {{download_path}} && ln -s {{deploy_path}}/shared/deployer.phar deployer.phar; fi");
})->desc('Downloading deployer to shared. If deployer exist there already then only symlink.');
