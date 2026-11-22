# Changelog

## 24.3.0

1)  [FEATURE] Add setting `file_upload_build_rsync_options` with additional rsync options for task
    `file:upload_build`, e.g. to set permissions of uploaded files independently of the build environment
    (`['--no-perms', '--chmod=ugo=rwX,o-w']`). Empty by default, so source permissions are kept as before.
2)  [TASK] Add `.gitattributes` to exclude development files (e.g. `.ddev`, `.gitignore`) from Composer package.
3)  [DOCS] Convert documentation from reStructuredText to Markdown. Old anchors in README are kept.
4)  [TASK] Fix links to README in task comments of `file:backup` and `service:php_fpm_reload`.

## 24.2.1

1)  [BUGFIX] Restore PHP 7.4 compatibility in cache:clear_php_http

## 24.2.0

1)  [FEATURE] Support for Deployer 8 (`deployer/deployer ^8.0.0`) with backward compatibility for Deployer 7.
2)  [BUGFIX] Fix `cache:clear_php_http` task crashing on Deployer 8 due to changed `runLocally()` signature
    (second argument changed from `?array $options` to `?string $cwd`). Timeout is now passed as a named
    argument (`timeout: $value`) which works correctly with both Deployer 7 and 8. Also fixes a pre-existing
    bug where the timeout value was silently ignored due to a numeric-keyed array being passed instead of an
    associative one.

## 24.1.2

1)  [BUGFIX] The --write flag doesn't exist in curl. Changed to --write-out to properly output HTTP status code.
    (credits to [maikschneider](https://github.com/maikschneider) )

## 24.1.1

1)  [BUGFIX] Fix the logic for resolving PHP binary. If the binary specified by `php_version` or `composer.json`
    is not found, it now gracefully falls back to search for `php` binary and finally defaults to plain `php`.

## 24.1.0

1)  [TASK][FEATURE] Print http status code for curl in cache:clear_php_http (credits to [martipoe](https://github.com/martipoe) )

## 24.0.0

1)  [TASK][BREAKING] Move `service_php_fpm_reload.php` from `includes/` to auto loaded `deployer/service/service_php_fpm_reload.php`.

## 23.0.0

1)  [TASK][BREAKING] Remove declaration of `set('web_path', '');` as it creates problems with overriding if other
    packages use the same name. Breaking change, because if this setting was used outside this package then now will be null,
    but was empty string.

## 22.0.0

1)  [FEATURE] Add `bin/php` that enhance standard behaviour of Deployer. It will check for
    `php_version` in host settings and if not found then it will check for `['config']['platform']['php']`
    and `['require']['php']` in composer.json. If not found then it will fallback to standard `php`.

2)  [TASK][BREAKING] Enhance existing `local/bin/php` to check for `php_version` in host settings and
    if not found then it will check for `['config']['platform']['php']` and `['require']['php']` in
    composer.json. If not found then it will fallback to standard `php`. This can return different version of PHP so
    breaking change.

3)  [TASK][BREAKING] Move `bin/composer` override to `includes/settings/bin_composer.php`. This is breaking change as path  
    has changed and this file is loaded manually.

4)  [FEATURE] Add fixed version of original "release" task (<https://github.com/deployphp/deployer/pull/4034>) which will
    be available only to Deployer 8. Use if you still need it for Deployer 7.

5)  [FEATURE] Add simple task for php-fpm reloading. Make it hidden by default.

6)  [TASK][BREAKING] Remove deprecated eaccelerator calls.

## 21.0.0

1)  [BUGFIX] Remove dependency to `argument_host` which was part of removed `sourcebroker/deployer-instance`.
2)  [TASK][BREAKING] Remove overwriting of `branch` setting. This should be done at higher level. Base should follow Deployer
    default.
3)  [TASK][BREAKING] Remove overwriting of `bin/composer` setting. Base should be Deployer default. If you want then include
    `includes/composer.php` file directly in your project.
4)  [TASK][BREAKING] Make all task hidden as they should be usually part of deploy task. If you want to unhide task then
    `task('taskname')->hidden(false);` in your deploy.php file.
