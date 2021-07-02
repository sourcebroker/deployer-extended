<?php

namespace Deployer;

// Read more on https://github.com/sourcebroker/deployer-extended#backup-files
task('file:backup', function () {

    if (!empty(get('argument_stage'))) {
        $activePath = get('deploy_path') . '/' . (test('[ -L {{deploy_path}}/release ]') ? 'release' : 'current');
        run('cd ' . $activePath . ' && {{bin/php}} {{bin/deployer}} file:backup');
        return;
    }

    $signCode = 'e1a803ffea27e6bf3f93bd601dc42077';

    $backupFiles = get('file_backup_packages');
    $fileBackupKeep = get('file_backup_keep', 5);

    if (empty($backupFiles)) {
        writeln('Warning: No backup performed! Nothing defined in "file_backup_packages"');
        return;
    }

    $searchRootPath = !empty($_ENV['IS_DDEV_PROJECT']) ? '.' : get('deploy_path') . '/' . (testLocally('[ -e {{deploy_path}}/current ]') ? 'current' : '');

    foreach ($backupFiles as $package => $filtersGroups) {
        if (empty($filtersGroups)) {
            writeln('Skiping package "' . $package .'" - empty filters.');
            continue;
        }

        $filtersGroups = array_map(function ($group) {
            return is_array($group)
                ? '\( '.  implode(' -and ', $group). ' \)'
                : $group;
        }, $filtersGroups);

        $filtersConcat = implode(' -or ', $filtersGroups);

        $backupName = date('Ymd_His') .'_'. $signCode;

        $command = <<<BASH
if [ -d "{$searchRootPath}" ]
then
    cd "{$searchRootPath}"
    [ -d "{{file_backup_path}}/{$package}" ] || mkdir -p "{{file_backup_path}}/{$package}"
    find . -type f -follow \( {$filtersConcat} \) -printf '%P\\n' | \
    tar -czf {{file_backup_path}}/{$package}/{$backupName}.tar.gz -T -
fi
BASH;

        writeln('Creating backup. Package: "' . $package .'" Backup signature: "'. $backupName .'"');
        runLocally($command);

        writeln('Clean old backups');
        $command = '
if [ -d '. escapeshellarg(get('file_backup_path') .'/'. $package) .' ]
then
    cd '. escapeshellarg(get('file_backup_path') .'/'. $package) .'
    ls -1t | grep '. $signCode .' | tail -n +'. ($fileBackupKeep + 1) .' | xargs rm -f
fi
';

        runLocally($command);
    }
})
->desc('Backup filtered files / directories');
