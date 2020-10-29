<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Model\EventsGenerator;
use Predis\Client;

const EVENTS_NUMBER = 1000;
const ACCOUNTS_NUMBER = 100;
const LIMIT_EVENT_ON_ACCOUNT = 5;

$client = new Client([
    'host' => 'localhost',
    'port' => 6379,
]);

$client->flushall();

$generator = new EventsGenerator(EVENTS_NUMBER, ACCOUNTS_NUMBER, LIMIT_EVENT_ON_ACCOUNT, $client);
$generator->generate();