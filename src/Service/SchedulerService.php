<?php

namespace App\Service;

use App\Message\ParseExchangeRatesMessage;
use App\Repository\WidgetSettingRepository;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;

class SchedulerService
{
    private WidgetSettingRepository $widgetSettingRepository;

    public function __construct(WidgetSettingRepository $widgetSettingRepository)
    {
        $this->widgetSettingRepository = $widgetSettingRepository;
    }

    public function configureSchedule(Schedule $schedule): void
    {
        $frequency = $this->widgetSettingRepository->find(1)->getFrequency();

        $cronExpression = $this->getCronExpression($frequency);

        if ($cronExpression) {
            $schedule->add(
                RecurringMessage::cron($cronExpression, new ParseExchangeRatesMessage())
            );
        }
    }

    private function getCronExpression(string $scheduleType): ?string
    {
        switch ($scheduleType) {
            case 'minutely':
                return '* * * * *';
            case 'hourly':
                return '0 * * * *';
            case 'daily':
                return '0 17 * * *';
            default:
                return null;
        }
    }
}