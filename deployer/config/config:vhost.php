<?php

namespace Deployer;

use SourceBroker\DeployerExtended\Utility\FileUtility;

task('config:vhost_apache', function () {
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
        if (get('vhost_nocurrent', false) == true) {
            set('document_root', get('deploy_path'));
        } else {
            set('document_root', get('deploy_path') . '/current');
        }

        set('reponame', runLocally('basename `git rev-parse --show-toplevel`'));

        file_put_contents(parse('{{current_dir}}/{{reponame}}.conf'), parse(get('vhost_template')));

        if (!file_exists(parse('{{vhost_path}}/{{reponame}}.conf'))) {
            if (get('vhost_path', false) !== false) {
                runLocally('mv {{current_dir}}/{{reponame}}.conf ' . FileUtility::getUniqueFileName(parse('{{vhost_path}}/{{reponame}}.conf')));
            } else {
                writeln(parse('You did not set "VHOST_PATH" env var so we do not know where to copy generated vhosts. This is why vhost was stored in {{current_dir}} so you can move it manually.'));
            }
        } else {
            throw new \Exception(parse('There is already vhost with the name {{reponame}}.conf at directory {{vhost_path}}. This is why vhost was stored in {{current_dir}} so you can move it manually.'));
        }
    } else {
        throw new \Exception('"public_urls" var was not set for server. Vhost can not be generated without it.');
    }
})->desc('Create vhost and copy to env path set in "VHOST_PATH"');
