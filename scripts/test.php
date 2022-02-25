<?php

use CFBuddy\CFZoneFW;
use CFBuddy\ZoneMgmt;
use CFBuddy\CFFWRule\CFFWRule;

require(__DIR__.'/../bootstrap.php');

$zoneMgmt = new ZoneMgmt();
$zoneFW = new CFZoneFW();

$zone = 'your.zone';
$description = 'your description';
$zoneID = $zoneMgmt->getZoneID($zone);


//Delete FW rule for a zone
// if ($zoneID) {
//     $rules = $zoneFW->getFWRuleForZone($zoneID, [
//     'description' => $description
//   ]);
//     if ($rules) {
//         foreach ($rules as $rule) {
//             $zoneFW->deleteFWRuleForZone($zoneID, $rule);
//             $zoneFW->deleteFWRuleFilterForZone($zoneID, $rule->filter);
//         }
//     } else {
//         print "No FW rule found for this zone";
//     }
// } else {
//     print "No zone found";
// }

// Update FW rule for a zone
if ($zoneID) {
  $rules = $zoneFW->getFWRuleForZone($zoneID, [
    'description' => $description
  ]);
  if ($rules) {
    foreach ($rules as $rule) {
      $rule->paused = true;
      $rule->filter->expression = "(http.request.uri.path contains \".php\") or (http.request.uri.path contains \"/wp-content/\") or (http.request.uri.path contains \"/wp-includes/\") or (http.user_agent contains \"Fuzz Faster U Fool\")";
      $zoneFW->updateFWRuleForZone($zoneID, $rule);
      $zoneFW->updateFWRuleFilterForZone($zoneID, $rule->filter);
    }
  } else {
    print "No FW rule found for this zone";
  }
} else {
  print "No zone found";
}
