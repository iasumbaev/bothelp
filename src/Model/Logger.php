<?php


namespace App\Model;


class Logger
{
    public function log($accountID, $eventID)
    {
        file_put_contents('log.txt', $accountID . ':' . $eventID . PHP_EOL, FILE_APPEND);
    }
}