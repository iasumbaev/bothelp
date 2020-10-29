<?php

use Predis\Client;

require_once __DIR__ . '/vendor/autoload.php';

function execInBackground($cmd)
{
    if (strpos(php_uname(), 'Windows') === 0) {
        pclose(popen("start /B " . $cmd, "r"));
    } else {
        passthru($cmd . " > /dev/null &", $return);
        echo  $return;
    }
}

$client = new Client([
    'host' => 'localhost',
    'port' => 6379,
]);

file_put_contents('log.txt', '');

$start = microtime(true);
for ($i = 0; $client->llen('events') !== 0; $i++) {
    execInBackground('php execute.php');
}

echo 'Length now: ' . $client->llen('events') . PHP_EOL;
$loopCount = 0;
// Ожидание, пока обработчики не закончат работу
while ($client->llen('events') !== 0) {
    $loopCount++;
    if ($loopCount % 1000 === 0) {
        echo 'Length now: ' . $client->llen('events') . PHP_EOL;
    }
}

echo 'Done! Time: ' . (microtime(true) - $start) . PHP_EOL;