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

        // Set active_path so the task can be used before or after "symlink" task or standalone.
        set('active_path', get('deploy_path') . '/' . (test('[ -L {{deploy_path}}/release ]') ? 'release' : 'current'));


        foreach ($removeRecursiveAtomicDirectories as $removeRecursiveAtomicDirectory) {
            set('removeRecursiveAtomicDirectory', rtrim($removeRecursiveAtomicDirectory, '/'));
            if (run('if [ -d "{{active_path}}/{{removeRecursiveAtomicDirectory}}" ] ; then echo true; fi')->toBool()) {
                run('cd "{{active_path}}" && mv "{{removeRecursiveAtomicDirectory}}" "{{removeRecursiveAtomicDirectory}}{{random}}"');
                run('cd "{{active_path}}" && rm -rf "{{removeRecursiveAtomicDirectory}}{{random}}"');
            }
        }
    }
})->desc('Remove directory recursively - atomic. First rename then remove.');
