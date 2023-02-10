<?php

namespace Ensi\LaravelPhpRdKafka;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string[] availableConnections()
 * @method static string[] allTopics(string $connection)
 * @method static string topicName(string $connection, string $topicKey)
 * @method static string topicNameByClient(string $clientType, string $clientName, string $topicKey)
 * @method static \RdKafka\KafkaConsumer consumer(string $name)
 * @method static \RdKafka\Producer producer(string $name)
 * @method static \RdKafka\Producer rdKafka(string $connection)
 *
 * @see \Ensi\LaravelPhpRdKafka\KafkaManager
 */
class KafkaFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'kafka';
    }
}
