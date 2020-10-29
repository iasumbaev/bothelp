<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Model\EventsGenerator;
use Predis\Client;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$client = new Client([
    'host' => $_ENV['redis_host'],
    'port' => $_ENV['redis_port'],
]);

$client->flushall();

$generator = new EventsGenerator($_ENV['EVENTS_NUMBER'], $_ENV['ACCOUNTS_NUMBER'], $_ENV['LIMIT_EVENT_ON_ACCOUNT'], $client);
$generator->generate();