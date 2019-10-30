<?php

namespace Deployer;

// Read more on https://github.com/sourcebroker/deployer-extended#config-vhost-apache
task('config:vhost_apache', function () {
    if (get('public_urls', false)) {
        if (is_array(get('public_urls'))) {
            if (get('vhost_path', false) === false) {
                if (getenv('VHOST_PATH') !== false) {
                    set('vhost_path', getenv('VHOST_PATH'));
                }
            }
            set('vhost_sslcert_path_missing', '');
            if (get('vhost_sslcert_path', false) === false) {
                if (getenv('VHOST_SSLCERT_PATH') !== false) {
                    set('vhost_sslcert_path', getenv('VHOST_SSLCERT_PATH'));
                } else {
                    set('vhost_sslcert_path_missing', '#');
                    set('vhost_sslcert_path', '');
                    writeln('WARNING! A path to ssl certificates is missing! Set it on your env "VHOST_SSLCERT_PATH" or ' .
                        'or with configuration var "vhost_sslcert_path". For now the SSL in vhost is disabled.');
                }
            }
            if (get('vhost_sslcert_path', '') !== '') {
                array_map(function ($file) {
                    if (testLocally('[ ! -e ' . get('vhost_sslcert_path') . ' ]')) {
                        writeln('WARNING! A SSL file ' . $file . ' is missing in path: "' . get('vhost_sslcert_path') . '"');
                    }
                }, ['domain.pem', 'domain.key', 'domain.intermediate']);
            }
            if (get('vhost_proxy', false) === false) {
                if (getenv('VHOST_PROXY') !== false) {
                    set('vhost_proxy', getenv('VHOST_PROXY'));
                }
            }
            if (get('vhost_document_root', false) === false) {
                if (get('vhost_nocurrent', false) === false) {
                    set('vhost_document_root', get('deploy_path') . '/current');
                } else {
                    set('vhost_document_root', get('deploy_path'));
                }
            }
            set('vhost_projectname', basename(get('current_dir')));
            if (get('vhost_logs_error_log_filename', false) === false) {
                if (getenv('VHOST_LOGS_ERROR_LOG_FILENAME') !== false) {
                    set('vhost_logs_error_log_filename', getenv('VHOST_LOGS_ERROR_LOG_FILENAME'));
                }
            }
            if (get('vhost_logs_error_log_filename', false) === false) {
                set('vhost_logs_error_log_filename', get('vhost_projectname') . '_error_log');
            }
            if (get('vhost_logs_access_log_filename', false) === false) {
                if (getenv('VHOST_LOGS_ACCESS_LOG_FILENAME') !== false) {
                    set('vhost_logs_access_log_filename', getenv('VHOST_LOGS_ACCESS_LOG_FILENAME'));
                }
            }
            if (get('vhost_logs_access_log_filename', false) === false) {
                set('vhost_logs_access_log_filename', get('vhost_projectname') . '_access_log');
            }
            if (get('vhost_logs_path', false) === false) {
                set('vhost_logs_path', getenv('VHOST_LOGS_PATH'));
            }
            if (get('vhost_local_logs_path', false) === false) {
                if (get('vhost_nocurrent', false) === false) {
                    set('vhost_local_logs_path', get('deploy_path') . '/.dep/logs');
                } else {
                    set('vhost_local_logs_path', get('vhost_document_root') . '/.dep/logs');
                }
            }
            if (!testLocally('[ -e ' . get('vhost_local_logs_path') . ' ]')) {
                runLocally('mkdir -p ' . get('vhost_local_logs_path'));
            }
            set('vhost_server_names',
                implode("\n", array_map(function ($publicUrl, $key) {
                    $naming = $key === 0 ? 'ServerName' : 'ServerAlias';
                    return '    ' . $naming . ' ' . parse_url($publicUrl)['host'];
                }, get('public_urls'), array_keys(get('public_urls')))));
            // Apache logs
            if (testLocally('[ -e ' . escapeshellarg(rtrim(get('vhost_local_logs_path'))) . ' ]')) {
                foreach (['access', 'error'] as $logType) {
                    $logPathFile = rtrim(get('vhost_logs_path'),
                            '/') . '/' . get('vhost_logs_' . $logType . '_log_filename');
                    if (!testLocally('[ -e ' . escapeshellarg(get('vhost_local_logs_path') . '/' . $logType . '.log') . ' ]')) {
                        runLocally('ln -s ' . escapeshellarg($logPathFile) . ' ' . escapeshellarg(get('vhost_local_logs_path') . '/' . $logType . '.log'));
                    }
                }
            }
            // Determine the PHP version
            $phpVersionTwoDigit = null;
            if (runLocally('[ -e ' . get('current_dir') . '/composer.json' . ' ]')) {
                $composerJson = \json_decode(runLocally(
                    'cat ' . escapeshellarg(get('current_dir') . '/composer.json'))->toString(), true);
                if (!empty($composerJson['config']['platform']['php'])) {
                    $phpVersionParts = explode('.', $composerJson['config']['platform']['php']);
                    if (count($phpVersionParts)) {
                        $phpVersionTwoDigit = (count($phpVersionParts) === 1) ? $phpVersionParts[0] : $phpVersionParts[0] . $phpVersionParts[1];
                    } else {
                        writeln('WARNING! deployer-extended has problem trying to detect proper version of ' .
                            'php needed to be set later in ProxyPassMatch. The version of php was tried to be read from the ' .
                            'value stored in composer.json config/platform/php but it is set with some strange value that can' .
                            'not be parsed.');
                    }
                } else {
                    writeln('WARNING! deployer-extended has problem trying to detect proper version of ' .
                        'php needed to be set later in ProxyPassMatch. The version of php was tried to be read from the ' .
                        'value stored in composer.json config/platform/php but it is not set.');
                }
            } else {
                writeln('WARNING! deployer-extended has problem trying to detect proper version of ' .
                    'php needed to be set later in ProxyPassMatch. The version of php was tried to be read from the ' .
                    'value stored in composer.json config/platform/php but the file is missing.');
            }
            if ($phpVersionTwoDigit === null) {
                $phpVersionTwoDigit = ask('What is your php version for this project to be set in php-fpm vhost? 
                (two digit without dot like: 70, 71, 72)',
                    71);
            }
            set('phpVersionTwoDigit', $phpVersionTwoDigit);
            if (get('vhost_proxy_directive', false) === false) {
                if (getenv('VHOST_PROXY_PORT') !== false) {
                    set('vhost_proxy_port', getenv('VHOST_PROXY_PORT'));
                }
            }
            if (get('vhost_proxy_port_php' . $phpVersionTwoDigit, false)) {
                set('vhost_proxy_port', get('vhost_proxy_port_php' . $phpVersionTwoDigit));
            } else {
                set('vhost_proxy_port', '90' . $phpVersionTwoDigit);
            }
            if (get('vhost_proxy_directive', false) === false) {
                if (getenv('VHOST_PROXY_DIRECTIVE') !== false) {
                    set('vhost_proxy_directive', getenv('VHOST_PROXY_DIRECTIVE'));
                }
            }
            if (get('vhost_proxy', true)) {
                if (get('vhost_proxy_directive', false) === false) {
                    set('vhost_proxy_directive',
                        parse('ProxyPassMatch ^/(.*\.php(/.*)?)$ fcgi://127.0.0.1:{{vhost_proxy_port}}{{vhost_document_root}}/$1'));
                }
            } else {
                set('vhost_proxy_directive', '');
            }
            if (get('vhost_template', false) === false) {
                set('vhost_template', '<VirtualHost *:80>
    ServerAdmin webmaster@localhost

    DocumentRoot "{{vhost_document_root}}"
    {{vhost_proxy_directive}}

{{vhost_server_names}}

    <Directory "{{vhost_document_root}}">
        Options -Indexes
        Options FollowSymLinks MultiViews
        AllowOverride all
        Require all granted
    </Directory>

    LogFormat "%v:%p %h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" vhostcombined
    CustomLog "{{vhost_logs_path}}/{{vhost_logs_access_log_filename}}" vhostcombined
    ErrorLog "{{vhost_logs_path}}/{{vhost_logs_error_log_filename}}"
</VirtualHost>

<VirtualHost *:443>
    ServerAdmin webmaster@localhost

    DocumentRoot "{{vhost_document_root}}"
    {{vhost_proxy_directive}}

{{vhost_server_names}}

    <Directory "{{vhost_document_root}}">
        Options -Indexes
        Options FollowSymLinks MultiViews
        AllowOverride all
        Require all granted
    </Directory>

    {{vhost_sslcert_path_missing}}SSLEngine on
    {{vhost_sslcert_path_missing}}SSLCertificateFile "{{vhost_sslcert_path}}/domain.pem"
    {{vhost_sslcert_path_missing}}SSLCertificateKeyFile "{{vhost_sslcert_path}}/domain.key"
    {{vhost_sslcert_path_missing}}SSLCACertificateFile "{{vhost_sslcert_path}}/domain.intermediate"

    LogFormat "%v:%p %h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" vhostcombined
    CustomLog "{{vhost_logs_path}}/{{vhost_logs_access_log_filename}}" vhostcombined
    ErrorLog "{{vhost_logs_path}}/{{vhost_logs_error_log_filename}}"
</VirtualHost>
');
            }
            runLocally('echo ' . escapeshellarg(parse(get('vhost_template'))) . ' > ' . parse('{{current_dir}}/{{vhost_projectname}}.conf'));

            if (get('vhost_path', false) !== false) {
                if (testLocally('[ -e {{vhost_path}}/{{vhost_projectname}}.conf ]')) {
                    $oldVhostRename = parse('{{vhost_path}}/{{vhost_projectname}}.conf.' . strftime('%Y%m%d%H%M%S'));
                    runLocally('mv ' . parse('{{vhost_path}}/{{vhost_projectname}}.conf ' . $oldVhostRename));
                    writeln('Old vhost saved under ' . $oldVhostRename);
                }
                runLocally('mv {{current_dir}}/{{vhost_projectname}}.conf ' . parse('{{vhost_path}}/{{vhost_projectname}}.conf'));
                writeln(parse('New vhost saved under {{vhost_path}}/{{vhost_projectname}}.conf'));
            } else {
                writeln(parse('New vhost generated and saved under {{current_dir}}/{{vhost_projectname}}.conf.' . "\n" .
                    'NOTICE! If you would set config var "vhost_path" or env "VHOST_PATH" then deployer could copy the ' .
                    'generated vhost to your vhosts directory!'));
            }
        } else {
            throw new \Exception('"public_urls" config var must be an array of urls.');
        }
    } else {
        throw new \Exception('"public_urls" config var was not set for server. Vhost can not be generated without it.');
    }
})->desc('Create vhost and copy to path set in "VHOST_PATH" env var');
