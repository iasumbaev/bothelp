<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Model\EventsGenerator;
use Predis\Client;


const EVENTS_NUMBER = 10000;
const ACCOUNTS_NUMBER = 1000;
const LIMIT_EVENT_ON_ACCOUNT = 10;

$client = new Client([
    'host' => 'localhost',
    'port' => 6379,
]);

$generator = new EventsGenerator(EVENTS_NUMBER, ACCOUNTS_NUMBER, LIMIT_EVENT_ON_ACCOUNT, $client);
$generator->generate();