<?php
/**
 * Upload a custom certificate to Cloudflare for zones
 *
 * @author: tuanha
 */
require(__DIR__.'/../bootstrap.php');

$cert = file_get_contents(__DIR__ . '/../input/' . $_ENV['CFUPLOAD_CERT']);
$key = file_get_contents(__DIR__.'/../input/' . $_ENV['CFUPLOAD_KEY']);
$list = file_get_contents(__DIR__.'/../input/' . $_ENV['CFUPLOAD_ZONE']);
$zones = explode(',', $list);

$customSSL = new CFBuddy\CustomSSL();
$zoneMgmt = new CFBuddy\ZoneMgmt();
$skippedZones = [];

foreach ($zones as $index => $zone) {
    $zone = idn_to_ascii(trim($zone), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
    print "........................$index. Processing the zone $zone......................\n";

    // Check zoneID
    $zoneID = $zoneMgmt->getZoneID($zone);
    if ($zoneID === null || $zoneID === false) {
        print "Failed to check the zone $zone details, skip it for now. Please manually verify on Cloudflare\n";
        array_push($skippedZones, $zone);
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
    } else {
        print "A custom certificate found. Removing the current certificate...\n";
        if ($customSSL->removeCurrentCert($zoneID, $currentCertID)) {
            print "The current custom certificate has been successfully removed\n";
        } else {
            print "An errror occured while trying to remove the current certificate. Please manually verify on Cloudflare\n";
            break;
        }
    }

    // Upload new certificate
    print "Start uploading new certificate...\n";
    if (!$customSSL->uploadNewCustomCert($zoneID, $cert, $key)) {
        print "Failed to upload a custom certificate for the zone $zone. Please manually verify on Cloudflare\n";
        break;
    }

    // Update progress
    print ceil(($index + 1)/count($zones)*100) . "% - Completed $zone\n";
}

if (!empty($skippedZones)) {
    print "The following zones were skipped, please manually verify their existence on Cloudflare\n";
    print json_encode($skippedZones) . "\n";
}
