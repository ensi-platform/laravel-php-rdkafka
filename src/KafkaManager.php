<?php

namespace Ensi\LaravelPhpRdKafka;

use InvalidArgumentException;
use RdKafka;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\Producer;

class KafkaManager
{
    /** @var array<KafkaConsumer> */
    protected $consumers = [];

    /** @var array<Producer> */
    protected $producers = [];

    protected $config = [];

    public function __construct()
    {
        $this->config = config('kafka');
    }

    /**
     * Get a consumer instance.
     *
     * @param string $name
     * @return KafkaConsumer
     */
    public function consumer(string $name = 'default'): KafkaConsumer
    {
        if (!isset($this->consumers[$name])) {
            $this->consumers[$name] = new KafkaConsumer(
                $this->makeKafkaConf(
                    $this->rawKafkaSettings($name, 'consumer')
                )
            );
        }

        return $this->consumers[$name];
    }

    /**
     * Get a producer instance.
     *
     * @param string $name
     * @return Producer
     */
    public function producer(string $name = 'default'): Producer
    {
        if (!isset($this->producers[$name])) {
            $this->producers[$name] = new Producer(
                $this->makeKafkaConf(
                    $this->rawKafkaSettings($name, 'producer')
                )
            );
        }

        return $this->producers[$name];
    }

    public function rdKafka(string $connectionName): RdKafka
    {
        return new Producer(
            $this->makeKafkaConf(
                $this->rawConnectionConfig($connectionName)['settings']
            )
        );
    }

    public function availableConnections(): array
    {
        return array_keys($this->config['connections']);
    }

    public function allTopics(string $connection): array
    {
        $connectionConfig = $this->rawConnectionConfig($connection);

        return $connectionConfig['topics'];
    }

    public function topicName(string $connection, string $topicKey): string
    {
        $connectionConfig = $this->rawConnectionConfig($connection);

        if (!isset($connectionConfig['topics'][$topicKey])) {
            throw new InvalidArgumentException("Topic with key '{$topicKey}' is not registered in kafka.topics");
        }

        return $connectionConfig['topics'][$topicKey];
    }

    public function topicNameByClient(string $clientType, string $clientName, string $topicKey): string
    {
        $clientConfig = $this->rawClientConfig($clientType, $clientName);

        return $this->topicName($clientConfig['connection'], $topicKey);
    }

    protected function makeKafkaConf(array $rawConnectionSettings): Conf
    {
        $cleanedSettings = $this->cleanupKafkaSettings($rawConnectionSettings);

        $config = new Conf();
        foreach ($cleanedSettings as $key => $value) {
            $config->set($key, $value);
        }

        return $config;
    }

    /**
     * Get raw kafka settings array, from merging of client additional-settings and connection settings
     * @param string $clientName client name (key in kafka.consumers or kafka.producer)
     * @param string $clientType 'consumer' or 'producer'
     * @return array<string,mixed>
     */
    protected function rawKafkaSettings(string $clientName, string $clientType): array
    {
        $clientConfig = $this->rawClientConfig($clientType, $clientName);
        $connectionConfig = $this->rawConnectionConfig($clientConfig['connection']);

        return array_merge($connectionConfig['settings'], $clientConfig['additional-settings']);
    }

    protected function rawConnectionConfig(string $connectionName): array
    {
        if (!isset($this->config['connections'][$connectionName])) {
            throw new InvalidArgumentException("connection config [kafka.connections.{$connectionName}] not found.");
        }

        return $this->config['connections'][$connectionName];
    }

    protected function rawClientConfig(string $clientType, string $clientName): array
    {
        $haystack = $clientType . 's';
        if (!isset($this->config[$haystack][$clientName])) {
            throw new InvalidArgumentException("$clientType config [kafka.{$clientType}s.{$clientName}] not found.");
        }

        return $this->config[$haystack][$clientName];
    }

    /**
     * Remove nulls and turn booleans to strings
     * @param array<string,mixed> $configValues
     * @return array<string,mixed>
     */
    protected function cleanupKafkaSettings(array $configValues): array
    {
        array_walk($configValues, function ($value, $key) use (&$result) {
            if (is_null($value)) {
                return;
            }

            $result[$key] = Helpers::stringifyBoolean($value);
        });

        return $result;
    }
}
