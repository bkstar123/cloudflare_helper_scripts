<?php
/**
 * Fetch the SSL certificate information from URL
 *
 * @author: tuanha
 */
require(__DIR__.'/../bootstrap.php');

$list = file_get_contents(__DIR__ . '/../input/' . $_ENV['CHECKSSL_DOMAIN']);
$domains = explode(',', $list);
$fh = fopen(__DIR__ . '/../output/' . $_ENV['CHECKSSL_RESULT'], 'w');
fputcsv($fh, ['URL', 'Issuer', 'Valid_from', 'Expired_at', 'CN', 'Fingerprint', 'Remaining_days', 'Point_to_IP', 'Alias_to', 'SAN']);
foreach ($domains as $index => $domain) {
    $domain = idn_to_ascii(trim($domain), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
    $IPs = [];
    $Aliases = [];
    try {
        $a_records = dns_get_record($domain, DNS_A);
    } catch (Exception $e) {
        $a_records = [];
    }
    try {
        $cname_records = dns_get_record($domain, DNS_CNAME);
    } catch (Exception $e) {
        $cname_records = [];
    }
    if (!empty($a_records)) {
        foreach ($a_records as $record) {
            array_push($IPs, $record['ip']);
        }
    }
    if (!empty($cname_records)) {
        foreach ($cname_records as $record) {
            array_push($Aliases, $record['target']);
        }
    }
    try {
        $cert = Spatie\SslCertificate\SslCertificate::createForHostName($domain);
        fputcsv($fh, [
            $domain,
            $cert->getIssuer(),
            $cert->validFromDate(),
            $cert->expirationDate(),
            $cert->getDomain(),
            $cert->getFingerprint(),
            $cert->daysUntilExpirationDate(),
            json_encode($IPs),
            json_encode($Aliases),
            json_encode($cert->getAdditionalDomains()),
        ]);
    } catch (Exception $e) {
        fputcsv($fh, [$domain, '', '', '', '', '', '', json_encode($IPs), json_encode($Aliases), '']);
    }
    print ceil(($index + 1)/count($domains)*100). "% - Completed $domain\n";
}
fclose($fh);
