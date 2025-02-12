<?php

namespace Ensi\LaravelPhpRdKafka;

use Ensi\LaravelPhpRdKafka\Commands\CheckTopicsExistsCommand;
use Illuminate\Support\ServiceProvider;

class LaravelPhpRdKafkaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->packageBasePath("/../config/kafka.php"), 'kafka');

        $this->app->scoped('kafka', function ($app) {
            return new KafkaManager();
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->packageBasePath("/../config/kafka.php") => config_path("kafka.php"),
            ], "kafka-config");

            $this->commands([
                CheckTopicsExistsCommand::class,
            ]);
        }
    }

    protected function packageBasePath(?string $directory = null): string
    {
        if ($directory === null) {
            return __DIR__;
        }

        return __DIR__ . DIRECTORY_SEPARATOR . ltrim($directory, DIRECTORY_SEPARATOR);
    }
}
