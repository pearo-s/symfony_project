<?php

namespace App\Scheduler;

use App\Message\ParseExchangeRatesMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('parse-exchange-rates')]
final class ParseExchangeRatesScheduler implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->add(
                RecurringMessage::every('1 minute', new ParseExchangeRatesMessage()),
            )
        ;
    }
}
