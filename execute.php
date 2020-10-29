<?php

use App\Model\EventHandler;
use App\Model\Logger;
use Predis\Client;

require_once __DIR__ . '/vendor/autoload.php';


$client = new Client([
    'host' => 'localhost',
    'port' => 6379,
]);

$handler = new EventHandler($client, new Logger());
while ($handler->hasEventID() && $handler->execute()) {
    $handler = new EventHandler($client, new Logger());
}