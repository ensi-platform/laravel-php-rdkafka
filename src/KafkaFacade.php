<?php

namespace Ensi\LaravelPhpRdKafka;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ensi\LaravelPhpRdKafka\KafkaManager
 */
class KafkaFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'kafka';
    }
}
