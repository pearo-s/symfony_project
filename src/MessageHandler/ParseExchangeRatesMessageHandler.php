<?php

namespace App\MessageHandler;

use App\Message\ParseExchangeRatesMessage;
use App\Service\ExchangeRateParser;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ParseExchangeRatesMessageHandler
{
    private ExchangeRateParser $exchangeRateParser;

    public function __construct(ExchangeRateParser $exchangeRateParser) {
        $this->exchangeRateParser = $exchangeRateParser;
    }
    public function __invoke(ParseExchangeRatesMessage $message): void
    {
        $this->exchangeRateParser->fetchRates();
    }
}
