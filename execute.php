<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Model\EventHandler;
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('event_queue', false, true, false, false);

for ($i = 0; $i < 10; $i++) {
   $handler =  new EventHandler($channel);
   $handler->execute();
}