<?php

namespace Deployer;

use Deployer\Type\Csv;
use Deployer\Exception\GracefulShutdownException;

// Read more on https://github.com/sourcebroker/deployer-extended#deploy-check-branch
task('deploy:check_branch', function () {
    if (test('[ -f .dep/releases.extended ]')) {
        cd('{{deploy_path}}');
        $branchToBeDeployed = get('branch', null);
        if (input()->hasOption('branch')) {
            $inputBranch = input()->getOption('branch');
            if (!empty($inputBranch)) {
                $branchToBeDeployed = $inputBranch;
            }
        }
        $csv = run('tail -n 1 .dep/releases.extended');
        if ($csv) {
            $metainfo = Csv::parse($csv)[0];
            if (isset($metainfo[2]) && isset($metainfo[3])) {
                $currentRemoteBranch = $metainfo[2];
                $userName = $metainfo[3];
                if ($currentRemoteBranch != $branchToBeDeployed) {
                    if (!askConfirmation(sprintf('On instance you deploy to there is currently branch "%s" deployed by "%s". ' .
                        'This branch is different than you trying to deploy "%s". Do you really want to continue?',
                        $currentRemoteBranch, $userName, $branchToBeDeployed), false)) {
                        throw new GracefulShutdownException('Process aborted.');
                    }
                }
            }
        }
    }
})->desc('Check if the branch we want to deploy is equal to the branch that has been already deployed');
