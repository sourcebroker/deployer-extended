<?php

namespace Deployer;

use Deployer\Exception\Exception;

task('deploy:vhost_apache', function () {
    if (get('public_urls', null)) {
        $publicUrls = get('public_urls');
        $serverNames = [];
        if (count($publicUrls) > 0) {
            $serverNames[] = 'ServerName ' . parse_url($publicUrls[0])['host'];
            array_shift($publicUrls);
            foreach ($publicUrls as $publicUrl) {
                $serverNames[] = 'ServerAlias ' . parse_url($publicUrl)['host'];
            }
            $serverNames = array_map(function ($value) {
                return '    ' . $value;
            }, $serverNames);
            set('vhost_server_names', implode("\n", $serverNames));
        }
        set('reponame', runLocally('basename `git rev-parse --show-toplevel`'));
        file_put_contents(parse('{{current_dir}}/{{reponame}}.conf'), parse(get('vhost_template')));
        file_put_contents(parse('{{current_dir}}/{{reponame}}_ssl.conf'), parse(get('vhost_ssl_template')));
        if (get('vhost_path', false) !== false) {
            runLocally('mv {{current_dir}}/{{reponame}}.conf {{vhost_path}}');
            runLocally('mv {{current_dir}}/{{reponame}}_ssl.conf {{vhost_path}}');
        } else {
            throw new Exception('You did not set "vhost_path" var so we do not know where to copy generated vhosts. They were stored in {{deploy_path}}');
        }
    } else {
        throw new Exception('"public_urls" var was not set for server. Vhost can not be generated without it.');
    }
})->desc('Create vhost and copy to env path set in "vhost_path"');
