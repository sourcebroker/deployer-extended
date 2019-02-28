<?php

namespace SourceBroker\DeployerExtended;

use Deployer\Deployer;

class Configuration
{

    public static function getEnvironment($name)
    {
        try {
            $environment = Deployer::get()->environments[$name];
        } catch (\RuntimeException $e) {
            $environments = '';
            $i = 1;
            foreach (Deployer::get()->environments as $key => $server) {
                $environments .= "\n" . $i++ . '. ' . $key;
            }
            throw new \RuntimeException('Name of instance "' . $name . '" is not on the environments list:' .
                $environments . "\n" . 'Please check case sensitive.', 1500717628491);
        }

        return $environment;
    }

    public static function getServer($name)
    {
        try {
            $server = Deployer::get()->servers[$name];
        } catch (\RuntimeException $e) {
            $servers = '';
            $i = 1;
            foreach (Deployer::get()->servers as $key => $server) {
                $servers .= "\n" . $i++ . '. ' . $key;
            }
            throw new \RuntimeException('Name of instance "' . $name . '" is not on the environments list:' .
                $servers . "\n" . 'Please check case sensitive.', 1500717628491);
        }

        return $server;
    }

}
