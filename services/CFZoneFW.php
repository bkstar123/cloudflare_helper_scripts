<?php
/**
 * Interact with Cloudflare API to manage firewall rule configuration for a zone
 *
 * @author: tuanha
 */
namespace CFBuddy;

use Exception;
use CFBuddy\CFServiceBase;
use CFBuddy\CFFWRule\CFFWRule;
use CFBuddy\CFFWRule\CFFWRuleFilter;
use CFBuddy\CFFWAccessRule\CFFWAccessRule;

class CFZoneFW extends CFServiceBase
{
    /**
     * Create a new firewall rule for a zone
     *
     * @param string $zoneID
     * @param \CFBuddy\CFFWRule $rule
     * @return boolean
     */
    public function createFirewallRule($zoneID, $rule)
    {
        $options = [
            'body' => '[' . json_encode($rule->toArray()) . ']'
        ];
        $url = "zones/$zoneID/firewall/rules";
        try {
            $res = $this->client->request('POST', $url, $options);
            $data = json_decode($res->getBody()->getContents(), true);
            return $data['success'];
        } catch (Exception $e) {
            // var_dump($e->getResponse()->getBody(true)->getContents());
            return false;
        }
    }

    /**
     * Get Firewall rules for a zone by either rule description, or rule id, or rule ref
     *
     * @param string $zoneID
     * @param array $query
     * @return mixed (false | array of \CFBuddy\CFFWRule objects)
     */
    public function getFWRuleForZone($zoneID, array $query)
    {
        foreach (array_keys($query) as $key) {
            if (!in_array($key, ['description', 'id', 'ref'])) {
                throw new Exception("The second argument of the method getFWRuleForZone() must be an associative array contains one or more of following keys: 'description', id', 'ref'");
            }
        }
        $queryString = http_build_query($query);
        $url = "zones/$zoneID/firewall/rules?" . $queryString;
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                if (!empty($data['result'])) {
                    $rules = array_map(function ($rule) {
                        return new CFFWRule(
                            $rule['description'],
                            $rule['paused'],
                            new CFFWRuleFilter($rule['filter']['expression'], $rule['filter']['paused'], $rule['filter']['id']),
                            $rule['action'],
                            $rule['products'] ?? [],
                            $rule['id']
                        );
                    }, $data['result']);
                    return $rules;
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
     * Update a Firewall rule for a zone. It will not update the rule filter
     *
     * @param string $zoneID
     * @param \CFBuddy\CFFWRule $rule
     * @return boolean
     */
    public function updateFWRuleForZone($zoneID, $rule)
    {
        $options = [
            'body' => '[' . json_encode($rule->toArray()) . ']'
        ];
        $url = "zones/$zoneID/firewall/rules";
        try {
            $res = $this->client->request('PUT', $url, $options);
            $data = json_decode($res->getBody()->getContents(), true);
            return $data['success'];
        } catch (Exception $e) {
            // var_dump($e->getResponse()->getBody(true)->getContents());
            return false;
        }
    }

    /**
     * Update a Firewall rule's filter for a zone
     *
     * @param string $zoneID
     * @param \CFBuddy\CFFWRuleFilter $filter
     * @return boolean
     */
    public function updateFWRuleFilterForZone($zoneID, CFFWRuleFilter $filter)
    {
        $options = [
            'body' => '[' . json_encode($filter->toArray()) . ']'
        ];
        $url = "zones/$zoneID/filters";
        try {
            $res = $this->client->request('PUT', $url, $options);
            $data = json_decode($res->getBody()->getContents(), true);
            return $data['success'];
        } catch (Exception $e) {
            // var_dump($e->getResponse()->getBody(true)->getContents());
            return false;
        }
    }

    /**
     * Delete a Firewall rule for a zone, it will not delete the rule's filter
     *
     * @param string $zoneID
     * @param \CFBuddy\CFFWRule $rule
     * @return boolean
     */
    public function deleteFWRuleForZone($zoneID, $rule)
    {
        $url = "zones/$zoneID/firewall/rules?id=" . $rule->id;
        try {
            $res = $this->client->request('DELETE', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete a Firewall rule's filter for a zone
     *
     * @param string $zoneID
     * @param \CFBuddy\CFFWRuleFilter $filter
     * @return boolean
     */
    public function deleteFWRuleFilterForZone($zoneID, $filter)
    {
        $url = "zones/$zoneID/filters?id=" . $filter->id;
        try {
            $res = $this->client->request('DELETE', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get Firewall access rule for a zone
     *
     * @param string $zoneID
     * @param int $page
     * @param int $perPage
     * @return mixed (false | array)
     */
    public function getFWAccessRules($zoneID, $page, $perPage)
    {
        $url = "zones/$zoneID/firewall/access_rules/rules?page=$page&per_page=$perPage";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                if (!empty($data['result'])) {
                    $rules = array_map(function ($rule) {
                        return new CFFWAccessRule(
                            $rule['configuration']['target'],
                            $rule['configuration']['value'],
                            $rule['mode'],
                            $rule['paused'],
                            $rule['notes']
                        );
                    }, $data['result']);
                    return $rules;
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
}
