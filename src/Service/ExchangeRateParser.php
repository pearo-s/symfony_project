<?php

namespace App\Service;

use App\Entity\ExchangeRate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class ExchangeRateParser
{
    private EntityManagerInterface $entityManager;
    private string $url = 'https://nbkr.kg';

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function fetchRates(): void
    {
        $client = HttpClient::create([
            'resolve' => ['nbkr.kg' => '37.18.36.142'],
        ]);
        $response = $client->request('GET', $this->url);
        $html = $response->getContent();

        $crawler = new Crawler($html);

        $dates = explode(' ', $crawler->filter('.exchange-rates-head table tbody tr')->eq(0)->text());

        $crawler->filter('.exchange-rates-body table tbody tr')->each(function (Crawler $row) use ($dates) {
            $currency = trim($row->filter('.excurr')->text());
            $todayRate = str_replace(',', '.', $row->filter('.exrate')->eq(0)->text());
            $tomorrowRate = str_replace(',', '.', $row->filter('.exrate')->eq(1)->text());


            $exchangeRate = new ExchangeRate();
            $exchangeRate->setCurrency($currency);
            $exchangeRate->setTodayRate($todayRate);
            $exchangeRate->setTomorrowRate($tomorrowRate);
            $exchangeRate->setTodayDate($dates[0]);
            $exchangeRate->setTomorrowDate($dates[1]);

            $this->entityManager->persist($exchangeRate);
        });

        $this->entityManager->flush();
    }
}