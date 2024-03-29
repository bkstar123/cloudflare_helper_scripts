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
        print "Found some issues with the zone $zone while checking its current SSL configuration. Please manualy verify it on Cloudflare\n";
        break;
    } elseif ($currentCertID === null) {
        print "No existing certificate found for the zone $zone\n";
        print "Uploading certificate for the zone $zone\n";
        if (!$customSSL->uploadNewCustomCert($zoneID, $cert, $key)) {
            print "Failed to upload a custom certificate for the zone $zone. Please manually verify it on Cloudflare\n";
            break;
        }
    } else {
        print "An existing SSL configuration found. Updating the current certificate...\n";
        $validate = $customSSL->preReplaceValidate($zoneID, $currentCertID, $cert);
        if ($validate['isOK']) {
            if (!$customSSL->updateCustomCert($zoneID, $currentCertID, $cert, $key)) {
                print "Failed to update the existing SSL configuration for the zone $zone. Please manually verify it on Cloudflare\n";
                break;
            }
        } else {
            print "Not OK to replace ssl for this zone.\n";
            print "Verify if the certificate covers the following domains: " . json_encode($validate['diff']) . "\n";
        }
    }
    // Update progress
    print ceil(($index + 1)/count($zones)*100) . "% - Completed $zone\n";
}

if (!empty($skippedZones)) {
    print "The following zones were skipped, please manually verify their existence on Cloudflare\n";
    print json_encode($skippedZones) . "\n";
}
