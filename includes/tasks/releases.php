<?php

namespace Deployer;

use Symfony\Component\Console\Helper\Table;

/**
 * This task solves performance problems of original Deployer "releases" task.
 * Read more at PR added https://github.com/deployphp/deployer/pull/4034
 *
 * Issue has been solved but will be available only in Deployer 8.
 *
 * If you still want it in Deployer 7 then here it is.
 *
 */
task('releases', function () {
    cd('{{deploy_path}}');

    $releasesLog = get('releases_log');
    $currentRelease = basename(run('readlink {{current_path}}'));
    $releasesList = get('releases_list');

    $table = [];
    $tz = !empty(getenv('TIMEZONE')) ? getenv('TIMEZONE') : date_default_timezone_get();

    foreach ($releasesLog as &$metainfo) {
        $date = \DateTime::createFromFormat(\DateTimeInterface::ISO8601, $metainfo['created_at']);
        $date->setTimezone(new \DateTimeZone($tz));
        $status = $release = $metainfo['release_name'];
        if (in_array($release, $releasesList, true)) {
            if (test("[ -f releases/$release/BAD_RELEASE ]")) {
                $status = "<error>$release</error> (bad)";
            } elseif (test("[ -f releases/$release/DIRTY_RELEASE ]")) {
                $status = "<error>$release</error> (dirty)";
            } else {
                $status = "<info>$release</info>";
            }
            try {
                $revision = run("cat releases/$release/REVISION");
            } catch (\Throwable $e) {
                $revision = 'unknown';
            }
        } else {
            $revision = 'unknown';
        }
        if ($release === $currentRelease) {
            $status .= ' (current)';
        }
        $table[] = [
            $date->format("Y-m-d H:i:s"),
            $status,
            $metainfo['user'],
            $metainfo['target'],
            $revision,
        ];
    }

    (new Table(output()))
        ->setHeaderTitle(currentHost()->getAlias())
        ->setHeaders(["Date ($tz)", 'Release', 'Author', 'Target', 'Commit'])
        ->setRows($table)
        ->render();
});