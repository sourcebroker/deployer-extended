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

#### db:export

#### db:import

#### db:move

#### db:process_dump

#### db:pull

#### db:truncate_table

#### db:truncate_table_remote

#### db:upload

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