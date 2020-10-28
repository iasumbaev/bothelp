<?php

namespace App\Model;

class Event
{
    /**
     * @var int
     */
    private $accountID;
    /**
     * @var int
     */
    private $eventID;

    public function __construct(int $accountID, int $eventID)
    {
        $this->accountID = $accountID;
        $this->eventID = $eventID;
    }

    public function __toString()
    {
        return $this->accountID . ':' . $this->eventID;
    }
}