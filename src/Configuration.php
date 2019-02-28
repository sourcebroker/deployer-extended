<?php

namespace SourceBroker\DeployerExtended;

use Deployer\Deployer;

class Configuration
{

    public static function getServer($instance)
    {
        try {
            $environment = Deployer::get()->environments[$instance];
            $server = Deployer::get()->servers[$instance]->getConfiguration();
        } catch (\RuntimeException $e) {
            $servers = '';
            $i = 1;
            foreach (Deployer::get()->environments as $key => $server) {
                $servers .= "\n" . $i++ . '. ' . $key;
            }
            throw new \RuntimeException('Name of instance "' . $instance . '" is not on the server list:' .
                $servers . "\n" . 'Please check case sensitive.', 1500717628491);
        }

        return [
            'server' => $environment->get('server'),
            'deploy_path' => $environment->get('deploy_path'),
            'public_urls' => $environment->get('public_urls'),
            'user' => $server->getUser(),
        ];
    }

}
