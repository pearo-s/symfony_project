<?php

namespace App\Entity;

use App\Repository\CommentaryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: CommentaryRepository::class)]
#[ORM\Table(name: 'commentaries')]
#[ORM\HasLifecycleCallbacks]
class Commentary
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $text = null;

    #[ORM\ManyToOne(inversedBy: 'commentaries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'commentaries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $post = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $is_moderated = false;

    /**
     * @var Collection<int, CommentaryLike>
     */
    #[ORM\OneToMany(targetEntity: CommentaryLike::class, mappedBy: 'commentary', cascade: ['remove'], orphanRemoval: true)]
    private Collection $commentaryLikes;

    public function __construct()
    {
        $this->commentaryLikes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
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

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): static
    {
        $this->updated_at = (new \DateTimeImmutable())->setTimezone(new \DateTimeZone('Asia/Bishkek'));
        return $this;
    }

    public function isModerated(): ?bool
    {
        return $this->is_moderated;
    }

    public function setIsModerated(bool $is_moderated): static
    {
        $this->is_moderated = $is_moderated;

        return $this;
    }

    /**
     * @return Collection<int, CommentaryLike>
     */
    public function getCommentaryLikes(): Collection
    {
        return $this->commentaryLikes;
    }

    public function addCommentaryLike(CommentaryLike $commentaryLike): static
    {
        if (!$this->commentaryLikes->contains($commentaryLike)) {
            $this->commentaryLikes->add($commentaryLike);
            $commentaryLike->setCommentary($this);
        }

        return $this;
    }

    public function removeCommentaryLike(CommentaryLike $commentaryLike): static
    {
        if ($this->commentaryLikes->removeElement($commentaryLike)) {
            // set the owning side to null (unless already changed)
            if ($commentaryLike->getCommentary() === $this) {
                $commentaryLike->setCommentary(null);
            }
        }

        return $this;
    }

    public function isLikedBy(UserInterface $user): bool
    {
        foreach ($this->commentaryLikes as $commentaryLike) {
            if ($commentaryLike->getUser() === $user) {
                return true;
            }
        }
        return false;
    }
}
