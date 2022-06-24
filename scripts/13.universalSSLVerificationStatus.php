<?php
/**
 * Fetch Universal SSL Verification Status for all Cloudflare zones
 *
 * @author: tuanha
 */
require(__DIR__.'/../bootstrap.php');

function toWildcardHostname($hostname)
{
    $hostname = idn_to_ascii(trim($hostname), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
    return substr_replace($hostname,'*.',0, strpos($hostname, ".") + 1);
}

// Open file for writing the output in csv format, insert the field headers
$fh = fopen(__DIR__ . '/../output/cfZoneUniversalSSLVerificationStatus.csv', 'w');
fputcsv($fh, ['Zone', 'Hostnames with inactive universal certificate', 'Note']);

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
        if (empty($packs)) {
            fputcsv($fh, [$zone['name'], '', 'There seem no certificate packs eligible for verification on this zone']);
        } else {
            $inactiveUniversalSSL = [];
            foreach ($packs as $pack) {
                if ($pack['certificate_status'] != 'active') {
                    array_push($inactiveUniversalSSL, $pack['hostname']);
                }
            }
            if (!empty($inactiveUniversalSSL)) {
                $customCertID = $customSSL->getCurrentCustomCertID($zone['id']);
                if (empty($customCertID)) {
                    $comment = 'No custom certificate found to cover all hostnames which are pending for renewing universal certificate';
                } else {
                    $data = $customSSL->fetchCertDataToArray($zone['id'], $customCertID);
                    $sanDomains = json_decode($data['hosts'], true);
                    $diffHostnames = array_merge([], array_diff($inactiveUniversalSSL, $sanDomains));
                    $isCoverbyCustomCert = true;
                    if (!empty($diffHostnames)) {
                        foreach ($diffHostnames as $hostname) {
                            if (!in_array(toWildcardHostname($hostname), $sanDomains)) {
                               $isCoverbyCustomCert = false;
                               break;
                            }
                        }
                    }
                    if ($isCoverbyCustomCert) {
                        $comment = "Can be safely ignored as all hostnamess are covered by a custom certificate";
                    } else {
                        $comment = "It is likely that the custom certificate on the zone does not cover all hostnames which are pending for univeral SSL renewal";
                    }
                }
            } else {
                $comment = 'No hostnames with inactive universal certificate on the zone';
            }
            fputcsv($fh, [$zone['name'], json_encode($inactiveUniversalSSL), $comment]);
        }
        print "Proceeded " . $zone['name'] . "\n";
    }
    ++$page;
} while (!empty($zones));

fclose($fh);
