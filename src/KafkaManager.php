<?php

namespace Greensight\LaravelPhpRdKafka;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Illuminate\Contracts\Foundation\Application;
use RdKafka\Conf;
use RdKafka\Producer;
use RdKafka\KafkaConsumer;

class KafkaManager
{
   /**
    * The application instance.
    *
    * @var \Illuminate\Contracts\Foundation\Application
    */
   protected $app;

   /**
    * Consumers configs.
    *
    * @var array
    */
   protected array $consumersConfigs = [];

   /**
    * Producers configs.
    *
    * @var array
    */
   protected array $producersConfigs = [];

   /**
    * Consumers.
    *
    * @var array
    */
   protected array $consumers = [];

   /**
    * Producers.
    *
    * @var array
    */
   protected array $producers = [];

   /**
    * Create a new kafka manager instance.
    *
    * @param  Application  $app
    * @return void
    */
   public function __construct(Application $app)
   {
      $this->app = $app;
   }

   /**
    * Get a consumer config instance.
    *
    * @param  string  $name
    * @return Conf
    */
   public function consumerConfig(string $name = 'default'): Conf
   {
      if (!isset($this->consimersConfigs[$name])) {
         $this->consimersConfigs[$name] = $this->makeConfig($name, 'consumer');
      }

      return $this->consimersConfigs[$name];
   }

   /**
    * Get a producer config instance.
    *
    * @param  string  $name
    * @return Conf
    */
   public function producerConfig(string $name = 'default'): Conf
   {
      if (!isset($this->producersConfigs[$name])) {
         $this->producersConfigs[$name] = $this->makeConfig($name, 'producer');
      }

      return $this->producersConfigs[$name];
   }

   /**
    * Get a consumer instance.
    *
    * @param  string  $name
    * @return KafkaConsumer
    */
   public function consumer(string $name = 'default'): KafkaConsumer
   {
      if (!isset($this->consumers[$name])) {
         $this->consumers[$name] = new KafkaConsumer($this->consumerConfig($name));
      }

      return $this->consumers[$name];
   }

   /**
    * Get a producer instance.
    *
    * @param  string  $name
    * @return Producer
    */
   public function producer(string $name = 'default'): Producer
   {
      if (!isset($this->producers[$name])) {
         $this->producers[$name] = new Producer($this->producerConfig($name));
      }

      return $this->producers[$name];
   }


   protected function makeConfig(string $name, string $type): Conf
   {
      $availableValues = $this->app['config']["kafka.{$type}s"];

      if (is_null($configValues = Arr::get($availableValues, $name))) {
         throw new InvalidArgumentException("$type config [kafka.{$type}s.{$name}] not found.");
      }

      $config = new Conf();
      foreach ($this->cleanupConfigValues($configValues) as $key => $value) {
         $config->set($key, $value);
      }

      return $config;
   }

   /**
    * Register a terminating callback with the application.
    *
    * @param  callable|string  $callback
    * @return $this
    */
   public function terminating($callback)
   {
      return $this->app->terminating($callback);
   }

   protected function cleanupConfigValues(array $configValues)
   {
      if ($configValues['security.protocol'] === 'plaintext') {
         unset($configValues['security.protocol'], $configValues['security.username'], $configValues['sasl.password']);
      }

      foreach ($configValues as $key => $value) {
         if ($value === null) {
            unset($configValues[$key]);
         }
      }

      $booleanToStrings = [
         'enable.auto.commit',
      ];
      foreach ($booleanToStrings as $key) {
         if (isset($configValues[$key])) {
            $configValues[$key] = Helpers::stringifyBoolean($configValues[$key]);
         }
      }

      return $configValues;
   }
}
