<?php
/**
 * Fetch the custom SSL configuration for Cloudflare zones
 *
 * @author: tuanha
 */
require(__DIR__.'/../bootstrap.php');

$list = file_get_contents(__DIR__ . '/../input/' . $_ENV['CFCHECK_ZONE']);
$zones = explode(',', $list);

// Open file for writing the output in csv format, insert the field headers
$fh = fopen(__DIR__ . '/../output/' . $_ENV['CFCHECK_RESULT'], 'w');
fputcsv($fh, ['URL', 'Found on Cloudflare', 'Issuer', 'Expired_at', 'Hosts']);

$customSSL = new CFBuddy\CustomSSL();
$zoneMgmt = new CFBuddy\ZoneMgmt();

foreach ($zones as $index => $zone) {
    $zone = trim($zone);
    print "........................$index. Processing the zone $zone......................\n";

    // Check zoneID
    $zoneID = $zoneMgmt->getZoneID($zone);
    if ($zoneID === null || $zoneID === false) {
        print "Failed to check the zone $zone details, skip it for now. Please manually verify on Cloudflare\n";
        fputcsv($fh, [$zone, 'false', '', '', '']);
        continue;
    }

    /*
     * Check the current custom SSL certificate for the given zoneID
     * The script will stop if it cannot check for the current custom certificate for any reason, or see 2 custom cerificates exist there
     * The script will remove the current certificate if there is only one there
     */
    $currentCertID = $customSSL->getCurrentCustomCertID($zoneID);
    if ($currentCertID === false) {
        print "Found some issue with the zone $zone while checking its current SSL configuration. Please manualy verify on Cloudflare\n";
        break;
    } elseif ($currentCertID === null) {
        print "No current certificate found for the zone $zone\n";
        fputcsv($fh, [$zone, 'true', '', '', '']);
    } else {
        print "A custom certificate found. Fetching its data...\n";
        if (!$customSSL->fetchCertData($zone, $zoneID, $currentCertID, $fh)) {
            print "Failed to fetch the current certificate data for the zone $zone due to an error. Please manually verify on Cloudflare\n";
            break;
        }
    }
    // Update progress
    print ceil(($index + 1)/count($zones)*100) . "% - Completed $zone\n";
}

fclose($fh);
