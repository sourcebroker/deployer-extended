deployer-extended
=================

.. image:: https://styleci.io/repos/82486796/shield?branch=master
   :target: https://styleci.io/repos/82486796

.. image:: https://scrutinizer-ci.com/g/sourcebroker/deployer-extended/badges/quality-score.png?b=master
   :target: https://scrutinizer-ci.com/g/sourcebroker/deployer-extended/?branch=master

.. image:: http://img.shields.io/packagist/v/sourcebroker/deployer-extended.svg?style=flat
   :target: https://packagist.org/packages/sourcebroker/deployer-extended

.. image:: https://img.shields.io/badge/license-MIT-blue.svg?style=flat
   :target: https://packagist.org/packages/sourcebroker/deployer-extended

|

.. contents:: :local:

What does it do?
----------------

The package provides additional tasks for deployer (deployer.org).

How this can be useful for me?
------------------------------

The most important and useful functionality is moving databases between instances,
moving media between instances and buffering requests to all application entrypoints
during "just before" and "just after" release switch. There is also few other little
gems that can be useful during deployment like clearing frontend cache of apache/nginx,
clearing php cli cache, checking if composer install is needed before making deploy,
generating vhost etc. Look for documentation for all available tasks.

Installation
------------
::

   composer require sourcebroker/deployer-extended

For best experience look also for corensponding framework packages that depends on
sourcebroker/deployer-extended:

For now the only available is:
1) sourcebroker/deployer-extended-typo3


Task's documentation
--------------------

buffer
~~~~~~

buffer:start
++++++++++++

Starts buffering requests to application entrypoints. Application entrypoints means here any php file that
can handle Apache request or handle cli calls. For most good frameworks there is only one or two entrypoints.

The request are buffered but at the same time if you set special http header (by default HTTP_X_DEPLOYER_DEPLOYMENT)
with special value you will be able to make regular request. This can be very handy to check if the application
is working at all after switch (symliking to current) and to warm up some caches.

When you run `buffer:stop`_ all the waiting requests will hit the https server (or cli entrypoint).

The entrypoints are taken from variable "buffer_config" which is array of entrypoints configurations.

Options:

- | **entrypoint_filename**
  | *required:* yes
  |
  | The filename that will be overwritten with "entrypoint_inject" php code. If entrypoint is inside folder then
    write it with this folder like: 'entrypoint_filename' => 'typo3/index.php'

  |
- | **entrypoint_needle**
  | *required:* no
  | *default value:* <?php
  |
  | A "needle" in "entrypoint_filename" after which the php code from "entrypoint_inject" will be injected.
  |

- | **entrypoint_inject**
  | *required:* no
  |
  | A php code that actually do the buffering.
  | The default code with already prefilled variables (random, locker_filename):
  ::

       isset($_SERVER['HTTP_X_DEPLOYER_DEPLOYMENT']) && $_SERVER['HTTP_X_DEPLOYER_DEPLOYMENT'] == '823094823094' ? $deployerExtendedEnableBufferLock = false : $deployerExtendedEnableBufferLock = true;
       isset($_ENV['DEPLOYER_DEPLOYMENT']) && $_ENV['DEPLOYER_DEPLOYMENT'] == '823094823094' ? $deployerExtendedEnableBufferLock = false: $deployerExtendedEnableBufferLock = true;
       while (file_exists(__DIR__ . 'buffer.lock') && $deployerExtendedEnableBufferLock) {
         usleep(200000);
         clearstatcache(true, __DIR__ . '/buffer.lock');
       }


- | **locker_filename**
  | *required:* no
  | *default value* buffer.lock
  |
  | When file with name "buffer.lock" exists the reqests are buffered. The task `buffer:stop`_ just removes
    the "buffer.lock" files without removing the "entrypoint_inject" code.
  |

The simplest configuration example:
::

   set('buffer_config', [
           'index.php' => [
               'entrypoint_filename' => 'index.php',
           ]
       ]
   );

