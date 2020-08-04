<?php
/**
 * Manage Clouflare zone
 *
 * @author: tuanha
 */
namespace CFBuddy;

use Exception;
use GuzzleHttp\Client;
use CFBuddy\CFServiceBase;

class ZoneMgmt extends CFServiceBase
{
    /**
     * Get the ID of the given zone name
     *
     * @param string $zoneName
     * @return mixed null|false|string
     */
    public function getZoneID($zoneName)
    {
        $url = "zones?name=$zoneName&status=active";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                if (empty($data["result"])) {
                    return null; // zone not found
                } elseif (count($data["result"]) > 1) {
                    return false; // duplicated zoneID found for the given zone name
                }
                return $data["result"][0]["id"]; // The Id of the given zone name
            }
            print "Cannot check for the zone $zoneName details due to an unknown reason from Cloudflare\n";
            return false;
        } catch (Exception $e) {
            print "Failed to make request to Cloudflare\n";
            print "************\nError: {$e->getMessage()}************\n";
            return false;
        }
    }
}
