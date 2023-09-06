<?php
/**
 * Get China Network Enabled Zones
 *
 * @author: tuanha
 */
require(__DIR__.'/../bootstrap.php');

$zoneMgmt = new CFBuddy\ZoneMgmt();
$fullZones = [];

$page = 1;
do {
    $zones = $zoneMgmt->getZones($page, 1000);
    if (empty($zones)) {
        print "No more zone to proceed \n";
        break;
    }
    $data = array_filter($zones, function ($zone) {
        return isset($zone['type']) && $zone['type'] != 'partial';
    });
    if (!empty($data)) {
        $data = array_merge([], array_map(function ($zone) {
            return $zone['name'];
        }, $data));
    }
    $fullZones = array_merge($fullZones, $data);
    print "Proceeded page " . $page . "\n";
    ++$page;
} while (!empty($zones));

file_put_contents(__DIR__ . '/../output/fullZones.txt', implode(",", $fullZones));
