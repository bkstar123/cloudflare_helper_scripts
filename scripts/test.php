<?php

use CFBuddy\CFZoneFW;
use CFBuddy\ZoneMgmt;

require(__DIR__.'/../bootstrap.php');

$zoneMgmt = new ZoneMgmt();
$zoneFW = new CFZoneFW();

$zone = 'example.com';
$description = 'my rule';
$zoneID = $zoneMgmt->getZoneID($zone);


// Delete FW rule for a zone
if ($zoneID) {
  $rules = $zoneFW->queryFWRuleForZoneByDescription($zoneID, $description);
  if ($rules) {
    foreach ($rules as $rule) {
      $res = $zoneFW->deleteFWRuleForZone($zoneID, $rule['id']);
    }
  } else {
    print "No FW rule found for this zone";
  }
} else {
  print "No zone found";
}

// Update FW rule for a zone

// $newPayload = [
//   // "action" => 'block', // optional
//   "filter" => $filter ?? [],
//   // 'paused' => false, // optional
//   'description' => "tuan hoang" //optional
// ];


// if ($zoneID) {
//   $rules = $zoneFW->queryFWRuleForZoneByDescription($zoneID, $description);
//   if ($rules) {
//     foreach ($rules as $rule) {
//       $newPayload['filter']['id'] = $rule['filter.id'];
//       if (!isset($newPayload['description'])) {
//         $newPayload['description'] = $rule['description'];
//       }
//       if (!isset($newPayload['action'])) {
//         $newPayload['action'] = $rule['action'];
//       }
//       $res = $zoneFW->updateFWRuleForZone($zoneID, $rule['id'], $newPayload);
//       if (!$res) {
//         print "failed";
//       }
//     }
//   } else {
//     print "No FW rule found for this zone";
//   }
// } else {
//   print "No zone found";
// }

