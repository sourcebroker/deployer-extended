
Changelog
---------

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
