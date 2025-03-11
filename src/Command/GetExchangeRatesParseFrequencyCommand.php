<?php

namespace App\Command;

use App\Repository\WidgetSettingRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:get-exchange-rates-parse-frequency',
    description: 'The frequency of parsing nbkr.kg',
)]
class GetExchangeRatesParseFrequencyCommand extends Command
{
    private WidgetSettingRepository $widgetSettingRepository;
    public function __construct(WidgetSettingRepository $widgetSettingRepository)
    {
        parent::__construct();
        $this->widgetSettingRepository = $widgetSettingRepository;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $frequency = $this->widgetSettingRepository->find(1);

        if (!$frequency) {
            $output->writeln('daily'); //default
            return Command::FAILURE;
        }

        $output->writeln($frequency->getFrequency());

        return Command::SUCCESS;
    }
}
