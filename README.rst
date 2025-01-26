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

Setting's documentation
------------------------

composer_version
~~~~~~~~~~~~~~~~

Install specific composer version. Use tags. Valid tags are here https://github.com/composer/composer/tags . Default
value is ``null``.


composer_channel
~~~~~~~~~~~~~~~~

Install latest version from channel. Set this variable to '1' or '2' (or 'stable', 'snapshot', 'preview'). Read more on composer docs.
Default value is ``stable`` which will install latest version of composer. If you need stability set it better to '1' or '2'.

composer_channel_autoupdate
~~~~~~~~~~~~~~~~~~~~~~~~~~~

If set then on each deploy the composer is checked for latest version according to ``composer_channel`` settings.
Default value is ``true``.

web_path
~~~~~~~~

Path to public when not in root of project. Must be like "pub/" so without starting slash and with ending slash.


Task's documentation
--------------------

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
"composer install". If "composer install" returns information that some packages needs to be updated
or installed then it means that probably developer pulled composer.lock changes from repo but forget
to make "composer install". In that case deployment is stopped to allow developer to update packages,
make some test and make deployment then.

deploy:check_composer_validate
++++++++++++++++++++++++++++++

Check if ``composer.lock`` file is up to date with current state of ``composer.json``.
In not then deployment is stopped.

deploy:check_lock
+++++++++++++++++

Checks for existence of file deploy.lock in root of current instance. If the file deploy.lock is there then
deployment is stopped.

You can use it for whatever reason you have. Imagine that you develop css/js locally with "grunt watch".
After you have working code you may forget to build final js/css with "grunt build" and you will deploy
css/js that will be not used on production which reads compiled css/js.

To prevent this situation you can make "grunt watch" to generate file "deploy.lock" (with text "Run
'grunt build'." inside) to inform you that you missed some step before deploying application.

file
~~~~
file\:backup
++++++++++++

Creates backup of files.
Single task may perform multiple archivization using defined filters.
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

Sample configuration:
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
Each package defines filters which will be used in `find` command.
First level element are groups which will be concatenated using logical alternative operator operator OR.
If group is array type then group elements will be concatenated using logical conjunction operator.

Package *config*:
It is simplest definition.
For this package all files from directory "./etc/" will be backuped.

Package *translations*:
For this one all files from directory "./l10n/" will be backuped.
It will also include files from all "l10n/" from "modules" subdirectory.
For example "modules/cookies/l10n"

Package *small_images*:
This one will contain all small (smaller than 25kB) files from "media/uploads" and "media/theme".

As you can see *file_backup_keep* is set to 10 which means only newest 10 backups per package will be stored.


file:copy_dirs_ignore_existing
++++++++++++++++++++++++++++++

Copy directories from previous release except for those directories which already exists in new release.

file:copy_files_ignore_existing
+++++++++++++++++++++++++++++++

Copy files from previous release except for those files which already exists in new release.


file\:rm2steps\:1
+++++++++++++++++

Allows to remove files and directories in two steps for "security" and "speed".


cache
~~~~~

cache:clear_php_cli
+++++++++++++++++++

This task clears the file status cache, opcache and eaccelerator cache for CLI context.

cache:clear_php_http
++++++++++++++++++++

This task clears the file status cache, opcache and eaccelerator cache for HTTP context. It does following:

1) Creates file "cache_clear_[random].php" in "{{deploy_path}}/current" folder.
2) Fetch this file with selected method - curl / wget / file_get_contents - by default its wget.
3) The file is not removed after clearing cache for reason. It allows to prevent problems with realpath_cache. For
   more info read http://blog.jpauli.tech/2014-06-30-realpath-cache-html/

You must set **public_urls** configuration variable so the script knows the domain it should fetch the php script.
Here is example:

::

  server('prelive', 'example.com', 22)
    ->user('deploy')
    ->stage('prelive')
    ->set('deploy_path', '/home/web/html/www.example.com.prelive')
    ->set('public_urls', ['https://prelive.example.com']);


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

- | **local/bin/curl**
  | *required:* no
  | *default value:* value of "which curl"
  | *type:* string
  |
  | Path to curl binary on current system.
  |

- | **local/bin/wget**
  | *required:* no
  | *default value:* value of "which wget"
  | *type:* string
  |
  | Path to wget binary on current system.
  |

- | **local/bin/php**
  | *required:* no
  | *type:* string
  |
  | Path to php binary on current system.
  |


Changelog
---------

See https://github.com/sourcebroker/deployer-extended/blob/master/CHANGELOG.rst
