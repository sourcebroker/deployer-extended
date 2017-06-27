<?php

namespace Deployer;

// SOME DEFAULT VARS SPECIFIC FOR DEPLOYER

// Turn on the fast defaults
set('ssh_type', 'native');
set('ssh_multiplexing', true);


// SOME DEFAULT VARS SPECIFIC FOR DEPLOYER-EXTENDED

// Common random that can be used between tasks. Must be in form that can be used directly in filename!
set('random', md5(time() . rand()));

// Path to public when not in root of project. Must be like "pub/" so without starting slash and with ending slash.
set('web_path', '');

// Declare the way you want to fetch remote files on current instance. Options are "wget" / "file_get_content".
set('fetch_method', 'wget');

// Return path to php on current instance
set('local/bin/php', function () {
    return runLocally('which php')->toString();
});

// Return path to composer on current instance
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

// We assume deploy.php is in project root.
set('current_dir', function () {
    $current = getcwd();
    if (is_dir($current) && file_exists($current . '/deploy.php')) {
        return $current;
    } else {
        throw new \RuntimeException('Can not set "current_dir" var. Are you in folder with deploy.php file?');
    }
});

// TODO: REMOVE this var in some major change
set('temp_dir', sys_get_temp_dir() . '/');