5)  [TASK][BREAKING] Rename `current_dir` setting to `project_root` to reflect real usage.
6)  [TASK][BREAKING] Remove `random` setting to not pollute global Deployer scope without a good reason. After recent
    cleanup on tasks the `random` setting was used only at one task `cache:clear_php_http`.

## 20.0.0

1)  [TASK][BREAKING] Remove dependency to `sourcebroker/deployer-instance`. Not needed anymore.
2)  [TASK][BREAKING] Remove task `buffer:start`, `buffer:stop` as hard to test on so many hoster configs and different level of traffic.
3)  [TASK][BREAKING] Remove task `file:rm2steps:1`, `file:rm2steps:2` as should be not needed for nowadays deployment flow.
4)  [FEATURE] Add task `upload_build`.

## 19.0.0

1)  [TASK][BREAKING] Replace `--ignore-platform-reqs` with `{{check_composer_install_options}}` in `deploy:check_composer_install`  
    This change allows for greater flexibility in managing the options for `composer install --dry-run`.
    Default Value:
    `--no-dev --no-interaction --no-progress --no-suggest --no-scripts --no-plugins`
    Note:
    The value for `{{check_composer_install_options}}` must differ from `{{composer_options}}`, as the latter
    has `--no-dev` by default. Using `--no-dev` in local development would remove require-dev packages.

2)  [FEATURE] Add `deploy:check_composer_validate` task.

## 18.0.0

1)  [TASK][BREAKING] Bump dependency to `sourcebroker/deployer-instance`.
2)  [TASK] Clean up code.

## 17.0.0

1)  [TASK] Code refactor. Change Exception to GracefulShutdownException. Casting optimisation. Code reformatting / cleanup.
2)  [BREAKING] Remove task `deploy_extend_log` as it is now part of Deployer 7. Refactor task `deploy_check_branch`
    to check extended data deployer meta data file.
3)  [TASK] Refactor code for Deployer 7.
4)  [TASK] Update dependencies for Deployer 7.
5)  [TASK] Extend dependency to internal packages to dev-master.

## 16.1.0

1)  [BUGFIX] For task `cache:clear_php_http` add `--no-check-certificate` for wget (curl already have `--insecure`)
2)  [FEATURE] For task `cache:clear_php_http` add `fetch_method_wget_options` and `fetch_method_curl_options`.

## 16.0.0

1)  [FEATURE][BREAKING] Overwrite "bin/composer" and allow to setup composer binary version with `composer_version`
    or `composer_channel`. The default setting are: `composer_version=null` and `composer_channel="stable"`.
    This will download most new stable version of composer (for now version 2).
2)  [TASK][BREAKING] For task `deploy_check_composer_install` local version of composer must be available. So far it was
    downloaded in `set('local/bin/composer')`. Since this change there will be no try to download it. Composer must be
    installed and accessible locally for user running commands.
3)  [BUGFIX] Fix check if composer install must be run because of composer.lock changes.

## 15.0.0

1)  [TASK][BREAKING] Remove config:vhost_apache task without replacement. Use `https://ddev.readthedocs.io/en/stable/`
    or similar solutions for local development.
2)  [TASK][BREAKING] Add dependency to breaking `sourcebroker/deployer-instance`.
3)  [TASK] Add ddev support.

## 14.1.0

1)  [FEATURE] Add task `file:copy_dirs_ignore_existing` which copy directories from previous release except for those  
    folders which already exists in that folder.

2)  [FEATURE] Add task `file:copy_files_ignore_existing` which copy files from previous release except for those  
    files which already exists in that folder.

## 14.0.1

1)  [BUGFIX] Fix refactor of composer binary detection.

## 14.0.0

1)  [TASK][BREAKING] Change default php-fpm directive from ProxyPass to SetHandler in task `config vhost_apache`.
2)  [BUGFIX] Add support for `web_path` added to `vhost_document_root` in task `config vhost_apache`.
3)  [TASK] Refactor way the binary is detected. Possible breaking change.

## 13.0.0

