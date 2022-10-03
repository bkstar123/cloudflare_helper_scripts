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
     * Get the paginated list of Cloudflare zones for the your account
     *
     * @param integer $page
     * @param integer $perPage
     * @return array|false
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
                    $zones = array_map(function ($zone) {
                        return [
                            'id' => $zone['id'],
                            'name' => $zone['name']
                        ];
                    }, $data['result']);
                    return $zones;
                } else {
                    return [];
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
    public function getZoneSubDomains($zoneID, $longString = false, $onlyProdDomains = true)
    {
        $zoneSubDomains = [];
        $page = 1;
        do {
            $data = $this->getZonePaginatedSubDomains($zoneID, $longString, $onlyProdDomains, $page, 100);
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
     * @return array|false
     */
    protected function getZonePaginatedSubDomains($zoneID, $longString, $onlyProdDomains, $page = 1, $perPage = 100)
    {
        $subDomains = [];
        $url = "zones/$zoneID/dns_records?per_page=$perPage&page=$page";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                if (!empty($data['result'])) {
                    $dns_records = array_filter($data['result'], function ($record) use ($onlyProdDomains) {
                        if ($onlyProdDomains) {
                            return ($record['type'] == 'CNAME' && stristr($record['content'], 'episerver.net') && stristr($record['content'], 'prod.')) || $record['type'] == 'A';
                        } else {
                            return ($record['type'] == 'CNAME' && stristr($record['content'], 'episerver.net')) || $record['type'] == 'A';
                        }
                    });
                    $subDomains = array_map(function ($record) use ($longString) {
                        if ($longString) {
                            return $record['name'] . "," . $record['type'] . "," . $record['content'];
                        } else {
                            return $record['name'];
                        }
                    }, $dns_records);
                    return $subDomains;
                } else {
                    return [];
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get DNS CNAME record for a custom domain of the given zone
     *
     * @param $zoneID string
     * @param $name string
     *
     * @return string
     */
    public function getCFDnsCnameForACustomDomain($zoneID, $name)
    {
        $url = "zones/$zoneID/dns_records?name=$name";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                if (!empty($data['result'])) {
                    $dns_records = array_filter($data['result'], function ($record) {
                        return $record['type'] == 'CNAME';
                    });
                    return $dns_records[0]['content'];
                } else {
                    return '';
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get universal SSL verification statuses for hostnames of the given zone
     *
     * @param $zoneID string
     *
     * @return false|null|array
     */
    public function getUniversalSSLVerificationStatus($zoneID)
    {
        $url = "zones/$zoneID/ssl/verification";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                return $data['result'];
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }
}
