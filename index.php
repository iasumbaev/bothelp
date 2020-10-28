<?php

use Predis\Client;

require_once __DIR__ . '/vendor/autoload.php';

function action(): void
{
    exec('php execute.php > /dev/null 2>&1 &');
}

$client = new Client([
    'host' => 'localhost',
    'port' => 6379,
]);

file_put_contents('log.txt', '');

for ($i = 0; $i < 1000 && $client->llen('events') !== 0; $i++) {
    echo 'Action: ' . $i . PHP_EOL;
    action();
}

// Ожидание, пока обработчики не закончат работу
while ($client->llen('events') !== 0) {
    continue;
}

echo 'Done!' . PHP_EOL;