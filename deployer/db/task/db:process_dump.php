<?php

namespace Deployer;

task('db:process_dump', function () {

    $localStoragePath = get('db_get_local_server')->get('db_settings_storage_path');
    if (!file_exists($localStoragePath)) {
        mkdir($localStoragePath, 0755, true);
    }

    $dumpCode = null;
    if (input()->hasOption('dumpcode')) {
        $dumpCode = input()->getOption('dumpcode');
    }

    // remove "DEFINER" from the dump files to avoid problems with DEFINER views permissions
    // use hack for set multiple OS support (OSX/Linux) @see http://stackoverflow.com/a/38595160/1588346
    runLocally('sed --version >/dev/null 2>&1 && sed -i -- "s/DEFINER=[^*]*\*/\*/g" ' . $localStoragePath . '/*dumpcode:' . $dumpCode . '*.sql || sed -i \'\' \'s/DEFINER=[^*]*\*/\*/g\' ' . $localStoragePath . '/*dumpcode:' . $dumpCode . '*.sql');

})->desc('Run DEFINER replace on local databases defined by "dumpcode".');