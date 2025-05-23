deployer-extended
=================

    .. image:: http://img.shields.io/packagist/v/sourcebroker/deployer-extended.svg?style=flat
        :target: https://packagist.org/packages/sourcebroker/deployer-extended

    .. image:: https://img.shields.io/badge/license-MIT-blue.svg?style=flat
        :target: https://packagist.org/packages/sourcebroker/deployer-extended

.. contents:: :local:

What does it do?
----------------

Library with some additional tasks for deployer (deployer.org).

The project is organized into two main directories:

- ``deployer/``

  Contains files (tasks/settings) that are safe to be loaded as until explicitly used they do not affect Deployer.
  So even if you use two tasks from ``deployer/`` folder you can load whole directory safely. Tasks are set to
  be hidden by default so they will not pollute Deployer task list.

- ``includes/``

  Contains files that should be included selectively in your deployment process.
  Unlike the functionality in the ``deployer/`` directory, these components can override default Deployer functionality
  (for example override ``bin/composer`` or ``bin/php`` setting in Deployer).

New tasks (``deployer/`` folder)
----------------------------

cache
~~~~~

cache:clear_php_cli
+++++++++++++++++++

This task clears the file status cache, opcache and eaccelerator cache for CLI context.

cache:clear_php_http
++++++++++++++++++++

This task clears the file status cache, opcache and eaccelerator cache for HTTP context. It does following:

1) Creates file ``cache_clear_[random].php`` in ``{{deploy_path}}/current`` folder.
2) Fetch this file with selected method - curl / wget / file_get_contents - by default its wget.
3) The file is not removed after clearing cache for reason. It allows to prevent problems with realpath_cache.

You must set **public_urls** configuration variable so the script knows the domain it should fetch the php script.
Here is example:

::

   host('staging')
    ->setHostname('vm-dev.example.com')
    ->setRemoteUser('project1')
    ->set('public_urls', ['https://staging-t3base13.example.com'])
    ->set('deploy_path', '~/t3base13.example.com/staging');


Task configuration variables:

- | **cache:clear_php_http:phpcontent**
  | *required:* no
  | *type:* string
  | *default value:*
  ::

    <?php
      clearstatcache(true);
      if(function_exists('opcache_reset')) opcache_reset();
      if(function_exists('eaccelerator_clear')) eaccelerator_clear();

  |
  | Php content that will be put into dynamically created file that should clear the caches.
  |

- | **public_urls**
  | *required:* yes
  | *default value:* none
  | *type:* array
  |
  | Domain used to prepare url to fetch clear cache php file. Its expected to be array so you can put there more than one
    domain and use it for different purposes but here for this task the first domain will be taken.
  |

- | **fetch_method**
  | *required:* no
  | *default value:* wget
  | *type:* string
  |
  | Can be one of following value:
  | - curl,
  | - wget,
  | - file_get_contents
  |

- | **cache:clear_php_http:timeout**
  | *required:* no
  | *default value:* 15
  | *type:* integer
  |
  | Set the timeout in seconds for fetching php clear cache script.
  |


deploy
~~~~~~

deploy:check_branch
+++++++++++++++++++

Check if the branch you want to deploy is different from the branch currently deployed on host. If you have information that
the branch on the host is different than the branch you want to deploy then you can take decision to overwrite it or not.

deploy:check_branch_local
+++++++++++++++++++++++++

Check if the branch you are currently checked out on your local is the same branch you want to deploy.
The ``deploy.php`` files on both branches can be different and that can influence the deploy process.

deploy:check_composer_install
+++++++++++++++++++++++++++++

Check if there is composer.lock file on current instance and if its there then make dry run for
``composer install``. If ``composer install`` returns information that some packages needs to be updated
or installed then it means that probably developer pulled ``composer.lock`` changes from repo but forget
to make ``composer install``. In that case deployment is stopped to allow developer to update packages,
make some test and make deployment then.

deploy:check_composer_validate
++++++++++++++++++++++++++++++

Check if ``composer.lock`` file is up to date with current state of ``composer.json``.
In not then deployment is stopped.

deploy:check_lock
+++++++++++++++++

Checks for existence of file deploy.lock in root of current instance. If the file deploy.lock is there then
deployment is stopped. You can use it for whatever reason you have. Needed mainly if you do development from
local and not from CI.


file
~~~~
file\:backup
++++++++++++

Creates backup of files. Single task may perform multiple archivization using defined filters.
Old ones are deleted after executing this task. Default limit is 5.

Configuration description

- | **file_backup_packages**
  | *required:* yes
  | *default value:* none
  | *type:* array
  |
  | Packages definition

- | **file_backup_keep**
  | *required:* no
  | *default value:* 5
  | *type:* int
  |
  | Limit of backups per package

