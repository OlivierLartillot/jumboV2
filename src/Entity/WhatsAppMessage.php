<?php

namespace App\Entity;

use App\Repository\WhatsAppMessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WhatsAppMessageRepository::class)]
class WhatsAppMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'whatsAppMessages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $typeTransfer = null;

    #[ORM\Column(length: 3)]
    private ?string $language = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column]
    private ?bool $isDefaultMessage = null;

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

    public function getTypeTransfer(): ?int
    {
        return $this->typeTransfer;
    }

    public function setTypeTransfer(int $typeTransfer): static
    {
        $this->typeTransfer = $typeTransfer;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function isIsDefaultMessage(): ?bool
    {
        return $this->isDefaultMessage;
    }

    public function setIsDefaultMessage(bool $isDefaultMessage): static
    {
        $this->isDefaultMessage = $isDefaultMessage;

        return $this;
    }
}