More entrypoints example. An example for CMS TYPO3 8.7 LTS:
::

   set('buffer_config', [
           'index.php' => [
               'entrypoint_filename' => 'index.php', // frontend
           ]
           'typo3/index.php' => [
               'entrypoint_filename' => 'typo3/index.php', // backend
           ],
           'typo3/cli_dispatch.phpsh' => [
               'entrypoint_filename' => 'typo3/cli_dispatch.phpsh', // cli
           ]
       ]
   );

More configuration options examples:
::

   set('buffer_config', [
           'index.php' => [
               'entrypoint_filename' => 'index.php',
               'entrypoint_needle' => '// inject php code after this comment',
               'locker_filename' => 'deployment.lock',
               'entrypoint_inject' => 'while (file_exists(__DIR__ . "deployment.lock")){' . "\n"
                                      . 'usleep(200000);' . "\n"
                                      . 'clearstatcache(true, __DIR__ . "/buffer.lock")' . "\n"
                                      . '}'
           ]
       ]
   );


buffer:stop
+++++++++++

Stop buffering requests to application entrypoints. It deletes "buffer.lock" files.

config
~~~~~~

config:vhost
++++++++++++

Documentation to do.

db
~~
This tasks allows you to make database operation on current instance and between instances.
The most useful is ability to pull database from remote instance to current instance: `dep db:pull live`
or to move database between remote instances, eg: `dep db:move live dev`

Options:

- | **caching_tables**
  | *default value:* null
  |
  | Tables that will be truncated with task `db:truncate`_. Usually it should be some caching tables that
    should be truncated while deployment.

  |
- | **ignore_tables_out**
  | *default value:* null
  |
  | Tables that will be ignored while pulling database from target instance with task `db:pull`_

  |
- | **post_sql_in**
  | *default value:* null
  |
  | SQL that will be executed after importing database on current instance.

|
There is support to synchronise more than one database. Below and example for two database config.
All of the arrays in each database defined by key will be merged.
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
               get('db_default')
           ],
           'database_bar' => [
               [
                   'host' => '127.0.0.1',
                   'database' => 'bar',
                   'user' => 'bar',
                   'password' => 'barpass',
               ],
               get('db_default'),
               get('current_dir') . '/path/to/file/with/config_array.php'
           ],
       ]
   );

Example configuration for TYPO3:

::

   set('db_default', [
       'caching_tables' => [
           'cf_.*'
       ],
       'ignore_tables_out' => [
           'cf_.*',
           'cache_.*',
           'be_sessions',
           'sys_history',
           'sys_file_processedfile',
           'sys_log',
           'sys_refindex',
           'tx_devlog',
           'tx_extensionmanager_domain_model_extension',
           'tx_realurl_chashcache',
           'tx_realurl_errorlog',
           'tx_realurl_pathcache',
           'tx_realurl_uniqalias',
           'tx_realurl_urldecodecache',
           'tx_realurl_urlencodecache',
           'tx_powermail_domain_model_mails',
           'tx_powermail_domain_model_answers',
           'tx_solr_.*',
           'tx_crawler_queue',
           'tx_crawler_process',
       ],
       'post_sql_in' => ''
   ]);

db:download
+++++++++++

Download database from target instance to current instance.
There is required option --dumpcode to be passed.

**Example**
::

   dep db:download live --dumpcode=0772a8d396911951022db5ea385535f6

db:export
+++++++++

Export database to database storage on current instance. The database will be stored in two separate files.
One with tables structure. The second with data only. This tasks return json structure with dumpcode to
be used in other tasks.

**Example**

Example task call:
::

   dep db:export

Example output files:
::

   2017-02-26_14:56:08#server:live#dbcode:database_default#type:data#dumpcode:362d7ca0ff065f489c9b79d0a73720f5.sql
   2017-02-26_14:56:08#server:live#dbcode:database_default#type:structure#dumpcode:362d7ca0ff065f489c9b79d0a73720f5.sql

db:import
+++++++++

Import database from current instance database storage. There is required option --dumpcode to be passed.

