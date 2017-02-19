<?php

namespace Deployer;

set('deploy_lock_inject_filename', 'index.php');
set('deploy_lock_filename', 'index_buffer.php');
set('deploy_lock_needle', '<?php');
