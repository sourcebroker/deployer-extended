<?php

namespace Deployer;

task('db:truncate_table_remote', function () {

    run("cd {{deploy_path}}/current && {{bin/php}} deployer.phar -q db:truncate_table " . get('server')['name']);

})->desc('Truncate tables defined as caching tables on remote instance.');