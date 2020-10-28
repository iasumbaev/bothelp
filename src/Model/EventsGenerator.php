<?php

namespace App\Model;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use Predis\Client;

class EventsGenerator
{
    /**
     * @var int
     */
    private $eventsNumber;
    /**
     * @var int
     */
    private $accountsNumber;
    /**
     * @var int
     */
    private $limitEventOnAccount;
    /**
     * @var Client
     */
    private $client;

    public function __construct(int $eventsNumber, int $accountsNumber, int $limitEventOnAccount, Client $client)
    {
        $this->eventsNumber = $eventsNumber;
        // -1 для ограничений цикла, т.к. будем считать с 0
        $this->accountsNumber = $accountsNumber - 1;
        $this->limitEventOnAccount = $limitEventOnAccount - 1;
        $this->client = $client;
    }

    public function generate()
    {
        $eventsCount = 0;
        while ($eventsCount < $this->eventsNumber) {

            $accountID = random_int(0, $this->accountsNumber);
            $eventsNumber = random_int(0, $this->limitEventOnAccount);

            //Если до этого был использован такой же аккаунт, надо сохранить количество событий,
            // чтобы id событий не повторялись для одного аккаунта
            $lastEventID = $this->client->get('last_event_id_' . $accountID);
            if (is_null($lastEventID)) {
                $lastEventID = 0;
            }

            $events = [];
            $iMax = $eventsNumber + $lastEventID;
            for ($i = $lastEventID; $i < $iMax; $i++) {
                $events[] = new Event($accountID, $i);
            }

            //todo: add events to redis queue
            $this->client->rpush('events', $events);

            //Может быть сгенерировано чуть больше событий, чем EVENTS_NUMBER. Если это критично, то можно добавить проверку при генерации $eventsNumber.
            $eventsCount += $eventsNumber;
        }
    }
}