<?php

namespace App\Scheduler;

use App\Message\ParseExchangeRatesMessage;
use App\Repository\WidgetSettingRepository;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('parse-exchange-rates')]
final class ParseExchangeRatesScheduler implements ScheduleProviderInterface
{
    private WidgetSettingRepository $widgetSettingRepository;
    public function __construct(WidgetSettingRepository $widgetSettingRepository) {
        $this->widgetSettingRepository = $widgetSettingRepository;
    }

    public function getSchedule(): Schedule
    {
        $frequency = $this->widgetSettingRepository->find(1)->getFrequency();

        $frequency = match ($frequency) {
            'minutely' => '1 minute',
            'hourly' => '2 minutes',
            'daily' => '0 0 * * *',
        };

        return (new Schedule())
            ->add(
                RecurringMessage::every($frequency, new ParseExchangeRatesMessage()),
            )
        ;
    }
}
