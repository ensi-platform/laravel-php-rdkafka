<?php

namespace Ensi\LaravelPhpRdKafka\Commands;

use Ensi\LaravelPhpRdKafka\KafkaFacade;
use Illuminate\Console\Command;

class CheckTopicsExistsCommand extends Command
{
    protected $signature = 'kafka:find-not-created-topics
                            {--validate : return error if there are not created topics}';
    protected $description = 'Check if all topics from kafka.topics exist';

    public function __construct()
    {
        parent::__construct();
        error_reporting(E_ALL & ~E_DEPRECATED);
        ini_set('display_errors', '0');
    }

    public function handle(): int
    {
        $totalDesiredTopics = 0;
        $notFoundTopics = [];

        $connectionNames = KafkaFacade::availableConnections();

        foreach ($connectionNames as $connectionName) {
            $existingTopics = $this->getExistingTopics($connectionName);

            $desiredTopics = KafkaFacade::allTopics($connectionName);
            $totalDesiredTopics += count($desiredTopics);

            foreach ($desiredTopics as $topicName) {
                if (!in_array($topicName, $existingTopics)) {
                    $notFoundTopics[] = $topicName;
                }
            }
        }

        if ($notFoundTopics) {
            $this->output->writeln(join("\n", $notFoundTopics));
        }

        if ($this->option('validate')) {
            if ($notFoundTopics) {
                $notFoundTopicsCount = count($notFoundTopics);
                $this->output->writeln("\nThere are {$notFoundTopicsCount} not created topics");

                return self::FAILURE;
            } else {
                $this->output->writeln("All {$totalDesiredTopics} desired topics exists");

                return self::SUCCESS;
            }
        }

        return self::SUCCESS;
    }

    private function getExistingTopics(string $connectionName): array
    {
        $rdKafka = KafkaFacade::rdKafka($connectionName);
        $metadata = $rdKafka->getMetadata(true, null, 2000);

        $existingTopics = [];
        foreach ($metadata->getTopics() as $topicMeta) {
            $existingTopics[] = $topicMeta->getTopic();
        }

        return $existingTopics;
    }
}
