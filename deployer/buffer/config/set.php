<?php

namespace Deployer;

// Array keys for better unsetting if someone would like to change only part of configuration
set('buffer_config', [
        'index.php' => [
            'entrypoint_filename' => 'index.php',
        ]
    ]
);
