<?php

namespace Deployer;

// Read more on https://github.com/sourcebroker/deployer-extended#backup-files
task('file:backup', function () {
    $backupFiles = get('file_backup_packages');

    if ($backupFiles === null) {
        writeln('Warning: No backup performed! Nothing defined in "file_backup_packages"');
        return;
    }

    $searchRootPath = get('deploy_path') .'/'. (test('[ -e {{deploy_path}}/current ]') ? 'current' : '');

    foreach ($backupFiles as $package => $filtersGroups) {
        $filtersGroups = array_map(function ($group) {
            return is_array($group)
                ? '\( '.  implode(' -and ', $group). ' \)'
                : $group;
        }, $filtersGroups);

        $filtersConcat = implode(' -or ', $filtersGroups);

        $backupName = date('Ymd_His');

        $command = <<<BASH
cd {$searchRootPath}
[ -d "{{file_backup_path}}/{$package}" ] || mkdir -p "{{file_backup_path}}/{$package}"
find . -type f \( {$filtersConcat} \) -printf '%P\\n' | \
tar -czf {{file_backup_path}}/{$package}/{$backupName}.tar.gz -T -
BASH;

        writeln('Creating backup. Package: "' . $package .'" Backup signature: "'. $backupName .'"');
        run($command);
    }

})
->desc('Backup files - only files selected by filter will be backuped');
