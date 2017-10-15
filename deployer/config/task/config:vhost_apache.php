<?php

namespace Deployer;

task('config:vhost_apache', function () {
    if (get('public_urls', false)) {
        if (is_array(get('public_urls'))) {
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

    LogFormat "%v:%p %h %l %u %t \"%r\" %>s %b \"%{Referer}i\"" vhostcombined
    CustomLog "{{vhost_logs_path}}{{vhost_logs_access_log_filename}}" vhostcombined
    ErrorLog "{{vhost_logs_path}}{{vhost_logs_error_log_filename}}"
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

    SSLEngine on
    SSLCertificateFile "{{vhost_sslcert_path}}/domain.pem"
    SSLCertificateKeyFile "{{vhost_sslcert_path}}/domain.key"
    SSLCACertificateFile "{{vhost_sslcert_path}}/domain.intermediate"

    LogFormat "%v:%p %h %l %u %t \"%r\" %>s %b \"%{Referer}i\"" vhostcombined
    CustomLog "{{vhost_logs_path}}{{vhost_logs_access_log_filename}}" vhostcombined
    ErrorLog "{{vhost_logs_path}}{{vhost_logs_error_log_filename}}"
</VirtualHost>
');
            }
            if (get('vhost_path', false) === false) {
                set('vhost_path', getenv('VHOST_PATH'));
            }
            if (get('vhost_sslcert_path', false) === false) {
                set('vhost_sslcert_path', getenv('VHOST_SSLCERT_PATH'));
            }
            if (get('vhost_proxy', false) === false) {
                set('vhost_proxy', getenv('VHOST_PROXY'));
            }
            if (get('vhost_proxy_directive', false) === false) {
                set('vhost_proxy_directive', getenv('VHOST_PROXY_DIRECTIVE'));
            }
            if (get('vhost_proxy_directive', false) === false) {
                set('vhost_proxy_port', getenv('VHOST_PROXY_PORT'));
            }
            if (get('vhost_nocurrent', false) === false) {
                set('vhost_document_root', get('deploy_path') . '/current');
            } else {
                set('vhost_document_root', get('deploy_path'));
            }
            if (get('vhost_logs_error_log_filename', false) === false) {
                set('vhost_logs_error_log_filename', getenv('VHOST_LOGS_ERROR_LOG_FILENAME'));
            }
            if (get('vhost_logs_error_log_filename', false) === false) {
                set('vhost_logs_error_log_filename', 'error_log');
            }
            if (get('vhost_logs_access_log_filename', false) === false) {
                set('vhost_logs_access_log_filename', getenv('VHOST_LOGS_ACCESS_LOG_FILENAME'));
            }
            if (get('vhost_logs_access_log_filename', false) === false) {
                set('vhost_logs_access_log_filename', 'access_log');
            }
            if (get('vhost_logs_path', false) === false) {
                set('vhost_logs_path', getenv('VHOST_LOGS_PATH'));
            }
            if (get('vhost_logs_path', false) === false) {
                set('vhost_logs_path', get('vhost_document_root') . '/.dep/logs/');
            }
            if (!file_exists(get('vhost_logs_path'))) {
                mkdir(get('vhost_logs_path'), 0777, true);
            }
            set('vhost_projectname', basename(get('current_dir')));

            set('vhost_server_names',
                implode("\n", array_map(function ($publicUrl, $key) {
                    $naming = $key === 0 ? 'ServerName' : 'ServerAlias';
                    return '    ' . $naming . ' ' . parse_url($publicUrl)['host'];
                }, get('public_urls'), array_keys(get('public_urls')))));

            // Apache writes log with different user so lets create it for him
            if (!file_exists(get('vhost_logs_path') . get('vhost_logs_access_log_filename'))) {
                touch(get('vhost_logs_path') . get('vhost_logs_access_log_filename'));
            }
            if (!file_exists(get('vhost_logs_path') . get('vhost_logs_error_log_filename'))) {
                touch(get('vhost_logs_path') . get('vhost_logs_error_log_filename'));
            }
            if (get('vhost_proxy', true)) {
                if (get('vhost_proxy_directive', false) === false) {
                    if (get('vhost_proxy_port', false) === false) {
                        $composerJson = \json_decode(file_get_contents(get('current_dir') . '/composer.json'), true);
                        if (!empty($composerJson['config']) && !empty($composerJson['config']['platform']) && !empty($composerJson['config']['platform']['php'])) {
                            $phpVersionParts = explode('.', $composerJson['config']['platform']['php']);
                            if (count($phpVersionParts)) {
                                $phpVersionTwoDigit = (count($phpVersionParts) === 1) ? $phpVersionParts[0] : $phpVersionParts[0] . $phpVersionParts[1];
                                if (get('vhost_proxy_port_php' . $phpVersionTwoDigit, false)) {
                                    set('vhost_proxy_port', get('vhost_proxy_port_php' . $phpVersionTwoDigit));
                                } else {
                                    set('vhost_proxy_port', '90' . $phpVersionTwoDigit);
                                }
                            } else {
                                throw new \Exception('deployer-extended has problem trying to detect proper version of 
                        php needed to be set later in ProxyPassMatch. The version of php was tried to be read from the 
                        value stored in composer.json config/platform/php but either is not set or set with some strange 
                        value that can not be parsed.');
                            }
                        }
                    }
                    set('vhost_proxy_directive',
                        parse('ProxyPassMatch ^/(.*\.php(/.*)?)$ fcgi://127.0.0.1:{{vhost_proxy_port}}{{vhost_document_root}}/$1'));
                }
            } else {
                set('vhost_proxy_directive', '');
            }

            file_put_contents(parse('{{current_dir}}/{{vhost_projectname}}.conf'), parse(get('vhost_template')));

            if (get('vhost_path', false) !== false) {
                runLocally('mv ' . parse('{{vhost_path}}/{{vhost_projectname}}.conf {{vhost_path}}/{{vhost_projectname}}.conf.' . strftime('%Y%m%d%H%M%S')));
                runLocally('mv {{current_dir}}/{{vhost_projectname}}.conf ' . parse('{{vhost_path}}/{{vhost_projectname}}.conf'));
            } else {
                writeln(parse('You did not set "VHOST_PATH" env var so we do not know where to copy generated vhosts.' .
                    'This is why vhost was stored in {{current_dir}} so you can move it manually.'));
            }
        } else {
            throw new \Exception('"public_urls" config var must be an array of urls.');
        }
    } else {
        throw new \Exception('"public_urls" config var was not set for server. Vhost can not be generated without it.');
    }
})->desc('Create vhost and copy to env path set in "VHOST_PATH"');
