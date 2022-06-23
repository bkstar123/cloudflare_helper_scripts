<?php
/**
 * Fetch Universal SSL Verification Status for all Cloudflare zones
 *
 * @author: tuanha
 */
require(__DIR__.'/../bootstrap.php');

// Open file for writing the output in csv format, insert the field headers
$fh = fopen(__DIR__ . '/../output/cfZoneUniversalSSLVerificationStatus.csv', 'w');
fputcsv($fh, ['Zone', 'Active', 'Pending Issuance', 'Pending Validation', 'Validation Timeout', 'Note']);

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
        print "Checking univeral SSL verification status for zone " . $zone['name'] . "\n";
        $packs = $zoneMgmt->getUniversalSSLVerificationStatus($zone['id']);
        if ($packs === false) {
            fputcsv($fh, [$zone['name'], '', '', '', '', 'There seem no certificate packs eligible for verification on this zone']);
        } else if ($packs === null) {
            fputcsv($fh, [$zone['name'], '', '', '', '', 'There seem no certificate packs eligible for verification on this zone']);
        } else {
            $activeUniversalSSL = [];
            $pendingUniversalSSLIssuance = [];
            $pendingUniversalSSLValidation = [];
            $universalSSLValidationTimeout = [];
            foreach ($packs as $pack) {
                if ($pack['certificate_status'] == 'active') {
                    array_push($activeUniversalSSL, $pack['hostname']);
                } else if ($pack['certificate_status'] == 'pending_issuance') {
                    array_push($pendingUniversalSSLIssuance, $pack['hostname']);
                } else if ($pack['certificate_status'] == 'pending_validation') {
                    array_push($pendingUniversalSSLValidation, $pack['hostname']);
                } else if ($pack['certificate_status'] == 'validation_timed_out') {
                    array_push($universalSSLValidationTimeout, $pack['hostname']);
                }
            }
            $comment = '';
            if (!empty($pendingUniversalSSLIssuance) || !empty($pendingUniversalSSLValidation) || !empty($universalSSLValidationTimeout)) {
                $customCertID = $customSSL->getCurrentCustomCertID($zone['id']);
                if (empty($customCertID)) {
                    $comment = 'No custom certificate found to cover hostnames which are pending for validation or issuance';
                } else {
                    $data = $customSSL->fetchCertDataToArray($zone['id'], $customCertID);
                    $sanDomains = json_decode($data['hosts'], true);
                    $diff1 = array_merge([], array_diff($pendingUniversalSSLValidation, $sanDomains));
                    $diff2 = array_merge([], array_diff($pendingUniversalSSLIssuance, $sanDomains));
                    $diff3 = array_merge([], array_diff($universalSSLValidationTimeout, $sanDomains));
                    if (!empty($diff1) || !empty($diff2) || !empty($diff3)) {
                        $comment = "It is likely that the custom certificate on the zone does not cover all hostnames which are pending for univeral SSL renewal";
                    }
                }
            }
            fputcsv($fh, [$zone['name'], json_encode($activeUniversalSSL), json_encode($pendingUniversalSSLIssuance), json_encode($pendingUniversalSSLValidation), json_encode($universalSSLValidationTimeout), $comment]);
        }
        print "Proceeded " . $zone['name'] . "\n";
    }
    ++$page;
} while (!empty($zones));

fclose($fh);
