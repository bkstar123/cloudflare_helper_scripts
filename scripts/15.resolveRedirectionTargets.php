<?php
/**
 * Resolve Redirection Targets
 *
 * @author: tuanha
 * @date: 22-Aug-2022
 */
require(__DIR__.'/../bootstrap.php');

function resolveRedirectTargets($hostname)
{
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $hostname,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "cache-control: no-cache"
        ],
    ]);
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_NOBODY, 1);
    $res = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        return false;
    } else {
        if (!empty(explode("\r\n", stristr($res, 'Location:'))[0])) {
            return trim(explode(": ", explode("\r\n", stristr($res, 'Location:'))[0])[1]);
        } else {
            return '';
        }
    }
}
$list = file_get_contents(__DIR__ . '/../input/15.domains_to_browse.txt');
$hostnames = explode("\n", $list);
$hostnames = array_filter($hostnames, function ($hostname) {
    return !empty($hostname);
});
$fh = fopen(__DIR__ . '/../output/domain_browsing_result.csv', 'w');
fputcsv($fh, ['Hostname', 'Redirect To']);
foreach ($hostnames as $hostname) {
    $targets = [];
    $target = resolveRedirectTargets($hostname);
    while (!empty($target)) {
        array_push($targets, $target);
        $target = resolveRedirectTargets($target);
    }
    fputcsv($fh, [
        "http://$hostname",
        !empty($targets) ? implode(" => ", $targets) : ''
    ]);
    print "Completed $hostname\n";
}
fclose($fh);
