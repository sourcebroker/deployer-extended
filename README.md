[![StyleCI](https://styleci.io/repos/82486796/shield?branch=master)](https://styleci.io/repos/82486796)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sourcebroker/deployer-extended/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sourcebroker/deployer-extended/?branch=master)
&nbsp;
<a href="https://packagist.org/packages/sourcebroker/deployer-extended"><img src="https://img.shields.io/badge/license-MIT-blue.svg?style=flat" alt="License"></a>
<a href="https://packagist.org/packages/sourcebroker/deployer-extended"><img src="http://img.shields.io/packagist/v/sourcebroker/deployer-extended.svg?style=flat" alt="Latest Stable Version"></a>

## What does it do?

The package provides additional tasks for deployer.org.

## Documentation

### Cache Tasks

General cache task that can be used for all PHP applications.

#### cache:clearstatcache

This task clear the stat cache for real file pathes (http://php.net/manual/en/function.clearstatcache.php). 
Additionally it clears opcache and eaccelaeator cache for CLI context. 

#### cache:frontendreset

This task clear the clears opcache and eaccelaeator cache for WEB context. 


### Database Tasks

#### db:download

Download database from target instance do current instance. 
There is required option --dumpcode to be passed.

Example call:
      
      dep db:download live --dumpcode=0772a8d396911951022db5ea385535f6


__Notice!:__ Usually you do not need to run this command as its part of db:pull and db:move command.

#### db:export

Export database to database storage on current instance. The database will be stored on current instance database storage
as two separate files. One with tables structure. The second with data only.

2017-02-26_14:56:08#server:live#dbcode:database_default#type:data#dumpcode:362d7ca0ff065f489c9b79d0a73720f5.sql
2017-02-26_14:56:08#server:live#dbcode:database_default#type:structure#dumpcode:362d7ca0ff065f489c9b79d0a73720f5.sql

Example call:

      dep db:export

__Notice!:__ Usually you do not need to run this command as its part of db:pull and db:move command.

#### db:import

Import database from current instance database storage. 
There is required option --dumpcode to be passed.
    
      dep db:import --dumpcode=0772a8d396911951022db5ea385535f66
      
__Notice!:__ Usually you do not need to run this command as its part of db:pull and db:move command.
      
#### db:move

This command allows you to move database between instances.
In the background it runs several commands to accomplish this task.

Example call when you are on your local instance can be:

    dep db:pull live
        
It will do following:        
1) First it runs db:export task on target instance and get the "dumpcode" as return to use it in next commands.
2) Then it runs db:download (with "dumpcode" value from first task).
3) Then it runs db:process_dump (with "dumpcode" value from first task).
4) Then it runs db:import (with "dumpcode" value from first task).

#### db:process_dump

This command will run replace on the sql dump file. Its sometimes needed to remove or replace some strings directly on sql file 
before importing.
 
There is required option --dumpcode to be passed.
    
      dep db:process_dump --dumpcode=0772a8d396911951022db5ea385535f66
      
__Notice!:__ Usually you do not need to run this command as its part of db:pull and db:move command.

#### db:pull

This command allows you to download database from target instance to local instance.
In the background it runs several commands to accomplish this task.

Example call when you are on your local instance can be:

    dep db:pull live
        
It will do following:        
1) First it runs db:export task on target instance and get the "dumpcode" as return to use it in next commands.
2) Then it runs db:download (with "dumpcode" value from first task).
3) Then it runs db:process_dump (with "dumpcode" value from first task).
4) Then it runs db:import (with "dumpcode" value from first task).

#### db:truncate

This command allows you to truncate database tables.

Example call can be:

    dep db:truncate

#### db:upload

This command will upload the sql dump file to target instance. 
There is required option --dumpcode to be passed.
    
      dep db:pull --dumpcode=0772a8d396911951022db5ea385535f66
      
__Notice!:__ Usually you do not need to run this command as its part of db:pull and db:move command.

### Deploy Tasks

#### deploy:check_lock

#### deploy:composer_install_check

#### deployer:download

### Files Tasks

#### file:copy_from_previous

#### file:copy_from_shared

### Git Tasks

#### git

### Lock Tasks

#### lock:create_lock_files

#### lock:delete_lock_files

#### lock:overwrite_entry_point

#### lock:stop_if_http_status_200

### Media Tasks

#### media:push

#### media:move

#### media:pull

## Known problems
None.

## To-Do list
None.