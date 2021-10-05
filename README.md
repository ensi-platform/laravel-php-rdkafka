# Bridge package between Laravel and php-rdkafka

This packages allows you to describe Kafka producers and consumers in config/kafka.php and then reuse them everywhere.

## Installation

You can install the package via composer:

```bash
composer require ensi/laravel-phprdkafka
```

Publish the config file with:
```bash
php artisan vendor:publish --provider="Ensi\LaravelPhpRdKafka\LaravelPhpRdKafkaServiceProvider" --tag="kafka-config"
```

Now go to `config/kafka.php` and configure your producers and consumers there.
You typically need one producer/consumer per Kafka Cluster.
Configuration parameters can found in [Librdkafka Configuration reference](https://github.com/edenhill/librdkafka/blob/master/CONFIGURATION.md)

## Usage


Producer example:

```php

$producer = \Kafka::producer('producer-name'); // returns a configured RdKafka\Producer singleton.
// or $producer = \Kafka::producer(); if you want to get the default producer.
// or $producer = $kafkaManager->producer(); where $kafkaManager is an instance of Ensi\LaravelPhpRdKafka\KafkaManager resolved from the service container.

// now you can implement any producer logic e.g:

$headers = [];
$topicName = 'test-topic';
$topic = $producer->newTopic($topicName);
for ($i = 0; $i < 10; $i++) {
   $payload = json_encode([
      'body' => "Message $i in topic [$topicName]",
      'headers' => $headers
   ]);
   $topic->produce(RD_KAFKA_PARTITION_UA, 0, $payload);
   $producer->poll(0);
}

for ($flushRetries = 0; $flushRetries < 10; $flushRetries++) {
   $result = $producer->flush(10000);
   if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
      break;
   }
}
if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
   // Log and/or throw "Unable to flush Kafka producer, messages of topic [$topicName] might be lost.' exception.
}

// If you use php-fpm and producing is slow you can move its execution to the place after response has been sent. 
// This can be achieved e.g. by wrapping the whole producing or at least flushing in it in a "terminating" callback.
// app()->terminating(function () { ... });

```

Consumer example:

```php

public function handle(KafkaManager $kafkaManager)
{
   $consumer = $kafkaManager->consumer('consumer-name');
   $consumer->subscribe(['test-topic-1', 'test-topic-2']);

   while (true) {
      $message = $consumer->consume(120*1000);
      switch ($message->err) {
            case RD_KAFKA_RESP_ERR_NO_ERROR:
               $this->info($message->payload);
               $this->processMessage($message); // do something with the message
               // $consumer->commitAsync($message); // commit offsets asynchronously if you set 'enable.auto.commit' => false, in config/kafka.php
               break;
            case RD_KAFKA_RESP_ERR__PARTITION_EOF:
               echo "No more messages; will wait for more\n";
               break;
            case RD_KAFKA_RESP_ERR__TIMED_OUT:
               // this also happens when there is no new messages in the topic after the specified timeout: https://github.com/arnaud-lb/php-rdkafka/issues/343
               echo "Timed out\n";
               break;
            default:
               throw new Exception($message->errstr(), $message->err);
               break;
      }
   }
}

```

You can learn more about php-rdkafka producers and consumers [php-rdkafka examples](https://arnaud.le-blanc.net/php-rdkafka-doc/phpdoc/rdkafka.examples.html)

Direct access to `RdKafka\Conf` instances is available with the following getters:


```php

$producerConf = $kafkaManager->producerConfig('producer-name');
$consumerConf = $kafkaManager->consumerConfig('consumer-name');

```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
