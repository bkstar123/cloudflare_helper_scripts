<?php
/**
 * Fetch all CF-proxied hostnames
 *
 * @author: tuanha
 * @date: 22-Aug-2022
 */
require(__DIR__.'/../bootstrap.php');

// Open file for writing the output in csv format, insert the field headers
$fh = fopen(__DIR__ . '/../output/cfProxiedHostnames.txt', 'a');
$zoneMgmt = new CFBuddy\ZoneMgmt();
$accountID = '704f203b6ac1e0ffb5c6d8b6fc20ba71'; // DXP account
$page = 1;
do {
    print "Fetch page - " . $page . "\n";
    $zones = $zoneMgmt->getZones($page, 100, '', $accountID);
    if (empty($zones)) {
        print "No more zone to proceed \n";
        break;
    }
    foreach ($zones as $zone) {
        print "Checking hostnames for zone " . $zone['name'] . "\n";
        $entries = $zoneMgmt->getZoneSubDomains($zone['id'], null, false, false);
        if (!empty($entries)) {
            fwrite($fh, implode("\n", $entries));
            fwrite($fh, "\n");
        }
    }
    ++$page;
} while (!empty($zones));
fclose($fh);
print "Done\n";
