<?php

use Predis\Client;

require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function execInBackground($cmd)
{
    if (strpos(php_uname(), 'Windows') === 0) {
        pclose(popen('start /B ' . $cmd, 'r'));
    } else {
        exec($cmd . ' > /dev/null &');
    }
}

$client = new Client([
    'host' => $_ENV['redis_host'],
    'port' => $_ENV['redis_port'],
]);

//чистим логи
file_put_contents($_ENV['log_path'], '');

$command = 'php execute.php';

$start = microtime(true);
for ($i = 0; $i < $_ENV['HANDLERS_NUMBER'] && $client->llen('events'); $i++) {
    execInBackground($command);
}

$loopCount = 0;
// Ожидание, пока обработчики не закончат работу
while ($client->llen('events') !== 0) {
    if ($loopCount++ % 1000 === 0) {
        echo 'Queue size: ' . $client->llen('events') . PHP_EOL;
    }
}

echo 'Done! Time: ' . (microtime(true) - $start) . PHP_EOL;