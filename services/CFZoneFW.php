<?php
/**
 * Interact with Cloudflare API to manage firewall rule configuration for a zone
 *
 * @author: tuanha
 */
namespace CFBuddy;

use Exception;
use CFBuddy\CFServiceBase;

class CFZoneFW extends CFServiceBase
{
    /**
     * Create a new firewall rule for a zone
     *
     * @param string $zoneID
     * @param string $action
     * @param array $filter
     * @param string $description
     * @return boolean
     */
    public function createFirewallRule($zoneID, $action, $filter, $description)
    {
        $payload = [
            "action" => $action,
            "filter" => $filter,
            'description' => $description
        ];
        $url = "zones/$zoneID/firewall/rules";
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $_ENV['CF_BASE_URI'] . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => '['.json_encode($payload).']',
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "X-Auth-Key: {$_ENV['CF_API_KEY']}",
                "X-Auth-Email: {$_ENV['CF_API_EMAIL']}"
            ]
        ]);
        $result = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return false;
        } else {
            return json_decode($result)->success;
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
                        return [
                            'target' => $rule['configuration']['target'],
                            'value' => $rule['configuration']['value'],
                            'mode' => $rule['mode'],
                            'paused' => $rule['paused'],
                            'notes' => $rule['notes']
                        ];
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
     * Query Firewall rules for a zone by description
     *
     * @param string $zoneID
     * @param string $description
     * @return mixed (false | array)
     */
    public function queryFWRuleForZoneByDescription($zoneID, $description)
    {
        $queryString = http_build_query([
            'description' => $description
        ]);
        $url = "zones/$zoneID/firewall/rules?" . $queryString;
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                if (!empty($data['result'])) {
                    $rules = array_map(function ($rule) {
                        return [
                            'id' => $rule['id'],
                            'description' => $rule['description'],
                            'filter.expression' => $rule['filter']['expression'],
                            'filter.id' => $rule['filter']['id'],
                            'paused' => $rule['paused'],
                            'action' => $rule['action']
                        ];
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
     * Update a Firewall rule for a zone
     *
     * @param string $zoneID
     * @param string $ruleID
     * @param array $payload
     * @return boolean
     */
    public function updateFWRuleForZone($zoneID, $ruleID, $payload)
    {
        $payload = array_merge([
            'id' => $ruleID
        ], $payload);
        $url = "zones/$zoneID/firewall/rules";
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $_ENV['CF_BASE_URI'] . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => '['.json_encode($payload).']',
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "X-Auth-Key: {$_ENV['CF_API_KEY']}",
                "X-Auth-Email: {$_ENV['CF_API_EMAIL']}"
            ]
        ]);
        $result = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return false;
        } else {
            var_dump($result);die;
            return json_decode($result)->success;
        }
    }

    /**
     * Delete a Firewall rule for a zone
     *
     * @param string $zoneID
     * @param string $ruleID
     * @return boolean
     */
    public function deleteFWRuleForZone($zoneID, $ruleID)
    {
        $url = "zones/$zoneID/firewall/rules/$ruleID";
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
}