1)  [TASK][BREAKING] Change to new deployer-instance version.
2)  [TASK][BREAKING] Set naming according to new deployer-instance.

## 12.0.0

1)  [BUGFIX] Compatibility with 6+. Fix local test for composer.json in config_vhost_apache.php.
2)  [BUGFIX] Fix wrong calculation for vhost_local_logs_path.
3)  [BUGFIX] Fix test of existence for symlinked log files.
4)  [TASK] Refactor deploy:check_branch task.
5)  [TASK] Add deploy:check_branch_local task.
6)  [BUGFIX] Fix condition in deploy:check_branch_local task.
7)  [BUGFIX] Move cd() before condition in deploy_check_branch.php
8)  [TASK] Add ability to turn of branch guess which is by default added to deployer. By setting "branch_detect_to_deploy" you can disable deploying currently checkout branch.
9)  [TASK] Refactor deploy:extend_log to support for revision and tag options.
10) [TASK] Support to show time of deployment in deployment note.

## 11.0.1

1)  [BUGFIX] Fix wrong detection of previous clear http cache file.

## 11.0.0

1)  [TASK][BREAKING] Rename task php:clear_cache_cli.php to cache:clear_php_cli and php:clear_cache_http.php to cache:clear_php_http
2)  [TASK][BREAKING] Rename the logs filename to .dep/releases.extended. Add hash to log line. Format log line with csv formatter.
3)  [TASK][BREAKING] Deployer 6.4+ compatibility.

## 10.0.2

1)  [BUGFIX] Replace colon in filenames with underscore for Windows compatibility.

## 10.0.1

1)  [BUGFIX] Fix wrong formatting for info form deploy:check_branch task. Add GracefulShutdownException.

## 10.0.0

1)  [BUGFIX] Replace colon in filenames with underscore for Windows compatibility.
2)  [FEATURE] Add task deploy:check_branch to check if branch deployed to instance is the same as the one which is being deployed.
3)  [FEATURE] Add task extend:log to store additional info about deploy.
4)  [TASK] Increase version of sourcebroker/deployer-instance
5)  [TASK] Normalize use of dots at the end of task description.

## 9.1.0

1)  [FEATURE] Add possibility to set "vhost_document_root" outside.

## 9.0.0

1)  [BUGFIX][BREAKING] Fix wrong flag name for old release and prevent creation of this flag if current folder does not exits.
2)  [BUGFIX][BREAKING] Create "old release" flag just before removing "buffer request" flag.
3)  [TASK] Refactor buffer:stop
4)  [TASK][BREAKING] Remove composer dependencies to sourcebroker/deployer-loader. It must be declared in higher level package as
    someone can use task without autoloader.
5)  [TASK][BREAKING] Refactor config:vhost_apache with possible breaking changes.
6)  [TASK] Refactor config:vhost_apache.

## 8.0.1

1)  [BUGFIX] Remove unneeded comments.
2)  [DOCS] Docs cleanup.

## 8.0.0

1)  [TASK][!!!BREAKING] Remove default set('fetch_method', 'wget'); as it should have fallback in task itself.
2)  [BUGFIX] Create lock file in buffer:start only when directory exists.
3)  [TASK] Tasks buffer:start, buffer:stop code cleanup.
4)  [TASK][!!!BREAKING] Remove multiplexing on from deployer-extended default config vars as it should be part of higher
    level package.
5)  [TASK][!!!BREAKING] Remove FileUtility class to make tasks more independent. The method usage from this class
    usage was not really big in the end.
6)  [TASK][!!!BREAKING] Refactor config:vhost_apache task. Start of docs for this task.
7)  [TASK] Rewrite log file creation for config:vhost_apache.
8)  [TASK] Change composer.json description. Remove psr-4 as no classes.
9)  [TASK] Add support for edge cases in config:vhost_apache task.
10) [TASK] Task config:vhost_apache - add more descriptive user messages for different missing data cases.
11) [TASK] Task config:vhost_apache - convert all files operation to runLocally / testLocally.
12) [TASK] Cleanup code on task `config:vhost_apache`
13) [FEATURE] Implement extended flags in buffer:start with flags that cleans php stat cache for specific amount of time,
    and lock which is doing redirects if it detects that it still pointing to old release.

