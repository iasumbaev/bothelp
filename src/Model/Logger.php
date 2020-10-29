<?php


namespace App\Model;


class Logger
{
    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function log($accountID, $eventID): void
    {
        file_put_contents($this->path, $accountID . ':' . $eventID . PHP_EOL, FILE_APPEND);
    }
}