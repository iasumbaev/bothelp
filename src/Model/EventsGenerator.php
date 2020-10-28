<?php

namespace App\Model;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;

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
     * @var AMQPChannel
     */
    private $channel;

    public function __construct(int $eventsNumber, int $accountsNumber, int $limitEventOnAccount, AMQPChannel $channel)
    {
        $this->eventsNumber = $eventsNumber;
        // -1 для ограничений цикла, т.к. будем считать с 0
        $this->accountsNumber = $accountsNumber - 1;
        $this->limitEventOnAccount = $limitEventOnAccount - 1;
        $this->channel = $channel;
    }

    public function generate()
    {
        $eventsCount = 0;
        $accountIDs = [];
        while ($eventsCount < $this->eventsNumber) {

            $accountID = random_int(0, $this->accountsNumber);
            $eventsNumber = random_int(0, $this->limitEventOnAccount);

            //Если до этого был использован такой же аккаунт, надо сохранить количество событий,
            // чтобы id событий не повторялись для одного аккаунта
            //TODO: use redis
            if (isset($accountIDs[$accountID])) {
                $adding = $accountIDs[$accountID];
                $accountIDs[$accountID] += $eventsNumber;
            } else {
                $adding = 0;
                $accountIDs[$accountID] = $eventsNumber;
            }

            $events = [];
            for ($i = 0; $i < $eventsNumber; $i++) {
                $events[] = (string)(new Event($accountID, $adding + $i));
            }

            $msg = new AMQPMessage(implode(',', $events));

            $this->channel->basic_publish($msg, '', 'event_queue');

            //Может быть сгенерировано чуть больше событий, чем EVENTS_NUMBER. Если это критично, то можно добавить проверку при генерации $eventsNumber.
            $eventsCount += $eventsNumber;
        }
    }
}