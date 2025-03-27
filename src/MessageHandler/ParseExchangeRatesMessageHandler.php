<?php

namespace App\MessageHandler;

use App\Message\ParseExchangeRatesMessage;
use App\Repository\WidgetSettingRepository;
use App\Service\ExchangeRateParser;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;


#[AsMessageHandler]
final class ParseExchangeRatesMessageHandler
{
    public function __construct(
        private WidgetSettingRepository $widgetSettingRepository,
        private ExchangeRateParser      $exchangeRateParser
    )
    {
    }

    public function __invoke(ParseExchangeRatesMessage $message): void
    {
        $setting = $this->widgetSettingRepository->find(1);
        $frequency = $setting->getFrequency();

        if ($frequency === 'disable') {
            return;
        }

        $lastParsedDate = $setting->getLastParsedTime();
        $now = (new \DateTime)->setTimezone(new \DateTimeZone('Asia/Bishkek'));

        $shouldParse = match ($frequency) {
            'minutely' => true,
            'hourly' => $this->shouldParseHourly($lastParsedDate, $now),
            'daily' => $this->shouldParseDaily($lastParsedDate, $now),
            default => false,
        };

        if ($shouldParse) {
            $this->exchangeRateParser->fetchRates();
        }
    }

    private function shouldParseHourly(?\DateTime $lastParsedDate, \DateTime $now): bool
    {
        if (!$lastParsedDate) {
            return true;
        }

        $lastParsedDate = strtotime($lastParsedDate->format('Y-m-d H:i:s'));
        $now = strtotime($now->format('Y-m-d H:i:s'));


        $diff = $now - $lastParsedDate;

        return $diff >= 3600; //Если прошел как минимум час
    }

    private function shouldParseDaily(?\DateTime $lastParsedDate, \DateTime $now): bool
    {
        if (!$lastParsedDate) {
            return true;
        }

        $parseHour = '17';

        $nowHour = $now->format('H');
        $nowDay = $now->format('d');
        $lastParsedHour = $lastParsedDate->format('H');
        $lastParsedDay = $lastParsedDate->format('d');

        return $nowHour === $parseHour && ($lastParsedHour !== $parseHour || $nowDay !== $lastParsedDay); // Каждый день в определенный час
    }
}
