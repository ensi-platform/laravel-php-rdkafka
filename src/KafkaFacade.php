<?php

namespace Greensight\LaravelPhpRdKafka;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Greensight\LaravelPhpRdKafka\KafkaManager
 */
class KafkaFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'kafka';
    }
}
