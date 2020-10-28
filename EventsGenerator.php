<?php


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

    public function __construct(int $eventsNumber, int $accountsNumber, int $limitEventOnAccount)
    {
        $this->eventsNumber = $eventsNumber;
        // -1 для ограничений цикла, т.к. будем считать с 0
        $this->accountsNumber = $accountsNumber - 1;
        $this->limitEventOnAccount = $limitEventOnAccount -1;
    }

    public function generate() {
        $eventsCount = 0;

        while ($eventsCount < $this->eventsNumber) {

            $accountID = random_int(0, $this->accountsNumber);
            $eventsNumber = random_int(0, $this->limitEventOnAccount);

            $events = [];
            for ($i = 1; $i < $eventsNumber; $i++) {
                $events[] =  new Event($accountID, $i);
            }

            //TODO:add events to queue

            //Может быть сгенерировано чуть больше событий, чем EVENTS_NUMBER. Если это критично, то можно добавить проверку при генерации $eventsNumber.
            $eventsCount += $eventsNumber;
        }
    }
}