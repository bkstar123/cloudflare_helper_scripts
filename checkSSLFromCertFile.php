<?php
/**
 * Display the certificate information from a .crt file
 *
 * @author: tuanha
 */
$certificate = file_get_contents(__DIR__ . '/input/' . $_ENV['CHECKSSL_CERTFILE']);

$ssl = openssl_x509_parse($certificate);

$validFrom = $ssl['validFrom_time_t'];
$validTo = $ssl['validTo_time_t'];

print "-------------Certificate data-----------------\n\n";
print "Common Name: " . $ssl['subject']['CN'] . "\n";
print "Valid from: " . (new DateTime("@$validFrom"))->format('Y-m-d H:i:s') . "\n";
print "Valid until: " . (new DateTime("@$validTo"))->format('Y-m-d H:i:s') . "\n";
print "Issuer: " . $ssl['issuer']['CN'] . "\n";
print "SAN: " . $ssl['extensions']['subjectAltName'] . "\n";
print "-----------------End-------------------------------\n";