<?php

$lines = explode(PHP_EOL, file_get_contents('log.txt'));

$data = [];
foreach ($lines as $index => $line) {
    $elem = explode(':', $line);
    if(!isset($elem[1])){
        continue;
    }
    if (isset($data[$elem[0]])) {
        $data[$elem[0]][] = $elem[1];
    } else {
        $data[$elem[0]] = [$elem[1]];
    }
}

foreach ($data as $index => $datum) {
    $default = $datum;
    sort($default);
    if($default !== $datum) {
        echo 'Unsorted!';
        var_dump($datum);
        var_dump($index);
        break;
    }
}

