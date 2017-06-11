<?php

namespace Deployer;

set('vhost_template', '<VirtualHost *:80>

    ServerAdmin webmaster@localhost

    DocumentRoot "{{document_root}}"

    <Directory "{{document_root}}">
        Options -Indexes 
        Options FollowSymLinks MultiViews
        AllowOverride all
        Order allow,deny
        Allow from all
    </Directory>

{{vhost_server_names}}
    
</VirtualHost>

<VirtualHost *:443>
    
    ServerAdmin webmaster@localhost

    DocumentRoot "{{document_root}}"

    <Directory "{{document_root}}">
        Options -Indexes 
        Options FollowSymLinks MultiViews
        AllowOverride all
        Order allow,deny
        Allow from all
    </Directory>
    
{{vhost_server_names}}
    
    SSLEngine on
    SSLCertificateFile "{{vhost_sslcert_path}}/domain.pem"
    SSLCertificateKeyFile "{{vhost_sslcert_path}}/domain.key"
    SSLCACertificateFile "{{vhost_sslcert_path}}/domain.intermediate"
    
</VirtualHost>
');

set('vhost_path', getenv('VHOST_PATH'));
set('vhost_sslcert_path', getenv('VHOST_SSLCERT_PATH'));
