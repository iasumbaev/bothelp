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
     * @param $id - ID аккаунта
     */
    private function lockAccount($id): void
    {
        $this->client->set('lock_' . $id, true);
    }

    public function execute(): void
    {
        while (!$this->isQueueEmpty()) {
            //lpop вернёт accountID:eventID
            [$accountID, $eventID] = explode(':', $this->client->lpop('events'));

            while ($this->hasLock($accountID)) {
                usleep(100);
            }

            $this->lockAccount($accountID);

            sleep(1);

            $this->logger->log($accountID, $eventID);

            $this->release($accountID);
        }
    }

    /**
     * Снятие блокировки с аккаунта и удаление ключа из redis
     * @param $id - ID аккаунта
     */
    private function release($id): void
    {
        $this->client->del('lock_' . $id);
    }
}