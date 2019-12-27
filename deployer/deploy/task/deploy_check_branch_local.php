<?php

namespace Deployer;

use Deployer\Exception\GracefulShutdownException;

// Read more on https://github.com/sourcebroker/deployer-extended#deploy-check-branch-local
task('deploy:check_branch_local', function () {
    $branchCheck = true;
    if (input()->hasOption('tag')) {
        $tag = input()->getOption('tag');
        if (!empty($tag)) {
            $branchCheck = false;
        }
    }
    if (empty($tag) && input()->hasOption('revision')) {
        $revision = input()->getOption('revision');
        if (!empty($revision)) {
            $branchCheck = false;
        }
    }
    if ($branchCheck) {
        cd('{{deploy_path}}');
        $branchToBeDeployed = get('branch');
        if (get('branch_set_explicitly', true) && empty($branchToBeDeployed)) {
            writeln('No branch to deploy detected. Set the branch you want to deploy explicitly by setting ->set("branch", "master"); or by adding cli param "--branch="');
            throw new GracefulShutdownException('Process aborted.');
        }
        if (testLocally('[ -d .git ]')) {
            try {
                $branchCheckoutLocally = runLocally('git rev-parse --abbrev-ref HEAD');
            } catch (\Throwable $exception) {
                $branchCheckoutLocally = null;
            }
            if (!empty($branchCheckoutLocally) && $branchCheckoutLocally != $branchToBeDeployed) {
                if (!askConfirmation(sprintf('On the current instance you are checkout to branch "%s" but you want to deploy branch "%s". ' .
                    'The deploy.php files on both branches can be diffrent and that can influence the deploy process. Its not advisable. Do you really want to continue?',
                    $branchCheckoutLocally, $branchToBeDeployed), false)) {
                    throw new GracefulShutdownException('Process aborted.');
                }
            }
        }
    }
})->desc('Check if locally checkout branch is equal to what branch is going to be deployed');