**Example**
::

   dep db:import --dumpcode=0772a8d396911951022db5ea385535f66

db:move
+++++++

This command allows you to move database between instances.
In the background it runs several other tasks to accomplish this.

Here is the list of tasks that will be done afer "db:move":

1) First it runs `db:export`_ task on target instance and get the "dumpcode" as return to use it in next commands.
2) Then it runs `db:download`_ on current instance (with "dumpcode" value from first task).
3) Then it runs `db:process`_ on current instance (with "dumpcode" value from first task).
4) Then it runs `db:upload`_ on current instance (with "dumpcode" value from first task).
5) Then it runs `db:import`_ on target instance (with "dumpcode" value from first task).


**Example**

Example call when you are on your local instance can be:
::

   dep db:move live dev

db:process
++++++++++

This command will run some defined commands on pure sql file as its sometimes needed to remove or replace some strings
directly on sql file before importing. There is required option --dumpcode to be passed.

**Example**
::

   dep db:process --dumpcode=0772a8d396911951022db5ea385535f66


db:pull
+++++++

This command allows you to pull database from target instance to current instance.
In the background it runs several other tasks to accomplish this.

Here is the list of tasks that will be done afer "db:pull":

1) First it runs `db:export`_ task on target instance and get the "dumpcode" as return to use it in next commands.
2) Then it runs `db:download`_ on current instance (with "dumpcode" value from first task).
3) Then it runs `db:process`_ on current instance (with "dumpcode" value from first task).
4) Then it runs `db:import`_ on current instance (with "dumpcode" value from first task).

**Example**
::

   dep db:pull live

db:truncate
+++++++++++

This command allows you to truncate database tables defined in database config var "caching_tables"

**Example**
::

   dep db:truncate --dumpcode=0772a8d396911951022db5ea385535f6


db:upload
+++++++++

This command uploads the sql dump file to target instance.
There is required option --dumpcode to be passed.

**Example**

Upload database with dumpcode 0772a8d396911951022db5ea385535f6 to live instance
and store it on database storage folder.

::

   dep db:upload live --dumpcode=0772a8d396911951022db5ea385535f6


deploy
~~~~~~

deploy:check_composer_install
+++++++++++++++++++++++++++++

Check if there is composer.lock file on current instance and if its there then make dry run for
"composer install". If "composer install" returns information that some packages needs to be updated
or installed then it means that probably developer pulled composer.lock changes from repo but forget
to make "composer install". In that case deployment is stopped to allow developer to update packages,
make some test and make deployment then.

deploy:check_lock
+++++++++++++++++

Checks for existance of file deploy.lock in root of current instance. If the file deploy.lock is there then
deployment is stopped.

You can use it for whatever reason you have. Imagine that you develop css/js locally with "grunt watch".
After you have working code you may forget to build final js/css with "grunt build" and you will deploy
css/js that will be not used on production which reads compiled css/js.

