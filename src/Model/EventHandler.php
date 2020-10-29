<?php


namespace App\Model;

use Predis\Client;

class EventHandler
{

    /**
     * @var Client
     */
    private $client;
    /**
     * @var Logger
     */
    private $logger;

    private $accountID;

    private $eventID;

    public function __construct(Client $client, Logger $logger)
    {
        $this->client = $client;
        $this->logger = $logger;

//        $this->initEvent();
    }

    public function hasEventID()
    {
        return isset($this->eventID);
    }

    private function initEvent()
    {
        //lpop вернёт accountID:eventID
        $data = $this->client->lpop('events');
        if ($data) {
            [$this->accountID, $this->eventID] = explode(':', $data);
            $this->addEventToAccountPoll($this->accountID, $this->eventID);
            return true;
        }
        return false;
    }


    /**
     * Добавление события в пул событий аккаунтов
     * @param $accountID - ID аккаунта
     * @param $eventID - ID события
     */
    private function addEventToAccountPoll($accountID, $eventID): void
    {
        $this->client->sadd('account_' . $accountID, $eventID);
    }

    /**
     * Проверка на то, что событие можно исполнить
     * @param $accountID - ID аккаунта
     * @param $eventID - ID события
     * @return bool
     */
    private function isExecutable($accountID, $eventID): bool
    {
        $pool = $this->client->smembers('account_' . $accountID);

        // Если событие первое в пуле, то его можно выполнить
        return (int)min($pool) === (int)$eventID;

    }

    /**
     * Проверка есть ли ещё необработанные события
     */
    private function isQueueEmpty(): bool
    {
        return $this->client->llen('events') === 0;
    }

    /**
     * Проверка есть ли блокировка на аккаунт
     * @param $id - ID аккаунта
     * @return int - 1 если аккаунт заблокирован, иначе 0
     */
    private function hasLock($id): int
    {
        return $this->client->exists('lock_' . $id);
    }

    /**
     * Блокировка аккаунта для выполнения событий
     * @param $accountID - ID аккаунта
     * @return int
     */
    private function lockAccount($accountID, $eventID): int
    {
        if (!$this->isExecutable($accountID, $eventID)) {
            return 0;
        }
        return $this->client->setnx('lock_' . $accountID, true);
    }

    public function execute(): bool
    {
        while ($this->initEvent()) {
            while (!$this->lockAccount($this->accountID, $this->eventID)) {
                continue;
            }

            sleep(1);

            $this->logger->log($this->accountID, $this->eventID);

            $this->release($this->accountID, $this->eventID);
        }
    }

    /**
     * Снятие блокировки с аккаунта и удаление события из пула
     * @param $accountID - ID аккаунта
     * @param $eventID - ID события
     */
    private function release($accountID, $eventID): void
    {
        $this->client->del('lock_' . $accountID);
        $this->client->srem('account_' . $accountID, $eventID);
        $this->accountID = null;
        $this->eventID = null;
    }
}