<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class LanguageQueueConsumer extends Command
{
    protected $signature = 'queue:consume';
    protected $description = 'Consume messages from the language queue';

    public function handle()
    {
        $connection = new AMQPStreamConnection(
            config('queue.connections.rabbitmq.host'),
            config('queue.connections.rabbitmq.port'),
            config('queue.connections.rabbitmq.login'),
            config('queue.connections.rabbitmq.password'),
            config('queue.connections.rabbitmq.vhost')
        );

        $channel = $connection->channel();

        $channel->exchange_declare(
            config('queue.connections.rabbitmq.options.exchange.name'),
            config('queue.connections.rabbitmq.options.exchange.type')
        );

        list($queueName,,) = $channel->queue_declare('', false, false, true, false);

        $languagesOfInterest = ['english', 'spanish'];

        foreach ($languagesOfInterest as $language) {
            $channel->queue_bind($queueName, config('queue.connections.rabbitmq.options.exchange.name'), $language);
        }

        $callback = function (AMQPMessage $message) {
            $data = json_decode($message->getBody(), true);

            // Emparelhar a sala com um usuário disponível para o mesmo idioma
            $roomName = $data['room_name'];
            $userId = $data['user_id'];

            $redis = Redis::connection();
            $key = "room:{$roomName}";
            $connectedUser = $redis->get($key);

            if (!$connectedUser) {
                // Primeiro usuário na sala, armazene o ID do usuário no Redis
                $redis->set($key, $userId);
            } else {
                // Segundo usuário na sala, remova o ID do usuário do Redis e envie a mensagem de emparelhamento
                $redis->del($key);

                // Aviso para o primeiro usuário
                $this->info("Matched: User {$connectedUser} and User {$userId} are paired in room {$roomName}");

                // Aviso para o segundo usuário
                $this->info("Matched: You are paired with User {$connectedUser} in room {$roomName}");
            }
        };

        $channel->basic_consume($queueName, '', false, true, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}
