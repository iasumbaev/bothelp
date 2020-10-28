<?php


namespace App\Model;


use PhpAmqpLib\Channel\AMQPChannel;

class EventHandler
{
    /**
     * @var AMQPChannel
     */
    private $channel;

    public function __construct(AMQPChannel $channel)
    {
        $this->channel = $channel;
    }

    public function execute()
    {
        $callback = function ($msg) {
            if ($msg->body) {
                $events = explode(',', $msg->body);
                foreach ($events as $index => $event) {
                    file_put_contents('log.txt', $event . PHP_EOL, FILE_APPEND);
                }
                return true;
            }
            return false;
        };

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume('event_queue', '', false, false, false, false, $callback);

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }
}