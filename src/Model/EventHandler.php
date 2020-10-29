<?php


namespace App\Model;

use Predis\Client;

class EventHandler
{

    private const ACCOUNTS_POOL_NAME = 'accounts_';

    private const QUEUE_NAME = 'events';

    private const ACCOUNT_LOCK_NAME = 'lock_';

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

        $this->initEvent();
    }

    public function hasEventID(): bool
    {
        return (bool)$this->eventID;
    }

    private function initEvent(): void
    {
        //lpop вернёт accountID:eventID
        $data = $this->client->lpop(self::QUEUE_NAME);
        if ($data) {
            [$this->accountID, $this->eventID] = explode(':', $data);
            $this->addEventToAccountPoll();
        }
    }

    /**
     * Добавление события в пул событий аккаунтов
     */
    private function addEventToAccountPoll(): void
    {
        $this->client->sadd(self::ACCOUNTS_POOL_NAME . $this->accountID, $this->eventID);
    }

    /**
     * Проверка на то, что событие можно исполнить
     * @return bool
     */
    private function isExecutable(): bool
    {
        $pool = $this->client->smembers(self::ACCOUNTS_POOL_NAME . $this->accountID);

        // Если событие первое в пуле, то его можно выполнить
        return (int)min($pool) === (int)$this->eventID;
    }

    /**
     * Блокировка аккаунта для выполнения событий
     * @return bool
     */
    private function lockAccount(): bool
    {
        if (!$this->isExecutable()) {
            return false;
        }
        return (bool)$this->client->setnx(self::ACCOUNT_LOCK_NAME . $this->accountID, true);
    }

    public function execute(): bool
    {
        while (!$this->lockAccount()) {
            continue;
        }

        sleep(1);

        $this->logger->log($this->accountID, $this->eventID);

        $this->release($this->accountID, $this->eventID);

        return true;
    }

    /**
     * Снятие блокировки с аккаунта и удаление события из пула
     * @param $accountID - ID аккаунта
     * @param $eventID - ID события
     */
    private function release($accountID, $eventID): void
    {
        $this->client->del(self::ACCOUNT_LOCK_NAME . $accountID);
        $this->client->srem(self::ACCOUNTS_POOL_NAME . $accountID, $eventID);
        $this->accountID = null;
        $this->eventID = null;
    }
}