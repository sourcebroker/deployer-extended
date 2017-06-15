<?php

namespace Deployer;

task('media:move', function () {
    if (null === input()->getArgument('stage')) {
        throw new \RuntimeException("The target instance is required for media:move command. [Error code: 1488149866477]");
    }
    if (null !== input()->getArgument('targetStage')) {
        $targetInstanceName = input()->getArgument('targetStage');
        if ($targetInstanceName == 'live') {
            throw new \RuntimeException(
                "FORBIDDEN: For security its forbidden to move media to live instance!"
            );
        }
        if ($targetInstanceName == 'local') {
            throw new \RuntimeException(
                "FORBIDDEN: For synchro local media use: \ndep media:pull " . $targetInstanceName
            );
        }
    } else {
        throw new \RuntimeException(
            "You must set the target instance the media will be copied to as second parameter."
        );
    }
    $targetServer = get('current_server')->get('server')['name'];

    runLocally('{{bin/php}} {{local/bin/deployer}} media:pull ' . get('server')['name']);
    runLocally('{{bin/php}} {{local/bin/deployer}} media:push ' . $targetServer);
})->desc('Synchronize media between instances.');
