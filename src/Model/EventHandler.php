<?php


namespace App\Model;


use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class EventHandler
{
    /**
     * @var AMQPChannel
     */
    private $channel;
    /**
     * @var AMQPStreamConnection
     */
    private $connection;



    public function __construct()
    {
        $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->channel = $this->connection->channel();
    }

    public function execute()
    {
        $callback = function ($msg) {
            if ($msg->body) {
                $events = explode(',', $msg->body);
                foreach ($events as $index => $event) {
                    file_put_contents('log.txt', $event . PHP_EOL, FILE_APPEND);
                }
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                return true;
            }
            return false;
        };

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume('event_queue', '', false, false, false, false, $callback);

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }

        $this->channel->close();
        $this->connection->close();
    }
}