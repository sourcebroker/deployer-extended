<?php

namespace Deployer;

use Deployer\Exception\GracefulShutdownException;

// Read more on https://github.com/sourcebroker/deployer-extended#deploy-check-branch-local
task('deploy:check_branch_local', function () {
    cd('{{deploy_path}}');
    if (testLocally('[ -d .git ]')) {
        try {
            $branchCheckoutLocally = runLocally('git rev-parse --abbrev-ref HEAD');
        } catch (\Throwable $exception) {
            $branchCheckoutLocally = null;
        }
        $branchToBeDeployed = get('branch', null);
        if (input()->hasOption('branch')) {
            $inputBranch = input()->getOption('branch');
            if (!empty($inputBranch)) {
                $branchToBeDeployed = $inputBranch;
            }
        }
        if ($branchCheckoutLocally != $branchToBeDeployed) {
            if (!askConfirmation(sprintf('On the current instance you are checkout to branch "%s" but you want to deploy branch "%s". ' .
                'The deploy.php files on both branches can be diffrent and that can influence the deploy process. Its not advisable. Do you really want to continue?',
                $branchCheckoutLocally, $branchToBeDeployed), false)) {
                throw new GracefulShutdownException('Process aborted.');
            }
        }
    }
})->desc('Check if locally checkout branch is equal to what branch is going to be deployed');
