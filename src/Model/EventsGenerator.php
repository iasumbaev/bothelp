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
        // -1 т.к. будем считать с 0
        $this->accountsNumber = $accountsNumber - 1;
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
                $lastEventID = 1;
            }

            $iMax = $eventsNumber + $lastEventID;
            for ($i = $lastEventID; $i < $iMax; $i++) {
                // Добавляем события в очередь в формате accountID:eventID
                $this->client->rpush('events', [new Event($accountID, $i)]);
            }

            $this->client->set('last_event_id_' . $accountID, $iMax);

            // Может быть сгенерировано чуть больше событий, чем EVENTS_NUMBER.
            // Если это критично, то можно добавить проверку при генерации $eventsNumber.
            $eventsCount += $eventsNumber;
            echo 'Generated ' . $eventsNumber . ' events for account #' . $accountID . PHP_EOL;
        }

        $this->release();
    }


    /**
     * Очищаем redis от ключей вида last_event_id_ID, т.к. они использовались только для генерации
     */
    private function release(): void
    {
        for ($i = 1; $i <= $this->accountsNumber; $i++) {
            $this->client->del('last_event_id_' . $i);
        }
    }
}