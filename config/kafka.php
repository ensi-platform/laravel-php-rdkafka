<?php

// configurattion options can be found here: https://github.com/edenhill/librdkafka/blob/master/CONFIGURATION.md
// if an option is set to null it is ignored.
return [
   'consumers' => [
      'default' => [
         'metadata.broker.list' => env('KAFKA_BROKER_LIST'),
         'security.protocol' => env('KAFKA_SECURITY_PROTOCOL', 'plaintext'),
         'sasl.mechanisms' => env('KAFKA_SASL_MECHANISMS'),
         'sasl.username' => env('KAFKA_SASL_USERNAME'),
         'sasl.password' => env('KAFKA_SASL_PASSWORD'),
         'log_level' => env('KAFKA_DEBUG', false) ? (string) LOG_DEBUG : (string) LOG_INFO,
         'debug' => env('KAFKA_DEBUG', false) ? 'all' : null,

         // consumer specific options
         'group.id' => env('KAFKA_CONSUMER_GROUP_ID', env('APP_NAME')),
         'enable.auto.commit' => true,
         'auto.offset.reset' => 'largest',
      ],
   ],
   'producers' => [
      'default' => [
         'metadata.broker.list' => env('KAFKA_BROKER_LIST'),
         'security.protocol' => env('KAFKA_SECURITY_PROTOCOL', 'plaintext'),
         'sasl.mechanisms' => env('KAFKA_SASL_MECHANISMS'),
         'sasl.username' => env('KAFKA_SASL_USERNAME'),
         'sasl.password' => env('KAFKA_SASL_PASSWORD'),
         'log_level' => env('KAFKA_DEBUG', false) ? (string) LOG_DEBUG : (string) LOG_INFO,
         'debug' => env('KAFKA_DEBUG', false) ? 'all' : null,

         // producer specific options
         'compression.codec' => env('KAFKA_PRODUCER_COMPRESSION_CODEC', 'snappy'),
      ],
   ],
];
