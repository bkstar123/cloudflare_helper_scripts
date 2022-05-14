<?php
/**
 * Convert json log to csv
 *
 * @author: tuanha
 */
require(__DIR__.'/../bootstrap.php');

$fip = fopen(__DIR__ . '/../input/12.http_log.json', 'r');
$fop = fopen(__DIR__ . '/../output/http_log.csv', 'w');
fputcsv($fop, [
    'Category', 
    'Time', 
    'Resource ID', 
    'EventStampType', 
    'EventPrimaryStampName', 
    'EventStampName', 
    'Host', 
    'EventIpAddress', 
    'UserAgent',
    'Cookie',
    'ScStatus',
    'CsUsername',
    'Result',
    'CsHost',
    'CsMethod',
    'CsBytes',
    'CIp',
    'SPort',
    'Referer',
    'CsUriStem',
    'TimeTaken',
    'ScBytes',
    'ComputerName'
]);

$index = 0;
if ($fip) {   
    while (!feof($fip)) {
        $line = fgets($fip);
        if ($line) {
            $lineArray = json_decode($line, true);
            $properties = json_decode($lineArray['properties'], true);
            fputcsv($fop, [
                $lineArray['category'],
                $lineArray['time'],
                $lineArray['resourceId'],
                $lineArray['EventStampType'],
                $lineArray['EventPrimaryStampName'],
                $lineArray['EventStampName'],
                $lineArray['Host'],
                $lineArray['EventIpAddress'],
                $properties['UserAgent'],
                $properties['Cookie'],
                $properties['ScStatus'],
                $properties['CsUsername'],
                $properties['Result'],
                $properties['CsHost'],
                $properties['CsMethod'],
                $properties['CsBytes'],
                $properties['CIp'],
                $properties['SPort'],
                $properties['Referer'],
                $properties['CsUriStem'],
                $properties['TimeTaken'],
                $properties['ScBytes'],
                $properties['ComputerName']
            ]);
        }
        // Update progress
        ++$index;
        print "Proceeded line $index" . "\n";
    }
}
fclose($fip);
fclose($fop);