<?php
/**
 * Fetch all CF-proxied hostnames
 *
 * @author: tuanha
 * @date: 22-Aug-2022
 */
require(__DIR__.'/../bootstrap.php');

// Open file for writing the output in csv format, insert the field headers
$fh = fopen(__DIR__ . '/../output/cfProxiedHostnames4.txt', 'a');
$zoneMgmt = new CFBuddy\ZoneMgmt();
$page = 1;
do {
    print "Fetch page - " . $page . "\n";
    $zones = $zoneMgmt->getZones($page, 100);
    if (empty($zones)) {
        print "No more zone to proceed \n";
        break;
    }
    foreach ($zones as $zone) {
        print "Checking hostnames for zone " . $zone['name'] . "\n";
        $entries = $zoneMgmt->getZoneSubDomains($zone['id'], null, false, true);
        if (!empty($entries)) {
            fwrite($fh, implode("\n", $entries));
            fwrite($fh, "\n");
        }
    }
    ++$page;
} while (!empty($zones));
fclose($fh);
print "Done\n";
