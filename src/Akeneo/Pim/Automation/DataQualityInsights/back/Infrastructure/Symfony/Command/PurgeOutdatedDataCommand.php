<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\PurgeOutdatedData;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Command to launch manually a purge of the outdated data persisted for Data-Quality-Insights
 * It aims to be used as a replacement of the purge job. So the option date should be the date the job should have run.
 */
final class PurgeOutdatedDataCommand extends Command
{
    protected static $defaultName = 'pim:data-quality-insights:purge-outdated-data';
    protected static $defaultDescription = 'Purge the outdated data persisted for Data-Quality-Insights.';

    private PurgeOutdatedData $purgeOutdatedData;

    public function __construct(PurgeOutdatedData $purgeOutdatedData)
    {
        parent::__construct();

        $this->purgeOutdatedData = $purgeOutdatedData;
    }

    protected function configure()
    {
        $this->addOption(
            'date',
            'd',
            InputOption::VALUE_REQUIRED,
            'Date from which the purge will be launched (Y-m-d)',
            date('Y-m-d')
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $purgeDate = \DateTimeImmutable::createFromFormat('Y-m-d', $input->getOption('date'));

        if (!$purgeDate instanceof \DateTimeImmutable) {
            throw new \InvalidArgumentException(sprintf('The purge date "%s" is invalid.', $input->getOption('date')));
        }

        if ($purgeDate > new \DateTimeImmutable('now')) {
            throw new \InvalidArgumentException('The purge date cannot be in the future.');
        }

        if (!$this->confirmPurge($input, $output)) {
            return Command::SUCCESS;
        }

        $this->purgeOutdatedData($purgeDate, $output);

        return Command::SUCCESS;
    }

    private function purgeOutdatedData(\DateTimeImmutable $purgeDate, OutputInterface $output)
    {
        $output->writeln('Start to purge dashboard projection rates and product scores.');
        $this->purgeOutdatedData->purgeAllFrom($purgeDate);
        $output->writeln('Purge done.');
    }

    private function confirmPurge(InputInterface $input, OutputInterface $output): bool
    {
        $question = new ConfirmationQuestion(
            'Purge all the outdated data? [y/n]',
            false
        );

        if (!$this->getHelper('question')->ask($input, $output, $question)) {
            $output->writeln('Purge aborted');

            return false;
        };

        return true;
    }
}
