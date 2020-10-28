<?php

namespace App\Model;

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
        $this->accountsNumber = $accountsNumber;
        $this->limitEventOnAccount = $limitEventOnAccount;
        $this->client = $client;
    }

    public function generate(): void
    {
        $eventsCount = 0;
        while ($eventsCount < $this->eventsNumber) {

            $accountID = random_int(1, $this->accountsNumber);
            $eventsNumber = random_int(1, $this->limitEventOnAccount);

            // Если до этого был использован такой же аккаунт, надо сохранить количество событий,
            // чтобы id событий не повторялись для одного аккаунта
            $lastEventID = $this->client->get('last_event_id_' . $accountID);
            if (is_null($lastEventID)) {
                $lastEventID = 0;
            }

            $iMax = $eventsNumber + $lastEventID;
            for ($i = $lastEventID; $i < $iMax; $i++) {
                $this->client->rpush('events', [new Event($accountID, $i)]);
            }

            // Может быть сгенерировано чуть больше событий, чем EVENTS_NUMBER.
            // Если это критично, то можно добавить проверку при генерации $eventsNumber.
            $eventsCount += $eventsNumber;

            $this->client->set('last_event_id_' . $accountID, $iMax);
        }
    }
}