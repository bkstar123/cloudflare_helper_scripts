<?php
/**
 * Fetch the DNS A/CNAME records for a givem Cloudflare zone
 *
 * @author: tuanha
 */
require(__DIR__.'/../bootstrap.php');

// Open file for writing the output in csv format, insert the field headers
$fh = fopen(__DIR__ . '/../output/DnsRecords.txt', 'w');

$zoneMgmt = new CFBuddy\ZoneMgmt();
$result = [];
$zone = 'skanska.com';

$page = 1;
do {
    $records = $zoneMgmt->getDnsRecords($zoneMgmt->getZoneID($zone), $page, 100);
    if (empty($records)) {
        print "No more records to fetch \n";
        break;
    }
    $result = array_merge($result, $records);
    ++$page;
} while (!empty($records));
fwrite($fh, json_encode($result));
fclose($fh);

print "Total " . count($result) . " records. Done\n";
