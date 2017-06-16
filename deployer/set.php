<?php

namespace Deployer;

require_once 'recipe/common.php';

// deployer default settings overrides
set('ssh_type', 'native');
set('ssh_multiplexing', true);

// some default vars specific for deployer-extended
set('random', md5(time() . rand()));
set('web_path', '');
set('temp_dir', sys_get_temp_dir() . '/');
set('fetch_method', 'wget');

set('local/bin/php', function () {
    return runLocally('which php')->toString();
});

set('local/bin/composer', function () {
    $composerBin = null;
    //check for composer in local project
    if (file_exists('./composer.phar')) {
        $composerBin = '{{local/bin/php}} composer.phar';
    }
    //check for composer in global
    if (empty($composerBin)) {
        $composerBin = '{{local/bin/php}} ' . runLocally('which composer')->toString();
    }
    // no local and global then try to download composer
    // https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md
    if (empty($composerBin)) {
        $composerDownloaded = runLocally('EXPECTED_SIGNATURE=$(wget -q -O - https://composer.github.io/installer.sig)
                    php -r "copy(\'https://getcomposer.org/installer\', \'composer-setup.php\');"
                    ACTUAL_SIGNATURE=$(php -r "echo hash_file(\'SHA384\', \'composer-setup.php\');")
    
                    if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]
                    then
                        >&2 echo \'ERROR: Invalid installer signature\'
                        rm composer-setup.php
                        exit 1
                    fi
                    
                    php composer-setup.php --quiet
                    RESULT=$?
                    rm composer-setup.php
                    exit $RESULT
                    ');
        if ($composerDownloaded) {
            $composerBin = '{{local/bin/php}} composer.phar';
        }
    }
    if (empty($composerBin)) {
        throw new \RuntimeException("No composer found or can be installed on current instance. [Error code: 1496953936]");
    }
    return $composerBin;
});
