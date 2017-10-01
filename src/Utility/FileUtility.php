<?php

namespace SourceBroker\DeployerExtended\Utility;

/**
 * Class FileUtility
 * @package SourceBroker\DeployerExtended\Utility
 */
class FileUtility
{
    /**
     * @param $filename
     * @return mixed
     */
    public static function normalizeFilename($filename)
    {
        return preg_replace("/[^a-zA-Z0-9_]+/", "", $filename);
    }

    /**
     * @param $folder
     * @return string
     */
    public static function normalizeFolder($folder)
    {
        return rtrim($folder, '/');
    }

    /**
     * @param $absolutePath string Absolute path to file
     * @return string
     */
    public static function getUniqueFileName($absolutePath)
    {
        $pathParts = pathinfo($absolutePath);
        $i = 0;
        while (file_exists($absolutePath) && $i < 100) {
            $absolutePath = $pathParts['dirname'] . '/' . $pathParts['filename'] . '_' . $i . '.' . $pathParts['extension'];
            $i++;
        }
        return $absolutePath;
    }
}
