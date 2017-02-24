<?php

namespace Deployer;

/*
 * Based on https://github.com/deployphp/recipes/blob/master/recipes/rsync.php
 */
set('media-default',
    [
        'exclude' => [],
        'exclude-case-insensitive' => [
            '*.mp4',
            '*.zip',
            '*.pdf',
            '*.exe',
            '*.doc',
            '*.docx',
            '*.pptx',
            '*.ppt',
            '*.xls',
            '*.xlsx',
            '*.xlsm',
            '*.tiff',
            '*.tif',
            '*.potx',
            '*.mpg',
            '*.mp3',
            '*.avi',
            '*.wmv',
            '*.flv',
            '*.eps',
            '*.ai',
            '*.mov',
        ],
        'exclude-file' => false,
        'include' => [],
        'include-file' => false,
        'filter' => [],
        'filter-file' => false,
        'filter-perdir' => false,
        'flags' => 'rz',
        'options' => ['copy-links', 'keep-dirlinks', 'safe-links'],
        'timeout' => 0,
    ]);


set('media_rsync_dest', getcwd());

set('media_rsync_excludes', function () {
    $config = array_merge_recursive(get('media-default'), get('media'));

    $excludes = isset($config['exclude']) ? $config['exclude'] : [];
    $excludesRsync = '';
    foreach ($excludes as $exclude) {
        $excludesRsync .= ' --exclude=' . escapeshellarg($exclude);
    }

    $excludeFile = isset($config['exclude-file']) ? $config['exclude-file'] : false;
    if (!empty($excludeFile) && file_exists($excludeFile) && is_file($excludeFile) && is_readable($excludeFile)) {
        $excludesRsync .= ' --exclude-from=' . escapeshellarg($excludeFile);
    }

    // rsync does not have case insensitive flag
    $excludesCaseInsensitive = isset($config['exclude-case-insensitive']) ? $config['exclude-case-insensitive'] : [];
    foreach ($excludesCaseInsensitive as $excludeCaseInsensitive) {
        $excludePatternCaseInsensitive = '';
        $excludePatternNormalized = strtolower($excludeCaseInsensitive);
        foreach (str_split($excludePatternNormalized) as $letter) {
            if (strtoupper($letter) == $letter) {
                $excludePatternCaseInsensitive .= $letter;
            } else {
                $excludePatternCaseInsensitive .= '[' . $letter . mb_strtoupper($letter) . ']';
            }
        }
        $excludesRsync .= " --exclude '" . $excludePatternCaseInsensitive . "'";
    }

    return $excludesRsync;
});

set('media_rsync_includes', function () {
    $config = array_merge_recursive(get('media-default'), get('media'));

    $includes = isset($config['include']) ? $config['include'] : [];
    $includesRsync = '';
    foreach ($includes as $include) {
        $includesRsync .= ' --include=' . escapeshellarg($include);
    }

    $includeFile = isset($config['include-file']) ? $config['include-file'] : false;
    if (!empty($includeFile) && file_exists($includeFile) && is_file($includeFile) && is_readable($includeFile)) {
        $includesRsync .= ' --include-from=' . escapeshellarg($includeFile);
    }
    return $includesRsync;
});

set('media_rsync_filter', function () {
    $config = array_merge_recursive(get('media-default'), get('media'));

    $filters = isset($config['filter']) ? $config['filter'] : [];
    $filtersRsync = '';
    foreach ($filters as $filter) {
        $filtersRsync .= " --filter='$filter'";
    }

    $filterFile = isset($config['filter-file']) ? $config['filter-file'] : false;
    if (!empty($filterFile)) {
        $filtersRsync .= " --filter='merge $filterFile'";
    }

    $filterPerDir = isset($config['filter-perdir']) ? $config['filter-file'] : false;
    if (!empty($filterPerDir)) {
        $filtersRsync .= " --filter='dir-merge $filterFile'";
    }

    return $filtersRsync;
});

set('media_rsync_options', function () {
    $config = array_merge_recursive(get('media-default'), get('media'));

    $options = isset($config['options']) ? $config['options'] : [];
    $optionsRsync = [];
    foreach ($options as $option) {
        $optionsRsync[] = "--$option";
    }
    return implode(' ', $optionsRsync);
});
