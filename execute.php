<?php

use App\Model\EventHandler;
use App\Model\Logger;
use Predis\Client;

require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


//Пока есть необработанные события - выполняем
do {
    $handler = new EventHandler(
        new Client([
            'host' => $_ENV['redis_host'],
            'port' => $_ENV['redis_port'],
        ]),
        new Logger($_ENV['log_path']));
} while ($handler->hasEventID() && $handler->execute());