<?php

namespace App\Entity;

use App\Repository\CommentaryLikeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentaryLikeRepository::class)]
#[ORM\Table(name: 'commentary_likes')]
#[ORM\HasLifecycleCallbacks]
class CommentaryLike
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commentaryLikes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'commentaryLikes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commentary $commentary = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCommentary(): ?Commentary
    {
        return $this->commentary;
    }

    public function setCommentary(?Commentary $commentary): static
    {
        $this->commentary = $commentary;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): static
    {
        $this->created_at = (new \DateTimeImmutable())->setTimezone(new \DateTimeZone('Asia/Bishkek'));
        return $this;
    }
}
