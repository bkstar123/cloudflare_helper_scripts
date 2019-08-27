<?php
/**
 * Extract apex/root domains from a given list of hostnames
 *
 * @author: tuanha
 */
require(__DIR__.'/../bootstrap.php');

/**
 * Fetch the TLD data
 * source: https://publicsuffix.org/list/public_suffix_list.dat
 */
$TLDs = file_get_contents(__DIR__ . '/../input/5.tlds.txt');
$TLDs = explode(',', $TLDs);

// Fetch the list of domains to proceed
$domains = file_get_contents(__DIR__ . '/../input/' . $_ENV['FILTERAPEX_DOMAIN']);
$domains = explode(',', $domains);

// Open file to write output
$fh = fopen(__DIR__ . '/../output/' . $_ENV['FILTERAPEX_RESULT'], 'w');

$apexZones = [];

foreach ($domains as $index => $domain) {
    $domainParts = explode('.', trim($domain));

    $i = count($domainParts) - 1;
    $apexZone = $domainParts[$i];

    while ($i >= 0 && in_array($apexZone, $TLDs)) {
        --$i;
        $apexZone = $domainParts[$i].'.'.$apexZone;
    }

    $apexZones[] = $apexZone;

    // Update progress
    print ceil(($index + 1)/count($domains)*100) . "% - Completed $domain\n";
}
fputs($fh, implode(',', array_unique($apexZones)));
fclose($fh);