## 7.1.3

1)  [DOCS] Update changelog.

## 7.1.2

1)  [BUGFIX] In task "buffer:start" add -f (force) to mv command as on some linux distro its asking to overwrite by default.

## 7.1.1

1)  [BUGFIX] Fix wrongly set default fetch_method for "php:clear_cache_http"
2)  [DOC] Update changelog.

## 7.1.0

1)  [FEATURE] Add curl as additional fetch_method.
2)  [FEATURE] Add fallback when fetch_method is not set.
3)  [BUGFIX] Change wget command to not store file at all. Previous settings causes wget to return error
    when there was no access to write on current folder. Right now there is no need to have write
    access.
4)  [BUGFIX] Do fallback for get('public_urls', []) so right exception is shown.
5)  [FEATURE] Introduce {{bin/local/wget}}
6)  [FEATURE] Introduce {{bin/local/curl}}
7)  [DOC] Extend documentation about task properties.

## 7.0.0

1)  [TASK] Add dependency to sourcebroker/deployer-loader
2)  [TASK][!!!BREAKING] Remove `\SourceBroker\DeployerExtended\Loader.php` in favour of using sourcebroker/deployer-loader
3)  [TASK][!!!BREAKING] Remove `\SourceBroker\DeployerExtended\Utility\FileUtility->requireFilesFromDirectoryReqursively`
    because it was used only in `\SourceBroker\DeployerExtended\Loader.php`

## 6.1.3

1)  [BUGFIX] Fix problem when few request want to delete the same file in buffer tasks.
2)  [TASK] Increase req for php to 5.6 as deployer does not work with php 5.4.

## 6.1.2

1)  Fix missing changelog.

## 6.1.1

1)  Fix hardcoded locker file name.
2)  Docs update.

## 6.1.0

1)  Add option to buffer:start to auto remove lock files after some time.
2)  Add option "entrypoint_refresh"

## 6.0.0

1)  Start entrypoint in task "buffer:start" and "buffer:stop" from deploy_path and not form web_path
2)  Remove not used var "tmp_dir".

## 5.1.0

1)  Rework of php:clear_cache_http. Look for old clear_cache file in previous release.

## 5.0.0

1)  Remove autoload of recipes. From now an object of class Loader must be created that will load
    the recipes.

## 4.0.0

Tasks removed with replacement in other package: <https://github.com/sourcebroker/deployer-extended-database>

1)  db:download
2)  db:export
3)  db:import
4)  db:move
5)  db:process
6)  db:pull
7)  db:truncate
8)  db:upload

Tasks removed with replacement in other package: <https://github.com/sourcebroker/deployer-extended-media>

1)  media:move
2)  media:pull
3)  media:push

## 3.0.0

Flatten structure of databases settings for database tasks.

Structure was:

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
              ['database_bar' => '/absolute/path/to/file/with/config_array.php']
          ]
      );

Should be now:

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
                  '/absolute/path/to/file/with/config_array.php'
              ],
              'database_bar' => [
                  get('db_default'),
                  '/absolute/path/to/file/with/config_array.php'
              ],
          ]
      );

All of the arrays in each database defined by key will be merged.

## 2.0.0

Task renamed:

1)  Rename deploy:composer_check_install to `deploy:check_composer_install`
2)  Rename cache:clearstatcache to `php:clear_cache_cli`
3)  Rename cache:frontendreset to `php:clear_cache_http`
4)  Rename deploy:vhosts to `config:vhost`

Task splitted/renamed with no simple replacement:

1)  file:remove_recursive_atomic - replaced by `file:rm2steps:1`, `file:rm2steps:2`
2)  lock:create_lock_files - replaced by `buffer:start`
3)  lock:delete_lock_files - replaced by `buffer:stop`
4)  lock:overwrite_entry_point - replaced by `buffer:start`

Task removed with no replacement:

1)  file:copy_from_shared
2)  file:copy_from_previous
3)  git:check_status
4)  lock:stop_if_http_status_200
