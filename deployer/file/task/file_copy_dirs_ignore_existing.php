<?php

namespace Deployer;

task('file:copy_dirs_ignore_existing', function () {
    if (has('previous_release')) {
        foreach (get('copy_dirs_ignore_existing') as $dir) {
            $previousReleasePath = get('previous_release');
            $releasePath = get('release_path');
            $script = <<<BASH
for FOLDER in {$previousReleasePath}/{$dir}/* ; do
     if [ -d \${FOLDER} ]; then
      FOLDER_NAME=`basename \${FOLDER}`
        if [ ! -d {$releasePath}/{$dir}/\${FOLDER_NAME} ]; then
          mkdir -p {$releasePath}/{$dir}/\${FOLDER_NAME}
          rsync -av {$previousReleasePath}/{$dir}/\${FOLDER_NAME}/ {$releasePath}/{$dir}/\${FOLDER_NAME}
        fi
     fi
done
BASH;
            run($script);
        }
    }
})
    ->desc('Copy directories from previous release except for those folders which already exists.')->hidden();
