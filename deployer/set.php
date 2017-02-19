<?php

namespace Deployer;

require_once 'recipe/common.php';

// deployer default settings overrides
set('writable_mode', 'chgrp');
set('keep_releases', 3);
set('ssh_type', 'native');
set('ssh_multiplexing', true);

// some default vars specific for deployer-extended
set('deployer_version', 4);
set('random', md5(time() . rand()));
set('web_path', '');
set('temp_dir', sys_get_temp_dir() . '/');
set('opcache_loop', 50);
set('fetch_method', 'wget');
