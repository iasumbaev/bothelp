<?php

use Predis\Client;

require_once __DIR__ . '/vendor/autoload.php';


function execInBackground($cmd)
{
    if (strpos(php_uname(), 'Windows') === 0) {
        pclose(popen("start /B " . $cmd, "r"));
    } else {
        exec($cmd . " > /dev/null &");
    }
}

$client = new Client([
    'host' => 'localhost',
    'port' => 6379,
]);

file_put_contents('log.txt', '');

$start = microtime(true);
for ($i = 0; $i < 100 && $client->llen('events') !== 0; $i++) {
    execInBackground('php execute.php');
}

// Ожидание, пока обработчики не закончат работу
$loopCount = 0;
while ($client->llen('events') !== 0) {
    $loopCount++;
    if ($loopCount % 1000 === 0) {
        echo 'Length: ' . $client->llen('events'). PHP_EOL;
    }
    continue;
}

echo 'Done! Time: ' . (microtime(true) - $start) . PHP_EOL;