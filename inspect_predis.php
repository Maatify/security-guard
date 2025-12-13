<?php
require __DIR__ . '/vendor/autoload.php';

use Predis\Client;

echo "Predis Client methods:\n";
$client = new Client();
print_r(get_class_methods($client));

echo "\nConnection methods:\n";
$connection = $client->getConnection();
echo "Connection class: " . get_class($connection) . "\n";
print_r(get_class_methods($connection));
