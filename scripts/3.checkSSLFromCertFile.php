<?php
/**
 * Display the certificate information from a .crt file
 *
 * @author: tuanha
 */
require(__DIR__.'/../bootstrap.php');

use Spatie\SslCertificate\SslCertificate;

$certificate = file_get_contents(__DIR__ . '/../input/' . $_ENV['CHECKSSL_CERTFILE']);
$ssl = SslCertificate::createFromString($certificate);
$msgFormat = "CN: %s,\nOrganization: %s,\nOrganization Unit: %s,\nLocality: %s,\nState: %s,\nCountry: %s,\nValid from: %s,\nValid until: %s,\nIssuer: %s,\nSAN: %s\n";
    $msg = sprintf(
        $msgFormat,
        $ssl->getDomain(),
        $ssl->getOrganization(),
        $ssl->getRawCertificateFields()['subject']['OU'] ?? '',
        $ssl->getRawCertificateFields()['subject']['L'] ?? '',
        $ssl->getRawCertificateFields()['subject']['ST'] ?? '',
        $ssl->getRawCertificateFields()['subject']['C'] ?? '',
        $ssl->validFromDate(),
        $ssl->expirationDate(),
        $ssl->getIssuer(),
        implode(',', $ssl->getAdditionalDomains())
    );
print $msg;
