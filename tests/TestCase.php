<?php

namespace Ensi\LaravelPhpRdKafka\Tests;

use Ensi\LaravelPhpRdKafka\LaravelPhpRdKafkaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelPhpRdKafkaServiceProvider::class,
        ];
    }
}
