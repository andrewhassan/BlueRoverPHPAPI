<?php

// API SAMPLE USAGE

include 'api.php';

$api = new BlueRoverApi("insert key here", 
						"insert token here", 
						'http://developers.polairus.com');

echo "events: ";
echo $api->event(1375971038, 1375971238, 0) . "\n";

echo "rfids: ";
echo $api->rfid() . "\n";

echo "devices: ";
echo $api->device() . "\n";
?>