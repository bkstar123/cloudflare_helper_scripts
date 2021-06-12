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
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get the SSL settings for a zone given by ID
     *
     * @param string $zoneID
     * @return mixed string|false|null
     */
    public function getZoneSSLMode($zoneID)
    {
        $url = "zones/$zoneID/settings/ssl";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                return $data["result"]["value"] ?? null;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get the list of Cloudflare zones
     *
     * @param integer $page
     * @param integer $perPage
     * @return mixed 
     */
    public function getZones($page = 1, $perPage = 100)
    {
        $zones = [];
        $url = "zones?per_page=$perPage&page=$page";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                if (!empty($data['result'])) {
                    $zones = array_map(function($zone) {
                        return [
                            'id' => $zone['id'],
                            'name' => $zone['name']
                        ];
                    }, $data['result']);
                    return $zones;
                } else {
                    return null;
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get the list of all sub domains for the given zone
     *
     * @param string $zoneID
     * @return array 
     */
    public function getZoneSubDomains($zoneID)
    {
        $zoneSubDomains = [];
        $page = 1;
        do {
            $data = $this->getZoneSubDomainsByPage($zoneID, $page, 100);
            if (empty($data)) {
                break;
            }
            $zoneSubDomains = array_merge($zoneSubDomains, $data);
            ++$page;
        } while (!empty($data));
        return $zoneSubDomains;
    }

    /**
     * Get the list of sub domains for the given zone by the given page
     *
     * @param string $zoneID
     * @param integer $page
     * @param integer $perPage
     * @return null|array 
     */
    protected function getZoneSubDomainsByPage($zoneID, $page = 1, $perPage = 100)
    {
        $subDomains = [];
        $url = "zones/$zoneID/dns_records?per_page=$perPage&page=$page";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                if (!empty($data['result'])) {
                    $dns_records = array_filter($data['result'], function($record) {
                        return ($record['type'] == 'CNAME' || $record['type'] == 'A') && 
                            !preg_match('/^awverify.*$/', $record['name']);
                    });
                    $subDomains = array_map(function($record) {
                        return $record['name'];
                    }, $dns_records);
                    return $subDomains;
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }
    }
}
