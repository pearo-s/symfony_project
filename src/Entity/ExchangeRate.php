<?php

namespace App\Entity;

use App\Repository\ExchangeRateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExchangeRateRepository::class)]
class ExchangeRate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $currency = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 4)]
    private ?float $todayRate = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 4)]
    private ?float $tomorrowRate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getTodayRate(): ?float
    {
        return $this->todayRate;
    }

    public function setTodayRate(float $todayRate): static
    {
        $this->todayRate = $todayRate;

        return $this;
    }

    public function getTomorrowRate(): ?float
    {
        return $this->tomorrowRate;
    }

    public function setTomorrowRate(float $tomorrowRate): static
    {
        $this->tomorrowRate = $tomorrowRate;

        return $this;
    }
}
