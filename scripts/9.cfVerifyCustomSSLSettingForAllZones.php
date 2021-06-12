<?php
/**
 * Check how many custom SSL configurations are set for each zone
 *
 * @author: tuanha
 */
require(__DIR__.'/../bootstrap.php');

// Open file for writing the output in csv format, insert the field headers
$fh = fopen(__DIR__ . '/../output/cfZonesCustomSSLConfiguration.csv', 'w');
fputcsv($fh, ['Zone', 'Note']);

$zoneMgmt = new CFBuddy\ZoneMgmt();
$customSSL = new CFBuddy\CustomSSL();
$page = 1;
do {
    $zones = $zoneMgmt->getZones($page, 100);
    if (empty($zones)) {
        print "No more zone to proceed \n";
        break;
    }
    foreach ($zones as $zone) {
        print "Checking SSL mode for zone " . $zone['name'] . "\n";
        $currentCertID = $customSSL->getCurrentCustomCertID($zone['id']);
        if ($currentCertID === false) {
            fputcsv($fh, [
                'Zone' => $zone['name'],
                'Note' => 'Something is unusual, please manually double check in the Cloudflare portal'
            ]);
            continue;
        } elseif ($currentCertID === null) {
            fputcsv($fh, [
                'Zone' => $zone['name'],
                'Note' => 'No custom SSL setting'
            ]);
        } else {  
            fputcsv($fh, [
                'Zone' => $zone['name'],
                'Note' => '01 custom SSL setting detected'
            ]);
        }
        print "Proceeded " . $zone['name'] . "\n";
    }
    ++$page;
} while (!empty($zones));

fclose($fh);