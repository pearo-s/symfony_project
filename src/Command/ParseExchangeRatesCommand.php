<?php

namespace App\Command;

use App\Service\ExchangeRateParser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:parse-exchange-rates',
    description: 'Parse exchange rates from nbkr.kg',
)]
class ParseExchangeRatesCommand extends Command
{
    private ExchangeRateParser $parser;
    public function __construct(ExchangeRateParser $parser)
    {
        parent::__construct();
        $this->parser = $parser;
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->parser->fetchRates();
            echo 'Exchange rates successfully updated at ' . (new \DateTime())->setTimezone(new \DateTimeZone('Asia/Bishkek'))->format('Y-m-d H:i:s');
            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            echo 'Parse error: ' . $exception->getMessage();
            return Command::FAILURE;
        }
    }
}
