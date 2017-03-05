<?php

namespace Deployer;

set('vhost_template', '<VirtualHost *:80>

    ServerAdmin webmaster@localhost

    DocumentRoot "{{deploy_path}}/current"

    <Directory "{{deploy_path}}/current">
        Options -Indexes 
        Options FollowSymLinks MultiViews
        AllowOverride all
        Order allow,deny
        Allow from all
    </Directory>

{{vhost_server_names}}
    
</VirtualHost>
');

set('vhost_ssl_template', '<VirtualHost *:443>
    
    ServerAdmin webmaster@localhost

    DocumentRoot "{{deploy_path}}/current"

    <Directory "{{deploy_path}}/current">
        Options -Indexes 
        Options FollowSymLinks MultiViews
        AllowOverride all
        Order allow,deny
        Allow from all
    </Directory>
    
{{vhost_server_names}}
    
    SSLEngine on
    SSLCertificateFile "{{vhost_ssl_path}}/domain.pem"
    SSLCertificateKeyFile "{{vhost_ssl_path}}/domain.key"
    SSLCACertificateFile "{{vhost_ssl_path}}/domain.intermediate"
    
</VirtualHost>');

set('vhost_path', getenv('VHOSTS_PATH'));
set('vhost_ssl_path', getenv('VHOSTS_SSLCERT_PATH'));
