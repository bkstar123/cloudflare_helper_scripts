<?php
/**
 * Interact with Cloudflare API to manage custom SSL configuration
 *
 * @author: tuanha
 */
namespace CFBuddy;

use Exception;
use CFBuddy\CFServiceBase;

class CustomSSL extends CFServiceBase
{
    /**
     * Get the ID of the current SSL certificate for the given zone
     *
     * @param string $zoneID
     * @return mixed null|false|string
     */
    public function getCurrentCustomCertID($zoneID)
    {
        $url = "zones/$zoneID/custom_certificates?status=active";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                if (empty($data["result"])) {
                    return null; // No existing certs found
                } elseif (count($data["result"]) > 1) {
                    print "There are more than one custom certificate installed for the given zone\n";
                    return false; // Do not expect to see more than one custom certificate there, stop and manually verify on Cloudflare
                }
                return $data["result"][0]["id"]; // The Id of the current certificate
            } else {
                print "Cannot check the current SSL configuration for the given zone due to unknown reason from Cloudflare\n";
                return false;
            }
        } catch (Exception $e) {
            print "Failed to make request to the Cloudflare\n";
            print "************\nError: {$e->getMessage()}************\n";
            return false;
        }
    }

    /**
     * Upload new custom certificate for a zone
     *
     * @param string $zoneID
     * @param string $cert
     * @param string $key
     * @return boolean
     */
    public function uploadNewCustomCert($zoneID, $cert, $key)
    {
        $url = "zones/$zoneID/custom_certificates";
        $uploadData = [
            "certificate" => $cert,
            "private_key" => $key,
            "bundle_method" => "ubiquitous"
        ];
        $options = [
            'body' => json_encode($uploadData, JSON_UNESCAPED_SLASHES)
        ];
        try {
            $res = $this->client->request('POST', $url, $options);
            $data = json_decode($res->getBody()->getContents(), true);
            return $data["success"];
        } catch (Exception $e) {
            print "Failed to make request to the Cloudflare\n";
            print "************\nError: {$e->getMessage()}************\n";
            return false;
        }
    }

    /**
     * Remove existing certificate for the given zone
     *
     * @param string $zoneID
     * @param string $ceertID
     * @return boolean
     */
    public function removeCurrentCert($zoneID, $certID)
    {
        $url = "zones/$zoneID/custom_certificates/$certID";
        try {
            $res = $this->client->request("DELETE", $url);
            $data = json_decode($res->getBody()->getContents(), true);
            return $data['success'];
        } catch (Exception $e) {
            print "Failed to make request to the Cloudflare\n";
            print "************\nError: {$e->getMessage()}************\n";
            return false;
        }
    }
    
    /**
     * Fetch the current custom SSL certificate details for the given zone
     * and write the result to a file given by its resource handler $fh
     *
     * @param string $zone
     * @param string $zoneID
     * @param string $certID
     * @param resource $fh
     */
    public function fetchCertData($zone, $zoneID, $certID, $fh)
    {
        $url = "zones/$zoneID/custom_certificates/$certID";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                fputcsv($fh, [
                    $zone,
                    $data['result']['issuer'],
                    $data['result']['expires_on'],
                    json_encode($data['result']['hosts'])
                ]);
                return true;
            } else {
                print "Cannot check the current SSL configuration for the given zone due to unknown reason from Cloudflare\n";
                return false;
            }
        } catch (Exception $e) {
            print "Failed to make request to the Cloudflare\n";
            print "************\nError: {$e->getMessage()}************\n";
            return false;
        }
    }
}
