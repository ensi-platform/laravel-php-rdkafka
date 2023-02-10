<?php

namespace Ensi\LaravelPhpRdKafka;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string|null topicName(string $topicKey)
 * @method static \RdKafka\Conf consumerConfig(string $name)
 * @method static \RdKafka\Conf producerConfig(string $name)
 * @method static \RdKafka\KafkaConsumer consumer(string $name)
 * @method static \RdKafka\Producer producer(string $name)
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
