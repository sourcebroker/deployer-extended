<?php

namespace Deployer;

/**
 * Remove directory recursively - atomic.
 *
 * First rename folder which is atomic operation and then remove.
 * Important when removing file based caches.
 */
task('file:remove_recursive_atomic', function () {

    if (!get('remove_recursive_atomic_directories', false)) {
        if (isVerbose()) {
            writeln('Variable remove_recursive_atomic_directories not set. No directories removed.');
        }
    } else {
        $removeRecursiveAtomicDirectories = get('remove_recursive_atomic_directories');

        set('random', str_replace('.', '', microtime(true)) . rand());

        // Set active_dir so the task can be used before or after "symlink" task or standalone.
        if (run('if [ -L {{deploy_path}}/release ] ; then echo true; fi')->toBool()) {
            set('active_dir', get('deploy_path') . '/release');
        } else {
            set('active_dir', get('deploy_path') . '/current');
        }

        foreach ($removeRecursiveAtomicDirectories as $removeRecursiveAtomicDirectory) {
            set('removeRecursiveAtomicDirectory', rtrim($removeRecursiveAtomicDirectory, '/'));
            if (run('if [ -d "{{active_dir}}/{{removeRecursiveAtomicDirectory}}" ] ; then echo true; fi')->toBool()) {
                run('cd "{{active_dir}}" && mv "{{removeRecursiveAtomicDirectory}}" "{{removeRecursiveAtomicDirectory}}{{random}}"');
                run('cd "{{active_dir}}" && rm -rf "{{removeRecursiveAtomicDirectory}}{{random}}"');
            }
        }
    }
})->desc('Remove directory recursively - atomic. First rename then remove.');
