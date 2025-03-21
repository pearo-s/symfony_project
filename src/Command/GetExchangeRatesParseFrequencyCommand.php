<?php

namespace App\Command;

use App\Repository\WidgetSettingRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:get-exchange-rates-parse-frequency',
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
            ->setDescription('Get the execution frequency of the app:parse-exchange-rates command from the widget_setting table')
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
