<?php
/**
 * Create firewall rules for CF zones
 *
 * @author: tuanha
 */
require(__DIR__.'/../bootstrap.php');

$config = require(__DIR__.'/../input/6.cffirewall_rule_params.php');

$list = file_get_contents(__DIR__.'/../input/' . $_ENV['CFFWRULE_ZONE']);
$zones = explode(',', $list);

$zoneMgmt = new CFBuddy\ZoneMgmt();
$zoneFW = new CFBuddy\CFZoneFW();
$skippedZones = [];

$action = $config['action'];
$filter = [
    'expression' => $config['expression'],
    'paused' =>  false
];
$description = $config['description'];

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

    // Create new firewall rule
    print "Start creating new firewall rule...\n";
    if (!$zoneFW->createFirewallRule($zoneID, $action, $filter, $description)) {
        print "Failed to create the firewall rule for the zone $zone, skip it for now. Please manually verify on Cloudflare\n";
        array_push($skippedZones, $zone);
        continue;
    }

    // Update progress
    print ceil(($index + 1)/count($zones)*100) . "% - Completed $zone\n";
}

if (!empty($skippedZones)) {
    print "The following zones were skipped, please manually verify their existence on Cloudflare\n";
    print json_encode($skippedZones) . "\n";
}
