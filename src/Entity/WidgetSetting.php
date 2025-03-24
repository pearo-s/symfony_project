<?php

namespace App\Entity;

use App\Repository\WidgetSettingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WidgetSettingRepository::class)]
class WidgetSetting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $frequency = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $is_shown = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $last_parsed_time = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    public function setFrequency(string $frequency): static
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function isShown(): ?bool
    {
        return $this->is_shown;
    }

    public function setIsShown(bool $is_shown): static
    {
        $this->is_shown = $is_shown;

        return $this;
    }

    public function getLastParsedTime(): ?\DateTime
    {
        return $this->last_parsed_time;
    }

    public function setLastParsedTime(?\DateTime $last_parsed_time): static
    {
        $this->last_parsed_time = $last_parsed_time;

        return $this;
    }
}
