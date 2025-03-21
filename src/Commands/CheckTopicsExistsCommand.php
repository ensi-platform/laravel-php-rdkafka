<?php

namespace Ensi\LaravelPhpRdKafka\Commands;

use Ensi\LaravelPhpRdKafka\KafkaFacade;
use Illuminate\Console\Command;

class CheckTopicsExistsCommand extends Command
{
    protected $signature = 'kafka:find-not-created-topics
                            {--validate : return error if there are not created topics}
                            {--file= : path to file in which to write a list of non-existent topics}';
    protected $description = 'Check if all topics from kafka.topics exist';

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
            $this->info("Not found topics: " . implode(', ', $notFoundTopics));
            $this->writeOutput(join("\n", $notFoundTopics));
        }

        if ($this->option('validate')) {
            if ($notFoundTopics) {
                $notFoundTopicsCount = count($notFoundTopics);
                $this->writeOutput("\nThere are {$notFoundTopicsCount} not created topics");

                return self::FAILURE;
            } else {
                $this->writeOutput("All {$totalDesiredTopics} desired topics exist");

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

    private function writeOutput(string $message): void
    {
        $filePath = $this->option('file');

        if ($filePath) {
            file_put_contents($filePath, $message, FILE_APPEND);
            $this->info("Filepath: {$filePath}");
            $this->info("User: " . get_current_user());
            $this->info("File exists: " . (file_exists($filePath) ? 'true' : 'false'));
            $this->info("Is writable: " . (is_writable(dirname($filePath)) ? 'true' : 'false'));

        } else {
            $this->output->writeln($message);
            $this->info("No filepath provided");
        }
    }
}
