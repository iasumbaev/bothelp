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

    public function __construct(Client $client, Logger $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
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
        $pool = array_map('intval', $pool);
        sort($pool);

        // Если событие первое в пуле, то его можно выполнить
        return $pool[0] === (int)$eventID;

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
    private function lockAccount($accountID): int
    {
        return $this->client->setnx('lock_' . $accountID, true);
    }

    public function execute(): void
    {
        while (!$this->isQueueEmpty()) {
            //lpop вернёт accountID:eventID
            [$accountID, $eventID] = explode(':', $this->client->lpop('events'));

            $this->addEventToAccountPoll($accountID, $eventID);

            while ($this->hasLock($accountID) || !$this->isExecutable($accountID, $eventID)) {
                continue;
            }

            //Проверка, можем ли мы заблокировать аккаунт
            while (!$this->lockAccount($accountID)) {
                continue;
            }

//            sleep(1);

            $this->logger->log($accountID, $eventID);

            $this->release($accountID, $eventID);
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
    }
}