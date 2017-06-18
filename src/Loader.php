<?php

namespace SourceBroker\DeployerExtended;

class Loader
{
    public function __construct()
    {
        \SourceBroker\DeployerExtended\Utility\FileUtility::requireFilesFromDirectoryReqursively(
            dirname((new \ReflectionClass('\SourceBroker\DeployerExtended\Loader'))->getFileName()) . '/../deployer/'
        );
    }
}
