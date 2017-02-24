<?php

namespace Deployer;

task('media:move', function () {

    $targetServer = get('db_get_local_server')->get('server')['name'];

    runLocally('{{bin/php}} {{bin/deployer}} media:pull ' . get('server')['name']);
    runLocally('{{bin/php}} {{bin/deployer}} media:push ' . $targetServer);

})->desc('Synchronize media between instances.');
