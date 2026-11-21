<?php

namespace Deployer;

task('file:upload_build', function () {
    $clearPaths = get('clear_paths') ?? [];
    $sharedFiles = get('shared_files') ?? [];
    $sharedDirs = get('shared_dirs') ?? [];
    $allPaths = [...$clearPaths, ...$sharedFiles, ...$sharedDirs];
    $exclusions = array_map(static fn($item) => '/' . $item, $allPaths);

    $excludeOptions = [];
    foreach ($exclusions as $exclusion) {
        $excludeOptions[] = '--exclude';
        $excludeOptions[] = $exclusion;
    }
    $chmod = (string)get('file_upload_build_chmod', '');
    $chmodOptions = $chmod !== '' ? ['--chmod=' . $chmod] : [];
    upload('./', get('release_path'), ['options' => array_merge($excludeOptions, $chmodOptions, ['--quiet'])]);
})->desc('Uploading build to server')->hidden();
