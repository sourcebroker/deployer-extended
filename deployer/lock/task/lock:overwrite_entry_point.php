<?php

namespace Deployer;

task('lock:overwrite_entry_point', function () {
    $content = run('cd {{release_path}} && [ -f {{web_path}}{{deploy_lock_inject_filename}} ] && cat {{web_path}}{{deploy_lock_inject_filename}} || echo ""');
    if ($content) {
        $needle = get('deploy_lock_needle');
        $phpCode = <<<EOT

if (file_exists(__DIR__ . '/index_buffer.php')) {
    include __DIR__ . '/index_buffer.php';
}

EOT;
        $pos = strpos($content, $needle);
        if ($pos !== false) {
            $content = substr_replace($content, $needle . $phpCode, $pos, strlen($needle));
            $path = get('temp_dir') . md5(get('deploy_lock_inject_filename') . get('random'));
            file_put_contents($path, $content);
            run('cd {{release_path}} && rm {{web_path}}{{deploy_lock_inject_filename}}');
            upload($path, "{{release_path}}/{{web_path}}{{deploy_lock_inject_filename}}");
            @unlink($path);
        }
    }
})->desc('Make symlink for file which do index_buffer inclusion');
