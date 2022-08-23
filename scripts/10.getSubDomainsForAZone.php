<?php
/**
 * Fetch sub domains for a given Cloudflare zone
 *
 * @author: tuanha
 */
require(__DIR__.'/../bootstrap.php');

// Open file for writing the output in csv format, insert the field headers
$fh = fopen(__DIR__ . '/../output/DnsRecords.txt', 'w');

$zoneMgmt = new CFBuddy\ZoneMgmt();
$zone = 'skanska.com';

$data = $zoneMgmt->getZoneSubDomains($zoneMgmt->getZoneID($zone));
fwrite($fh, json_encode($data));
fclose($fh);

print "Total " . count($data) . " sub domains. Done\n";
