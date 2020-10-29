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

function getProcessCount($processName)
{
    exec("ps -A | grep -i $processName | grep -v grep", $pids);
    return count($pids);
}

$client = new Client([
    'host' => 'localhost',
    'port' => 6379,
]);

file_put_contents('log.txt', '');

$start = microtime(true);
$command = 'php execute.php';
//for ($i = 0; $i < 10 && $client->llen('events'); $i++) {
//while ($client->llen('events') !== 0) {
if (getProcessCount($command) < 50) {
    execInBackground($command);
}
//}

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