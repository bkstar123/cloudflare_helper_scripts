<?php
/**
 * Get China Network Enabled Zones
 *
 * @author: tuanha
 */
require(__DIR__.'/../bootstrap.php');

$zoneMgmt = new CFBuddy\ZoneMgmt();
$jdcloudZones = [];

$page = 1;
do {
    $zones = $zoneMgmt->getZones($page, 1000);
    if (empty($zones)) {
        print "No more zone to proceed \n";
        break;
    }
    $data = array_filter($zones, function ($zone) {
        return isset($zone['betas']) && in_array('jdcloud_network_operational', $zone['betas']);
    });
    if (!empty($data)) {
        $data = array_merge([], array_map(function ($zone) {
            return $zone['name'];
        }, $data));
    }
    $jdcloudZones = array_merge($jdcloudZones, $data);
    print "Proceeded page " . $page . "\n";
    ++$page;
} while (!empty($zones));

file_put_contents(__DIR__ . '/../output/chinaNetworkCheck.txt', implode(",", $jdcloudZones));
