<?php

namespace Deployer;

task('lock:stop_if_http_status_200', function(){
    $statusCode = '';
    if ('wget' == get('fetch_method')) {
        $statusCode = runLocally("wget --no-check-certificate  --header='X-DEPLOYMENT:{{random}}' -SO- -T15 -t1 '{{stage_url}}' 2>&1 | grep 'HTTP/' | awk '{print $2}' | tail -1",15)->toString();
    } elseif ('file_get_contents' == get('fetch_method')) {
        $statusCode = runLocally('php -r \'file_get_contents("{{stage_url}}", false, stream_context_create(array("http"=>array("header"=>"X-DEPLOYMENT:{{random}}","timeout"=>15))));foreach($http_response_header as $header){preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#",$header,$out);if(intval($out[1]) > 0) {echo intval($out[1]) ; exit;}};\'',15)->toString();
    }

    if (200 == intval($statusCode)) {
        # remove files from new release
        run('cd {{release_path}} && rm -f {{web_path}}deployment.lock');
        run('cd {{release_path}} && rm -f {{web_path}}{{deploy_lock_filename}}');

        # remove files from previous release
        $releasesList = get('releases_list');
        if (isset($releasesList[1])) {
            run('cd {{deploy_path}} && rm -f releases/' . $releasesList[1] . '/{{web_path}}deployment.lock');
            run('cd {{deploy_path}} && rm -f releases/' . $releasesList[1] . '/{{web_path}}{{deploy_lock_filename}}');
        }
    } else {
        $formatter = Deployer::get()->getHelper('formatter');
        $errorMessage = [
            "Status code on smoke test was not 200 but ".trim($statusCode)
        ];
        writeln($formatter->formatBlock($errorMessage, 'error', true));
    }

})->desc('Lock stop if http status is 200');


