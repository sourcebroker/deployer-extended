<?php

namespace Deployer;

require_once 'recipe/common.php';

// deployer default settings overrides
set('ssh_type', 'native');
set('ssh_multiplexing', true);

// some default vars specific for deployer-extended
set('deployer_version', 4);
set('random', md5(time() . rand()));
set('web_path', '');
set('temp_dir', sys_get_temp_dir() . '/');
set('fetch_method', 'wget');

set('current_server', function () {
    try {
        $currentServer = Deployer::get()->environments[get('instance')];
    } catch (\RuntimeException $e) {
        $servers = '';
        $i = 1;
        foreach (Deployer::get()->environments as $key => $server) {
            $servers .= "\n" . $i++ . '. ' . $key;
        }
        throw new \RuntimeException("Name of instance \"" . get('instance') . "\" is not on the server list:" . $servers . "\nPlease check case sensitive. [Error code: 1458412947]");
    }
    return $currentServer;
});
