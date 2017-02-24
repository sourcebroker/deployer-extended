<?php

namespace Deployer;

task('media:push', function () {
    $config = array_merge_recursive(get('media-default'), get('media'));

    // TODO - $src can not be callable like this - this is bug
    $src = get('deploy_path') . '/current';
    while (is_callable($src)) {
        $src = $src();
    }

    print_r($src);
    die(' -> 24.02.2017 17:32');
    if (!trim($src)) {
        throw new \RuntimeException('You need to specify a source path.');
    }

    $dst = get('media_rsync_dest');
    while (is_callable($dst)) {
        $dst = $dst();
    }
    if (!trim($dst)) {
        throw new \RuntimeException('You need to specify a destination path.');
    }

    $server = \Deployer\Task\Context::get()->getServer()->getConfiguration();
    $host = $server->getHost();
    $port = $server->getPort() ? ' -p' . $server->getPort() : '';
    $identityFile = $server->getPrivateKey() ? ' -i ' . $server->getPrivateKey() : '';
    $user = !$server->getUser() ? '' : $server->getUser() . '@';

    $flags = isset($config['flags']) ? '-' . $config['flags'] : false;
    runLocally("rsync {$flags} -e 'ssh$port$identityFile' {{media_rsync_options}}{{media_rsync_includes}}{{media_rsync_excludes}}{{media_rsync_filter}} '$dst/' '$user$host:$src/' ",
        0);
})->desc('Synchronize media between instances.');
