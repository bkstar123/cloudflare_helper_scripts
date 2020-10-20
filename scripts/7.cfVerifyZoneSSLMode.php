<?php
/**
 * Fetch the custom SSL configuration for Cloudflare zones
 *
 * @author: tuanha
 */
require(__DIR__.'/../bootstrap.php');

// Open file for writing the output in csv format, insert the field headers
$fh = fopen(__DIR__ . '/../output/cfZonesSSLMode.csv', 'w');
fputcsv($fh, ['Zone', 'SSL mode']);

$zoneMgmt = new CFBuddy\ZoneMgmt();
$skippedZones = [];

$page = 1;
do {
    $zones = $zoneMgmt->getZones($page, 100);
    if (empty($zones)) {
        print "No more zone to proceed \n";
        break;
    }
    foreach ($zones as $zone) {
        print "Checking SSL mode for zone " . $zone['name'] . "\n";
        $sslMode = $zoneMgmt->getZoneSSLMode($zone['id']);
        if (!$sslMode) {
            print "Failed to check SSL mode for zone $zoneName";
            array_push($skippedZones, $zone['name']);
            fputcsv($fh, [$zone['name'], '']);
            continue;
        } else {
            fputcsv($fh, [$zone['name'], $sslMode]);
        }
        print "Proceeded " . $zone['name'] . "\n";
    }
    ++$page;
} while (!empty($zones));

fclose($fh);

if (!empty($skippedZones)) {
    print "Please manually verify the following zones on Cloudflare\n";
    print json_encode($skippedZones) . "\n";
}
