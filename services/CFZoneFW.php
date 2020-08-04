<?php
/**
 * Interact with Cloudflare API to manage zone firewall rule configuration
 *
 * @author: tuanha
 */
namespace CFBuddy;

use Exception;

class CFZoneFW
{
    /**
     * Upload new custom certificate for a zone
     *
     * @param string $zoneID
     * @param string $cert
     * @param string $key
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
            print "Failed to make request to the Cloudflare\n";
            print "************\nError: {$err}************\n";
            return false;
        } else {
            return json_decode($result)->success;
        }
    }
}
