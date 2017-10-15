
Changelog
---------

8.0.0
~~~~~

a) [TASK][!!!BREAKING] Remove default set('fetch_method', 'wget'); as it should have fallback in task itself.
b) [BUGFIX] Create lock file in buffer:start only when directory exists.

7.1.3
~~~~~

a) [DOCS] Update changelog.


7.1.2
~~~~~

a) [BUGFIX] In task "buffer:start" add -f (force) to mv command as on some linux distro its asking to overwrite by default.

7.1.1
~~~~~

a) [BUGFIX] Fix wrongly set default fetch_method for "php:clear_cache_http"
b) [DOC] Update changelog.

7.1.0
~~~~~

a) [FEATURE] Add curl as additional fetch_method.
b) [FEATURE] Add fallback when fetch_method is not set.
c) [BUGFIX] Change wget command to not store file at all. Previous settings causes wget to return error
   when there was no access to write on current folder. Right now there is no need to have write
   access.
d) [BUGFIX] Do fallback for get('public_urls', []) so right exeption is shown.
e) [FEATURE] Introduce {{bin/local/wget}}
f) [FEATURE] Introduce {{bin/local/curl}}
g) [DOC] Extend documentation about task properties.

7.0.0
~~~~~

a) [TASK] Add dependency to sourcebroker/deployer-loader
b) [TASK][!!!BREAKING] Remove SourceBroker\DeployerExtended\Loader.php in favour of using sourcebroker/deployer-loader
c) [TASK][!!!BREAKING] Remove SourceBroker\DeployerExtended\Utility\FileUtility->requireFilesFromDirectoryReqursively
   becase it was used only in SourceBroker\DeployerExtended\Loader.php

6.1.3
~~~~~

a) [BUGFIX] Fix problem when few request want to delete the same file in buffer tasks.
b) [TASK] Increase req for php to 5.6 as deployer does not work with php 5.4.

6.1.2
~~~~~

a) Fix missing changelog.

6.1.1
~~~~~

a) Fix hardcoded locker file name.
b) Docs update.

6.1.0
~~~~~

a) Add option to buffer:start to auto remove lock files after some time.
b) Add option "entrypoint_refresh"

6.0.0
~~~~~

a) Start entrypoints in task "buffer:start" and "buffer:stop" from deploy_path and not form web_path
b) Remove not used var "tmp_dir".

5.1.0
~~~~~

a) Rework of php:clear_cache_http. Look for old clear_cache file in previous release.

5.0.0
~~~~~

a) Remove autoload of recipes. From now an object of class Loader must be created that will load
   the recipes.

4.0.0
~~~~~

Tasks removed with replacement in other package: https://github.com/sourcebroker/deployer-extended-database

a) db:download
b) db:export
c) db:import
d) db:move
e) db:process
f) db:pull
g) db:truncate
h) db:upload

Tasks removed with replacement in other package: https://github.com/sourcebroker/deployer-extended-media

a) media:move
b) media:pull
c) media:push

3.0.0
~~~~~

Flatten structure of databases settings for database tasks.

Structure was:
::

 set(
       'db_databases',
       [
           ['database_foo' => [
                   'host' => '127.0.0.1',
                   'database' => 'foo',
                   'user' => 'foo',
                   'password' => 'foopass',
                  ]
           ],
           ['database_foo' => get('db_default')]
           ['database_bar' => [
                   'host' => '127.0.0.1',
                   'database' => 'bar',
                   'user' => 'bar',
                   'password' => 'barpass',
                  ],
           ],
           ['database_bar' => get('db_default')]
           ['database_bar' => '/aboslute/path/to/file/with/config_array.php']
       ]
   );

Should be now:
::

 set(
       'db_databases',
       [
           'database_foo' => [
               [
                   'host' => '127.0.0.1',
                   'database' => 'foo',
                   'user' => 'foo',
                   'password' => 'foopass',
               ],
               get('db_default'),
               '/aboslute/path/to/file/with/config_array.php'
           ],
           'database_bar' => [
               get('db_default'),
               '/aboslute/path/to/file/with/config_array.php'
           ],
       ]
   );

All of the arrays in each database defined by key will be merged.

2.0.0
~~~~~

Task renamed:

a) Rename deploy:composer_check_install to `deploy:check_composer_install`_
b) Rename cache:clearstatcache to `php:clear_cache_cli`_
c) Rename cache:frontendreset to `php:clear_cache_http`_
d) Rename deploy:vhosts to `config:vhost`_

Task splitted/renamed with no simple replacement:

a) file:remove_recursive_atomic - replaced by `file:rm2steps:1`_, `file:rm2steps:2`_
b) lock:create_lock_files - replaced by `buffer:start`_
c) lock:delete_lock_files - replaced by `buffer:stop`_
d) lock:overwrite_entry_point - replaced by `buffer:start`_

Task removed with no replacement:

a) file:copy_from_shared
b) file:copy_from_previous
c) git:check_status
d) lock:stop_if_http_status_200
