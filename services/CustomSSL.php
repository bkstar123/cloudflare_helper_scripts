<?php
/**
 * Interact with Cloudflare API to manage custom SSL configuration
 *
 * @author: tuanha
 */
namespace CFBuddy;

use Exception;
use CFBuddy\ZoneMgmt;
use CFBuddy\CFServiceBase;
use Spatie\SslCertificate\SslCertificate;

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
                    return false; // Do not expect to see more than one custom certificate there, stop and manually verify on Cloudflare
                }
                return $data["result"][0]["id"]; // The Id of the current certificate
            } else {
                return false;
            }
        } catch (Exception $e) {
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
        $zoneMgmt = new ZoneMgmt();
        $sslMode = $zoneMgmt->getZoneSSLMode($zoneID);
        $url = "zones/$zoneID/custom_certificates/$certID";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                fputcsv($fh, [
                    $zone,
                    'true',
                    $data['result']['issuer'],
                    $sslMode ?? null,
                    $data['result']['uploaded_on'],
                    $data['result']['modified_on'],
                    $data['result']['expires_on'],
                    json_encode($data['result']['hosts'])
                ]);
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update an existing custom certificate for a zone
     *
     * @param string $zoneID
     * @param string $certID
     * @param string $cert
     * @param string $key
     * @return boolean
     */
    public function updateCustomCert($zoneID, $certID, $cert, $key)
    {
        $url = "zones/$zoneID/custom_certificates/$certID";
        $uploadData = [
            "certificate" => $cert,
            "private_key" => $key,
            "bundle_method" => "ubiquitous"
        ];
        $options = [
            'body' => json_encode($uploadData, JSON_UNESCAPED_SLASHES)
        ];
        try {
            $res = $this->client->request('PATCH', $url, $options);
            $data = json_decode($res->getBody()->getContents(), true);
            return $data["success"];
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param string $zoneID
     * @param string $certID
     * @param string $cert
     * @return array
     */
    public function preReplaceValidate($zoneID, $certID, $cert)
    {
        $url = "zones/$zoneID/custom_certificates/$certID";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                $oldSAN = $data['result']['hosts'];
                $certDomains = $this->getCertificateDomain($cert);
                $diff = array_merge([], array_diff($oldSAN, $certDomains));
                return [
                    'isOK' => empty($diff),
                    'diff' => $diff
                ];
            } else {
                return [
                    'isOK' => false,
                    'diff' => []
                ];
            }
        } catch (Exception $e) {
            return [
                'isOK' => false,
                'diff' => []
            ];
        }
    }

    /**
     * @param string $cert
     * @return array
     */
    protected function getCertificateDomain($cert)
    {
        try {
            $ssl = SslCertificate::createFromString($cert);
            return $ssl->getAdditionalDomains();
        } catch (Exception $e) {
            return [];
        }
    }
}
