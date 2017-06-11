
.. image:: https://styleci.io/repos/82486796/shield?branch=master
   :target: https://styleci.io/repos/82486796

.. image:: https://scrutinizer-ci.com/g/sourcebroker/deployer-extended/badges/quality-score.png?b=master
   :target: https://scrutinizer-ci.com/g/sourcebroker/deployer-extended/?branch=master

.. image:: http://img.shields.io/packagist/v/sourcebroker/deployer-extended.svg?style=flat
   :target: https://packagist.org/packages/sourcebroker/deployer-extended

.. image:: https://img.shields.io/badge/license-MIT-blue.svg?style=flat
   :target: https://packagist.org/packages/sourcebroker/deployer-extended


deployer-extended
=================

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

Just use composer.

::

    composer require sourcebroker/deployer-extended

For best experience look also for corensponding framework packages that depends on
sourcebroker/deployer-extended:

1) sourcebroker/deployer-extended-typo3
2) sourcebroker/deployer-extended-magento


Task's documentation
------------------

buffer
~~~~~~

buffer:start
++++++++++++

TODO.

buffer:end
++++++++++

TODO.

config
~~~~~~

config:vhost
++++++++++++

TODO.

db
~~

This tasks allows you to make database operation on current instance and between instances.
The most useful is ability to pull database from remote instance to current instance: `dep db:pull live`
or to move database between remote instances, eg: `dep db:move live dev`


- | *Options*
  | Domain name (string)

- | *Arguments*
  | Domain name (string)

- | *Variables*
  | Domain name (string)


db:download
+++++++++++

- | *Note*
  | Download database from target instance to current instance.
    There is required option --dumpcode to be passed.

- | *Example*

  ::

   dep db:download live --dumpcode=0772a8d396911951022db5ea385535f6

db:export
+++++++++

- | *Note*
  | Export database to database storage on current instance.

    The database will be stored in two separate files. One with tables structure. The second with data only.
    Example files:

    * <sup>2017-02-26_14:56:08#server:live#dbcode:database_default#type:data#dumpcode:362d7ca0ff065f489c9b79d0a73720f5.sql</sup>
    * <sup>2017-02-26_14:56:08#server:live#dbcode:database_default#type:structure#dumpcode:362d7ca0ff065f489c9b79d0a73720f5.sql</sup>


- | *Example*

  ::

   dep db:export



db:import
+++++++++

- | *Note*
  | Import database from current instance database storage.
    There is required option --dumpcode to be passed.

- | *Example*

  ::

   dep db:import --dumpcode=0772a8d396911951022db5ea385535f66



db:move
+++++++

- | *Note*
  | This command allows you to move database between instances.
    In the background it runs several commands to accomplish this task.

- | *Example*
  | Example call when you are on your local instance can be ``dep db:move live dev``
    This will move database from live instance to dev instance.
    It will do following:
    1) First it runs db:export task on target instance and get the "dumpcode" as return to use it in next commands.
    2) Then it runs db:download (with "dumpcode" value from first task).
    3) Then it runs db:process (with "dumpcode" value from first task).
    4) Then it runs db:import (with "dumpcode" value from first task).


db:process
++++++++++

- | *Note*
  | This command will run some defined commands on pure sql file as its sometimes needed to remove
    or replace some strings directly on sql file before importing.
    There is required option --dumpcode to be passed.

- | *Example*
  ::

   dep db:process --dumpcode=0772a8d396911951022db5ea385535f66


db:pull
+++++++

- | *Note*
  | This command allows you to download database from target instance to current instance.
    In the background it runs several commands to accomplish this task.
    It will do following:
    1) First it runs db:export task on target instance and get the "dumpcode" as return to use it in next commands.
    2) Then it runs db:download (with "dumpcode" value from first task).
    3) Then it runs db:process (with "dumpcode" value from first task).
    4) Then it runs db:import (with "dumpcode" value from first task).

- | *Example*
  ::

   dep db:pull live

db:truncate
+++++++++++

- | *Note*
  | This command allows you to truncate database tables defined in database config var "caching_tables"

- | *Example*
  | Allow only request with GET param secret=999 to access frontend.

  ::

   dep db:truncate --dumpcode=0772a8d396911951022db5ea385535f6


db:upload
+++++++++

- | *Note*
  | This command will upload the sql dump file to target instance.
  | There is required option --dumpcode to be passed.

- | *Example*
  | Upload database with dumpcode 0772a8d396911951022db5ea385535f6 to live instance
    and store it on database storage folder.

  ::

   dep db:upload live --dumpcode=0772a8d396911951022db5ea385535f6


deploy
~~~~~~

deploy:check_composer_install
+++++++++++++++++++++++++++++

- *Note*

  - Check if there is composer.lock file on current instance and if its there then make dry run for
    "composer install". If "composer install" returns information that some packages needs to be updated
    or installed then it means that probably developer pulled composer.lock changes from repo but forget
    to make "composer install". In that case deployment is stopped to allow developer to update packages,
    make some test and make deployment then.

deploy:check_lock
+++++++++++++++++

- *Note*

  - Check for existance of file deploy.lock in root of current instance. If the file deploy.lock is there then
    deployment is stopped.

    You can use it for whatever reason you have. Imagine that you develop css/js locally with "grunt watch".
    After you have working code you may forget to build final js/css with "grunt build" and you will deploy
    css/js that will be not used on production which reads compiled css/js.

    To prevent this situation you can make "grunt watch" to generate file "deploy.lock" (with text "Run
    'grunt build'." inside) to inform you that you missed some step before deploying application.


media
~~~~~

media:move
++++++++++

TODO.

media:pull
++++++++++

TODO.

media:push
++++++++++

TODO.


php
~~~

php:clear_cache_cli
+++++++++++++++++++

TODO.

php:clear_cache_http
++++++++++++++++++++

TODO.


To-Do list
----------

1. Refactor config:vhost to support nginx


Changelist
----------

2.0.0
~~~~~

b) Update documentation