To prevent this situation you can make "grunt watch" to generate file "deploy.lock" (with text "Run
'grunt build'." inside) to inform you that you missed some step before deploying application.

file
~~~~

file\:rm2steps\:1
+++++++++++++++++

Allows to remove files and directories in two steps for "security" and "speed".

**Security**

Sometimes removing cache folders with lot of files takes few seconds. In meantime of that process a new frontend
request can hit http server and new file cache will start to being generated because it will detect that some cache
files are missing and cache needs to be regnerated. A process which is deleting the cache folder can then delete
the newly generated cache files. The output of cache folder is not predictable in that case and can crash
the application.

**Speed**

If you decide to remove the cache folder during the `buffer:start`_ then its crucial to do it as fast as possbile in
order to buffer as low requests as possible.


The solution for both problems of "security" and "speed" is first rename the folder to some temporary and then delete it
later in next step. Renaming is atomic operation so there is no possibility that new http hit will start to build cache
in the same folder. We also gain speed because we can delete the folders/files at the end of deployment with task
`file:rm2steps:2`_ if thats needed at all because deployer "clenup" task will remove old releases anyway.


file\:rm2steps\:2
+++++++++++++++++

The second step of file:rm2steps tandem. Read more on `file:rm2steps:1`_


media
~~~~~

Options:

- | **exclude**
  | *default value:* null
  |
  | Array with patterns to be excluded.

  |
- | **exclude-case-insensitive**
  | *default value:* null
  |
  | Array with patterns to be excluded. Because rsync does not support case insensitive then
    each value of array is set in state uppercase/lowercase. That means if you will have ``['*.mp4', '*.zip']``
    then final exclude will be ``--exclude '*.[mM][pP]4' --exclude '*.[zZ][iI][pP]'``

  |
- | **exclude-file**
  | *default value:* null
  |
  | String containing absolute path to file, which contains exclude patterns.

  |
- | **include**
  | *default value:* null
  |
  | Array with patterns to be included.

  |
- | **include-file**
  | *default value:* null
  |
  | String containing absolute path to file, which contains include patterns.

  |
- | **filter**
  | *default value:* null
  |
  | Array of rsync filter rules

  |
- | **filter-file**
  | *default value:* null
  |
  | String containing merge-file filename.

  |
- | **filter-perdir**
  | *default value:* null
  |
  | String containing merge-file filename to be scanned and merger per each directory in rsync
    list offiles to send.

  |
- | **flags**
  | *default value:* rz
  |
  | Flags added to rsync command.

  |
- | **options**
  | *default value:* ['copy-links', 'keep-dirlinks', 'safe-links']
  |
  | Array of options to be added to rsync command.

  |
- | **timeout**
  | *default value:* 0
  |
  | Timeout for rsync task. Zero means no timeout.


Default configuration for task:
::
   set('media-default',
    [
        'exclude' => [],
        'exclude-case-insensitive' => [
            '*.mp4',
            '*.zip',
            '*.pdf',
            '*.exe',
            '*.doc',
            '*.docx',
            '*.pptx',
            '*.ppt',
            '*.xls',
            '*.xlsx',
            '*.xlsm',
            '*.tiff',
            '*.tif',
            '*.potx',
            '*.mpg',
            '*.mp3',
            '*.avi',
            '*.wmv',
            '*.flv',
            '*.eps',
            '*.ai',
            '*.mov',
        ],
        'exclude-file' => false,
        'include' => [],
        'include-file' => false,
        'filter' => [],
        'filter-file' => false,
        'filter-perdir' => false,
        'flags' => 'rz',
        'options' => ['copy-links', 'keep-dirlinks', 'safe-links'],
        'timeout' => 0,
    ]);


In your project you should set "media" which will be merged with "media-default" configuration.

Example configuration for TYPO3:
::

   set('media',
       [
        'filter' => [
            '+ /fileadmin/',
            '- /fileadmin/_processed_/*',
            '+ /fileadmin/**',
            '+ /uploads/',
            '+ /uploads/**',
            '- *'
       ]
   ]);


media:move
++++++++++

Move media from target instance to second target instance using rsync and options from "media-default" and "media".

Its a shortcut for two separated commands.
::

   media:move target1 target2


Is in fact:
::

   media:pull target1
   media:push target2

**Notice!**

Media are not moved directly from target1 to target2. First its synchronised from target1 instance to current
instance and then from current instance to target2 instance.

media:pull
++++++++++

Pull media from target instance to current instance using rsync and options from "media-default" and "media".

media:push
++++++++++

Pull media from current instance to target instance using rsync and options from "media-default" and "media".


php
~~~

php:clear_cache_cli
+++++++++++++++++++

This task clear the stat cache for real file pathes (http://php.net/manual/en/function.clearstatcache.php).
Additionally it clears opcache and eaccelaeator cache for CLI context.

php:clear_cache_http
++++++++++++++++++++

This task clear the opcache and eaccelaeator cache for WEB context.

To-Do list
----------

1. Refactor config:vhost to support nginx


Changelist
----------

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
