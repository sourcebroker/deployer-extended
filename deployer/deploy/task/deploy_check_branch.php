<?php

namespace Deployer;

use Deployer\Exception\GracefulShutdownException;

// Read more on https://github.com/sourcebroker/deployer-extended#deploy-check-branch
task('deploy:check_branch', function () {
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
        if (empty($branchToBeDeployed) && get('branch_set_explicitly', true)) {
            writeln('No branch to deploy detected. Set the branch you want to deploy explicitly by setting ->set("branch", "master"); or by adding cli param "--branch="');
            throw new GracefulShutdownException('Process aborted.');
        }
        $releasesLog = get('releases_log');
        if (test("[ -e {{current_path}} ]")) {
            $currentRelease = basename(run('readlink {{current_path}}'));
            foreach ($releasesLog as $metaInfo) {
                if ($metaInfo['release_name'] === $currentRelease) {
                    $currentRemoteBranch = $metaInfo['target'];
                    if (($currentRemoteBranch !== $branchToBeDeployed) && !askConfirmation(sprintf(
                            'On host "%s" there is currently branch "%s" deployed by "%s" on %s. ' .
                            'You are trying to deploy now branch "%s". Do you really want to continue?',
                            get('argument_host'),
                            $currentRemoteBranch,
                            $metaInfo['user'],
                            $metaInfo['created_at'],
                            $branchToBeDeployed
                        ), false)) {
                        throw new GracefulShutdownException('Process aborted.');
                    }
                    break;
                }
            }
        }
    }
})->desc('Check if the branch we want to deploy is equal to the branch that has been already deployed');
