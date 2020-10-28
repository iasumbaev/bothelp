<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Model\EventsGenerator;
use PhpAmqpLib\Connection\AMQPStreamConnection;


const EVENTS_NUMBER = 10000;
const ACCOUNTS_NUMBER = 1000;
const LIMIT_EVENT_ON_ACCOUNT = 10;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('event_queue', false, true, false, false);

$generator = new EventsGenerator(EVENTS_NUMBER, ACCOUNTS_NUMBER, LIMIT_EVENT_ON_ACCOUNT, $channel);
$generator->generate();
