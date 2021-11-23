<?php
/**
 * Fetch CF Cname record for a custom domain under a CF zone
 *
 * @date: 23-Nov-2021
 * @author: tuanha
 */
require(__DIR__.'/../bootstrap.php');

function extractRootDomain($domain)
{
    $TLDs = file_get_contents(__DIR__ . '/../input/5.tlds.txt');
    $TLDs = explode(',', $TLDs);
    $domainParts = explode('.', trim($domain));
    $i = count($domainParts) - 1;
    $apexZone = $domainParts[$i];
    while ($i >= 0 && in_array($apexZone, $TLDs)) {
        --$i;
        $apexZone = $domainParts[$i].'.'.$apexZone;
    }
    return $apexZone;
}

// Open file for writing the output in csv format, insert the field headers
$fh = fopen(__DIR__ . '/../output/CFDnsCnameRecords.csv', 'w');
fputcsv($fh, ['Type', 'Name', 'Content']);
$zoneMgmt = new CFBuddy\ZoneMgmt();
$list = file_get_contents(__DIR__ . '/../input/11.domains.txt');
$domains = explode(',', $list);
foreach ($domains as $index => $domain) {
    $zone = extractRootDomain($domain);
    $record = $zoneMgmt->getCFDnsCnameForACustomDomain($zoneMgmt->getZoneID($zone), $domain);
    fputcsv($fh, [
    'CNAME',
     $domain,
     $record
  ]);
    print ceil(($index + 1)/count($domains)*100). "% - Completed $domain\n";
}
fclose($fh);
