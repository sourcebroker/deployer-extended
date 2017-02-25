<?php

namespace Deployer;

task('git:check_status', function () {
    // check if directory exists and is under GIT control
    $result = run('if [ -d {{current}} ] && [ -d {{current}}/.git ]; then echo true; fi');

    $actions = [
        "??" => "Untracked files",
        "M" => "Modified files",
        "D" => "Deleted files",
    ];

    if (!$result->toBool()) {
        if (isVerbose()) {
            writeln('<comment>No GIT repository found - skipping.</comment>');
        }
        return;
    }

    $clearPaths = [];
    foreach (get('clear_paths') as $path) {
        $clearPaths[str_replace('release/', '', $path)] = 1;
    }

    $output = run('cd {{current}} && git status --porcelain');
    $result = [];
    $counter = 0;
    foreach ($output->toArray() as $value) {
        list($action, $file) = explode(' ', trim($value));
        if (!isset($clearPaths[$file])) {
            $result[$action][] = $file;
            $counter++;
        }
    }

    if (count($result)) {
        writeln('<comment>> You have on server ' . $counter . ' uncommited file(s).</comment>');
        output()->writeln("\033[1;30m< \033[0m", 1);
        foreach ($result as $action => $files) {
            writeln("<fg=red>> " . (isset($actions[$action]) ? $actions[$action] : "Changed files") . " (" . $action . ")</fg=red>");
            foreach ($files as $file) {
                writeln("<fg=magenta>    - " . $file . "</fg=magenta>");
            }
        }
        output()->writeln("\033[1;30m< \033[0m", 1);


        do {
            $answer = ask("Do you want continue (y/n)?", 'n');
        } while ($answer != 'y' && $answer != 'n');

        if ($answer == 'n') {
            writeln('<comment>> Stopped deployment.</comment>');
            output()->writeln("\033[1;30m< \033[0m", 1);
            die;
        }
    }
}
)->desc('Check git status on remote server');
