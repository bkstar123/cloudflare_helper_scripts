<?php
/**
 * Fetch Universal SSL Verification Status for all Cloudflare zones
 *
 * @author: tuanha
 */
require(__DIR__.'/../bootstrap.php');

// Open file for writing the output in csv format, insert the field headers
$fh = fopen(__DIR__ . '/../output/cfZoneUniversalSSLSettingStatus.csv', 'w');
fputcsv($fh, ['Zone', 'Universal SSL Enabled', 'CA']);

$zoneMgmt = new CFBuddy\ZoneMgmt();

$page = 1;
do {
    $zones = $zoneMgmt->getZones($page, 100);
    if (empty($zones)) {
        print "No more zone to proceed \n";
        break;
    }
    foreach ($zones as $zone) {
        print "Checking univeral SSL setting status for zone " . $zone['name'] . "\n";
        $result = $zoneMgmt->getUniversalSSLSettingStatus($zone['id']);
        if (empty($result)) {
            fputcsv($fh, [$zone['name'], '', '']);
        } else {
            fputcsv($fh, [
                $zone['name'], 
                $result['enabled'], 
                $result['certificate_authority']
            ]);
        }
    }
    ++$page;
} while (!empty($zones));

fclose($fh);
