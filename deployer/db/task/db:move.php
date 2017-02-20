<?php

namespace Deployer;

task('db:move', function () {

    try {
        if (input()->hasArgument('targetStage')) {
            $targetInstanceName = input()->getArgument('targetStage');
            if ($targetInstanceName == null) {
                throw new \RuntimeException(
                    "You must set the target instance the database will be copied to as second parameter."
                );
            }
            if ($targetInstanceName == 'live') {
                throw new \RuntimeException(
                    "FORBIDDEN: For security its forbidden to move database to live instance!"
                );
            }
            if ($targetInstanceName == 'local') {
                throw new \RuntimeException(
                    "FORBIDDEN: For synchro local database use: \ndep db:pull live"
                );
            }
            $targetInstanceEnv = Deployer::get()->environments[$targetInstanceName];
        } else {
            throw new \RuntimeException(
                "The targetStage is not set as parameter."
            );
        }

        $sourceInstance = get('server')['name'];

        // export fresh database dump to remote database dump storage
        $command = Task\Context::get()->getEnvironment()->parse("cd {{deploy_path}}/current && {{bin/php}} deployer.phar -q db:export " . $sourceInstance);
        $databaseDumpResult = run($command);
        $databaseDumpResponse = json_decode(trim($databaseDumpResult->toString()), true);
        if ($databaseDumpResponse == null) {
            throw new \RuntimeException(
                "db:export failed on " . $sourceInstance . ". The database dumpCode is null. Try to call: \n" .
                $command . "\n" .
                "on " . $sourceInstance . " instance. \n" .
                "Export task returned: " . $databaseDumpResult->toString() . "\n" .
                "One of the reason can be PHP notices or warnings added to output."
            );
        }

        // download the latest database dumps from remote database dump storage
        runLocally("{{deployer_exec}} db:download " . $sourceInstance . " --dumpcode=" . $databaseDumpResponse['dumpCode'], 0);

        // processing downloaded dump files
        runLocally("{{deployer_exec}} db:process_dump " . $sourceInstance . " --dumpcode=" . $databaseDumpResponse['dumpCode'], 0);

        if (get('instance') == $targetInstanceName) {
            // import the latest database dumps from local database dump storage
            runLocally("{{deployer_exec}} db:import " . $sourceInstance . " --dumpcode=" . $databaseDumpResponse['dumpCode'], 0);

        } else {
            // import the latest database dumps from local database dump storage
            runLocally("{{deployer_exec}} db:upload " . $targetInstanceName . " --dumpcode=" . $databaseDumpResponse['dumpCode'], 0);

            // import the latest database dumps from local database dump storage
            run("cd " . $targetInstanceEnv->get('deploy_path') . "/current && " . $targetInstanceEnv->get('bin/php') .
                " deployer.phar -q db:import " . $targetInstanceName . " --dumpcode=" . $databaseDumpResponse['dumpCode']);
        }
    } catch (\RuntimeException $e) {
        throw $e;
    }

})->desc('Synchronize database between instances.');