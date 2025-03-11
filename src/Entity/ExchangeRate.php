<?php

namespace App\Entity;

use App\Repository\ExchangeRateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExchangeRateRepository::class)]
#[ORM\HasLifecycleCallbacks]
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

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at;

    #[ORM\Column(length: 15)]
    private ?string $today_date = null;

    #[ORM\Column(length: 15)]
    private ?string $tomorrow_date = null;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->created_at = (new \DateTimeImmutable())->setTimezone(new \DateTimeZone('Asia/Bishkek'));
    }

    public function getTodayDate(): ?string
    {
        return $this->today_date;
    }

    public function setTodayDate(?string $today_date): static
    {
        $this->today_date = $today_date;

        return $this;
    }

    public function getTomorrowDate(): ?string
    {
        return $this->tomorrow_date;
    }

    public function setTomorrowDate(string $tomorrow_date): static
    {
        $this->tomorrow_date = $tomorrow_date;

        return $this;
    }
}
