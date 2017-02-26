<?php

namespace Deployer;

task('db:process', function () {
    if (input()->getOption('dumpcode')) {
        $dumpCode = input()->getOption('dumpcode');
    } else {
        throw new \InvalidArgumentException('No --dumpcode option set. [Error code: 1458937128562]');
    }

    $currentInstanceDatabaseStoragePath = get('current_server')->get('db_settings_storage_path');

    // remove "DEFINER" from the dump files to avoid problems with DEFINER views permissions
    // use hack for set multiple OS support (OSX/Linux) @see http://stackoverflow.com/a/38595160/1588346
    runLocally('sed --version >/dev/null 2>&1 && sed -i -- "s/DEFINER=[^*]*\*/\*/g" ' . $currentInstanceDatabaseStoragePath . '/*dumpcode:' . $dumpCode . '*.sql || sed -i \'\' \'s/DEFINER=[^*]*\*/\*/g\' ' . $currentInstanceDatabaseStoragePath . '/*dumpcode:' . $dumpCode . '*.sql');
})->desc('Run DEFINER replace on local databases defined by "dumpcode".');
