<?php

namespace App\Entity;

use App\Repository\CheckedHistoryRepository;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CheckedHistoryRepository::class)]
class CheckedHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isChecked = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $type = null;

    #[ORM\ManyToOne(inversedBy: 'checkedHistories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CustomerCard $customerCard = null;

    #[ORM\ManyToOne(inversedBy: 'checkedHistories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $updatedBy = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct(){
        $this->createdAt = new DateTimeImmutable("now", new DateTimeZone('America/Santo_Domingo'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isIsChecked(): ?bool
    {
        return $this->isChecked;
    }

    public function setIsChecked(?bool $isChecked): static
    {
        $this->isChecked = $isChecked;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCustomerCard(): ?CustomerCard
    {
        return $this->customerCard;
    }

    public function setCustomerCard(?CustomerCard $customerCard): static
    {
        $this->customerCard = $customerCard;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): static
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
