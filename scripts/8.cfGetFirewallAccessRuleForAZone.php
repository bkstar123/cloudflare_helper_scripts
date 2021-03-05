<?php
/**
 * Fetch the Firewall access rule for a zone
 *
 * @author: tuanha
 */
require(__DIR__.'/../bootstrap.php');

// Open file for writing the output in csv format, insert the field headers
$fh = fopen(__DIR__ . '/../output/cfFirewallAccessRules.csv', 'w');
fputcsv($fh, ['Target', 'Value', 'Mode', 'Paused', 'Note']);

$cfZoneFW = new CFBuddy\CFZoneFW();
$zoneMgmt = new CFBuddy\ZoneMgmt();
$zone = 'thedoctors.com';

$page = 1;
$perPage = 300;

$rules = $cfZoneFW->getFWAccessRules($zoneMgmt->getZoneID($zone), $page, 300);

foreach ($rules as $rule) {
	fputcsv($fh, [
		$rule['target'], 
		$rule['value'],
		$rule['mode'],
		$rule['paused'],
		$rule['notes']
	]);
}
fclose($fh);
