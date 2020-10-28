<?php

function action(): void
{
    exec('php execute.php > /dev/null 2>&1 &');
}

for ($i = 0; $i < 256; $i++) {
    echo 'Action: ' . $i . PHP_EOL;
    action();
}