<?php

// run only if called from deployer.phar context
if (function_exists('\Deployer\set')) {

    require_once 'recipe/common.php';

    if ($_SERVER['_'] == $_SERVER['PHP_SELF']) {
        \Deployer\set('deployer_exec', $_SERVER['_']);
    } else {
        \Deployer\set('deployer_exec', $_SERVER['_'] . $_SERVER['PHP_SELF']);
    }

// TODO - find more reliable way to set root of project
    $data = debug_backtrace();
    if (isset($data[3]) && isset($data[3]['file'])) {
        \Deployer\set('current_dir', dirname($data[3]['file']));
    }

    \SourceBroker\DeployerExtended\Utility\FileUtility::requireFilesFromDirectoryReqursively(__DIR__ . '/../deployer/');

}