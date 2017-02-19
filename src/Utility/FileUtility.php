<?php

namespace SourceBroker\DeployerExtended\Utility;


/**
 * Class FileUtility
 * @package SourceBroker\DeployerExtended\Utility
 */
class FileUtility
{

    public static function normalizeFilename($filename)
    {
        return preg_replace("/[^a-zA-Z0-9_]+/", "", $filename);
    }


    public static function requireFilesFromDirectoryReqursively($absolutePath, $excludePattern = null)
    {
        if (is_dir($absolutePath)) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($absolutePath), \RecursiveIteratorIterator::SELF_FIRST);
            foreach ($iterator as $file) {
                /** @var $file \SplFileInfo */
                if ($file->isFile()) {
                    $excludeMatch = null;
                    if ($excludePattern != null) {
                        $excludeMatch = preg_match($excludePattern, $file->getFilename());
                    }
                    if ($file->getExtension() == 'php' && !$excludeMatch) {
                        require_once $file->getRealPath();
                    }
                }
            }
        }
    }
}