Example configuration:
::

    set('file_backup_packages', [
        'config' => [
            '-path "./etc/*"',
        ],
        'translations' => [
            '-path "./l10n/*"',
            '-path "./modules/*/l10n/*"',
        ],
        'small_images' => [
            [ '-path "./media/uploads/*"', '-size -25k' ],
            [ '-path "./media/theme/*"', '-size -25k' ],
        ],
    ]);

    set('file_backup_keep', 10);

Config variable *file_backup_packages* stores information about backup packages and files filtering options.
Each package defines filters which will be used in ``find`` command.
First level element are groups which will be concatenated using logical alternative operator operator OR.
If group is array type then group elements will be concatenated using logical conjunction operator.

Package *config*:
It is simplest definition.
For this package all files from directory ``./etc/`` will be backuped.

Package *translations*:
For this one all files from directory ``./l10n/`` will be backuped.
It will also include files from all ``l10n/`` from "modules" subdirectory.
For example ``modules/cookies/l10``

Package *small_images*:
This one will contain all small (smaller than 25kB) files from "media/uploads" and "media/theme".

As you can see *file_backup_keep* is set to 10 which means only newest 10 backups per package will be stored.


file:copy_dirs_ignore_existing
++++++++++++++++++++++++++++++

Copy directories from previous release except for those directories which already exists in new release.

file:copy_files_ignore_existing
+++++++++++++++++++++++++++++++

Copy files from previous release except for those files which already exists in new release.

file:upload_build
+++++++++++++++++++++++++++++++

Upload files not defined in ``clear_paths``, ``shared_files``, ``shared_dirs``.
Can be used as good default for uploading build from CI.


service
~~~~~~~

service:php_fpm_reload
++++++++++++++++++++++

Very simple task for php-fpm reloading. There is lot of different ways to reload php-fpm depending on hoster configuration.
The command can look like ``nine-flush-fpm`` (nine.ch hoster), ``killall -9 php-cgi`` (hostpoint.ch hoster) or just more
regular ``sudo service php84-fpm reload``.

All you need to do is to add to host configuration ``service_php_fpm_reload_command`` setting with command that should be executed.

Example:

::

 host('production')
   ->setHostname('my.example.com')
   ->setRemoteUser('deploy')
   ->set('deploy')
   ->set('service_php_fpm_reload_command', 'sudo service php84-fpm reload')

Then add it also to you deploy flow like ``after('deploy:symlink', 'service:php_fpm_reload');``;
This is not done here as the rule is that ``sourcebroker/deployer-extended`` should not override default Deployer tasks
or settings.

Tasks and settings override (``includes/`` folder)
--------------------------------------------------

bin/composer
~~~~~~~~~~~~

In ``includes/settings/bin_composer.php`` you can find ``bin/composer`` setting override. This implementation has more functionality
compared to default Deployer version. It allows to install specific version of composer and later check if composer
is up to date.

- | **composer_version**
  | *required:* no
  | *default value:* null
  |
  | Install specific composer version. Use tags. Valid tags are here https://github.com/composer/composer/tags .
  |

- | **composer_channel**
  | *required:* no
  | *default value:* stable
  |
  | Install latest version from channel. Set this variable to '1' or '2' (or 'stable', 'snapshot', 'preview'). Read more on composer docs.
  | Default value is ``stable`` which will install latest version of composer.
  |

- | **composer_channel_autoupdate**
  | *required:* no
  | *default value:* true
  |
  | If set then on each deploy the composer is checked for latest version according to ``composer_channel`` settings.
  | Default value is ``true``.
  |


bin/php
~~~~~~~

In ``includes/settings/bin_php.php`` you can find ``bin/php`` setting override. This implementation has more functionality
compared to default Deployer version.

It works like:

1. It takes as first ``php_version`` if it is set explicitly for host.
2. If ``php_version`` is not set for host then ``composer.json`` file is searched for ``['config']['platform']['php']``
   and if not found then for ``['require']['php']``.
3. Values set from point 1 or point 2 are then normalised to 'X.Y' and system is checked for specific PHP binaries
   with ``which('phpX.Y')`` and ``which('phpXY')``.
4. If none of ``php_version``,  ``['config']['platform']['php']``,  ``['require']['php']`` are set then there
   is standard check for ``which('php')``.

Generally after you include this you can completely forget about ``bin/php`` setting.


releases
~~~~~~~~

In ``includes/tasks/releases.php`` you can find ``release`` task override.

This task solves performance problems of original Deployer "releases" task.
Read more at PR added https://github.com/deployphp/deployer/pull/4034

Issue has been solved but will be available only in Deployer 8. If you still want it in Deployer 7 then here it is.


Changelog
---------

See https://github.com/sourcebroker/deployer-extended/blob/master/CHANGELOG.rst
