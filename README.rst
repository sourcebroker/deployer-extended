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

**NOTE! Its tested only with Deployer 4.3.0!**

Installation
------------
1) Install package with composer:
   ::

      composer require sourcebroker/deployer-extended

2) If you are using deployer as composer package then just put following line in your deploy.php:
   ::

      new \SourceBroker\DeployerExtended\Loader();

3) If you are using deployer as phar then put following lines in your deploy.php:
   ::

      require __DIR__ . '/vendor/autoload.php';
      new \SourceBroker\DeployerExtended\Loader();

   | IMPORTANT NOTE!
   | Because there is inclusion of '/vendor/autoload.php' inside deployer realm then sometimes there can be conflict
     of deployer dependencies with you project dependencies. Quite often its about symfony/console version or
     monolog/monolog version because they are most common between projects. In that case use deployer installed as
     composer package and resolve the dependency problems on composer level. Example of error when you run "dep" command
     and there are dependencies problems:
   ::

      Fatal error: Declaration of Symfony\Component\Console\Input\ArrayInput::hasParameterOption() must be compatible with Symfony\Component\Console\Input\InputInterface::hasParameterOption($values, $onlyParams = false) in /.../vendor/symfony/symfony/src/Symfony/Component/Console/Input/ArrayInput.php on line 190


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

When you run `buffer:stop`_ all the waiting requests will hit the http server (or cli entrypoint).

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
- | **entrypoint_refresh**
  | *required:* no
  | *default value:* 200000 μs (200ms)
  |
  | How often the entrypoint will recheck if buffer.lock is still there. Values in microseconds.
  | 100000 μs = 100 ms = 0,1 s.
  |

- | **entrypoint_inject**
  | *required:* no
  |
  | A php code that actually do the buffering.
  | The default code with already prefilled variables (random, locker_filename, locker_expire, entrypoint_refresh):
  ::

       isset($_SERVER['HTTP_X_DEPLOYER_DEPLOYMENT']) && $_SERVER['HTTP_X_DEPLOYER_DEPLOYMENT'] == '823094823094' ? $deployerExtendedEnableBufferLock = false : $deployerExtendedEnableBufferLock = true;
       isset($_ENV['DEPLOYER_DEPLOYMENT']) && $_ENV['DEPLOYER_DEPLOYMENT'] == '823094823094' ? $deployerExtendedEnableBufferLock = false: $deployerExtendedEnableBufferLock = true;
       while (file_exists(__DIR__ . 'buffer.lock') && $deployerExtendedEnableBufferLock) {
         usleep(200000);
         clearstatcache(true, __DIR__ . '/buffer.lock');
         if(time() - filectime(__DIR__ . '/buffer.lock') > 60) unlink(__DIR__ . '/buffer.lock');
       }


- | **locker_filename**
  | *required:* no
  | *default value:* buffer.lock
  |
  | When file with name "buffer.lock" exists the reqests are buffered. The task `buffer:stop`_ just removes
    the "buffer.lock" files without removing the "entrypoint_inject" code.
  |

- | **locker_expire**
  | *required:* no
  | *default value:* 60
  |
  | The time in seconds after which the buffer.lock files will be removed automatically.
  |
  | Usually its buffer:stop task that should remove buffer.lock files. Unfortunatly sometimes deploy can fail. If deploy
  | will fail after buffer:start task and before buffer:stop then the buffer.lock files will stay and block access to
  | entrypoints for good. In edge cases it can lead to run out all apache forks or if CLI entrypoint will be called
  | often by cron it can overload RAM. This is why its important to remove buffer.lock files after some time no matter
  | if the task buffer:stop will be called or not.

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

Changelog
---------

See https://github.com/sourcebroker/deployer-extended/blob/master/CHANGELOG.rst
