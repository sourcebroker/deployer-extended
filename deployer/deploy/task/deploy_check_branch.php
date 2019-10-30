<?php

namespace Deployer;

use Deployer\Type\Csv;

// Read more on https://github.com/sourcebroker/deployer-extended#deploy-check-branch
task('deploy:check_branch', function () {
    cd('{{deploy_path}}');
    if (run('if [ -f .dep/branch ]; then echo "true"; fi')->toBool()) {
        $csv = run('tail -n 1 .dep/branch');
        if ($csv) {
            $metainfo = Csv::parse($csv)[0];
            if (isset($metainfo[2]) && isset($metainfo[3])) {
                $currentBranch = $metainfo[2];
                $userName = $metainfo[3];
                $branch = get('branch') ?: 'master';
                if ($currentBranch != $branch) {
                    if (!askConfirmation(sprintf('On instance you deploy to there is currently branch "%s" deployed by "%s". 
                    This branch is different than you trying to deploy "%s". Do you really want to continue?',
                        $currentBranch, $userName, $branch), false)) {
                        throw new \RuntimeException('Process aborted.');
                    }
                }
            }
        }
    }
})->desc('Check if branch deployed to instance is the same as the one which is being deployed');
