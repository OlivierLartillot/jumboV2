<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PredefinedCommentsMessagesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PredefinedCommentsMessagesRepository::class)]
#[ApiResource]
class PredefinedCommentsMessages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $messageToDisplay = null;

    #[ORM\OneToMany(mappedBy: 'predefinedCommentsMessages', targetEntity: Comment::class)]
    private Collection $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }


    public function __toString()
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMessageToDisplay(): ?string
    {
        return $this->messageToDisplay;
    }

    public function setMessageToDisplay(string $messageToDisplay): self
    {
        $this->messageToDisplay = $messageToDisplay;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPredefinedCommentsMessages($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPredefinedCommentsMessages() === $this) {
                $comment->setPredefinedCommentsMessages(null);
            }
        }

        return $this;
    }


}
