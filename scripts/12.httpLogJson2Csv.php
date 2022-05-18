<?php
/**
 * Convert json log to csv
 *
 * @author: tuanha
 */
require(__DIR__.'/../bootstrap.php');

// Return true if the given argument is an JSON string
function isJSONString(string $str)
{
    return is_array(json_decode($str, true));
}

// Return line items in format of a flat [key => value] array
function prepareCSVDataForEachJSONLine($line)
{
    $data = [];
    if (isJSONString($line)) {
        $lineItems = json_decode($line, true);
        foreach ($lineItems as $key => $item) {
            if (!isJSONString($item)) {
                $data[$key] = $item;
            } else {
                $data = array_merge($data, prepareCSVDataForEachJSONLine($item));
            }
        }
    }
    return $data;
}

$fip = fopen(__DIR__ . '/../input/12.http_log.json', 'r');
$fop = fopen(__DIR__ . '/../output/http_log.csv', 'w');

$index = 0;
if ($fip) {   
    // Read JSON file first time to extract headers
    $headers = [];
    while (!feof($fip)) {
        $line = fgets($fip);
        if ($line) {
            $headers = array_merge($headers, array_diff(array_keys(prepareCSVDataForEachJSONLine($line)), $headers));
        }
    }
    fputcsv($fop, $headers);
    rewind($fip);
    // Read JSON file 2nd time to write data
    while (!feof($fip)) {
        $line = fgets($fip);
        if ($line) {
            $lineItems = prepareCSVDataForEachJSONLine($line);
            $lineItemsValues = [];
            foreach ($headers as $header) {
                array_push($lineItemsValues, $lineItems[$header] ?? '-');
            }
            fputcsv($fop, array_values($lineItemsValues));
            // Update progress
            ++$index;
            print "Proceeded line $index" . "\n";
        }
    }
}
fclose($fip);
fclose($fop);