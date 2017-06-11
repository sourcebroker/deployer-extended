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

set('bin/deployer', function () {
    $deployerMinimumSizeInBytesForDownloadCheck = 100000;
    set('active_path', get('deploy_path') . '/' . (test('[ -L {{deploy_path}}/release ]') ? 'release' : 'current'));

    // Figure out what version is expected to be used. "deployer_version" can be either integer "4"
    // or full verison "4.3.0". There is no support for "4.3".
    // TODO: Find the most recent versions automaticaly
    $deployerVersionRequestedByUser = trim(get('deployer_version'));
    if (strpos($deployerVersionRequestedByUser, '.') === false) {
        switch (($deployerVersionRequestedByUser)) {
            case 5:
                $deployerVersionToUse = '5.0.3';
                break;
            case 4:
                $deployerVersionToUse = '4.3.0';
                break;
            case 3:
                $deployerVersionToUse = '3.3.0';
                break;
            case 2:
                $deployerVersionToUse = '2.0.5';
                break;
            default:
                throw new \RuntimeException('You requested deployer version "' . $deployerVersionRequestedByUser . '" 
                but we are not able to determine what exact version is supposed to be chosen. 
                Please set "deployer_version" to full semantic versioning like "' . $deployerVersionRequestedByUser . '.0.1"');
        }
    } else {
        if (count(explode('.', $deployerVersionRequestedByUser)) === 3) {
            $deployerVersionToUse = $deployerVersionRequestedByUser;
        } else {
            throw new \RuntimeException('Deployer version must be set to just "major" like "4" which means give me most '
                . 'fresh version for deployer 4 or "major.minor.path" like "4.3.0". The "' . $deployerVersionRequestedByUser
                . '" is not supported.');
        }
    }
    set('deployer_filename', 'deployer-' . $deployerVersionToUse . '.phar');
    if (test('[ -f {{deploy_path}}/shared/{{deployer_filename}} ]')) {
        $downloadedFileSizeInBytes = trim(run('wc -c  < {{deploy_path}}/shared/{{deployer_filename}}')->toString());
        if ($downloadedFileSizeInBytes < $deployerMinimumSizeInBytesForDownloadCheck) {
            writeln(parse('Removing {{deploy_path}}/shared/{{deployer_filename}}" because the file size when last '
                . 'downloaded was ' . $downloadedFileSizeInBytes . ' bytes so downloads was probably not sucessful.'));
            run('rm -f {{deploy_path}}/shared/{{deployer_filename}}');

        }
    }
    //If we do not have yet this version fo deployer in shared then download it
    if (!test('[ -f {{deploy_path}}/shared/{{deployer_filename}} ]')) {
        set('deployer_download_link', 'https://deployer.org/releases/v' . $deployerVersionToUse . '/deployer.phar');
        run('cd {{deploy_path}}/shared && curl -o {{deployer_filename}} -L {{deployer_download_link}} && chmod 775 {{deployer_filename}}');
        //Simplified checker if deployer has been downloaded or not. Just check size of file which should be at least 200kB.
        $downloadedFileSizeInBytes = trim(run('wc -c  < {{deploy_path}}/shared/{{deployer_filename}}')->toString());
        if ($downloadedFileSizeInBytes > $deployerMinimumSizeInBytesForDownloadCheck) {
            run("cd {{deploy_path}}/shared && chmod 775 {{deployer_filename}}");
        } else {
            throw new \RuntimeException(parse('Downloaded deployer has size ' . $downloadedFileSizeInBytes . ' bytes. It seems'
                . ' like the download was unsucessfull. The file downloaded was: "{{deployer_download_link}}".'
                . 'Please check if this link return file. The downloaded content was stored in '
                . '{{deploy_path}}/shared/{{deployer_filename}}'));
        }
    }
    //Rebuild symlink of $deployerFilename to "{{active_path}}/deployer.phar"
    run("rm -f {{active_path}}/deployer.phar && cd {{active_path}} && {{bin/symlink}} {{deploy_path}}/shared/{{deployer_filename}} {{active_path}}/deployer.phar");
    if(test('[ -f {{active_path}}/deployer.phar ]')) {
        $deployerBin = parse('{{active_path}}/deployer.phar');
    } else {
        throw new \RuntimeException(parse('Can not create symlink from {{deploy_path}}/shared/{{deployer_filename}} to {{active_path}}/deployer.phar'));
    }
    return $deployerBin;
});

set('current_server', function () {
    try {
        $currentServer = Deployer::get()->environments[get('instance')];
    } catch (\RuntimeException $e) {
        $servers = '';
        $i = 1;
        foreach (Deployer::get()->environments as $key => $server) {
            $servers .= "\n" . $i++ . '. ' . $key;
        }
        throw new \RuntimeException("Name of instance \"" . get('instance') . "\" is not on the server list:" . $servers
            . "\nPlease check case sensitive. [Error code: 1458412947]");
    }
    return $currentServer;
});
