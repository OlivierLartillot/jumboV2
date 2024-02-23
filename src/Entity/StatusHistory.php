<?php

namespace App\Entity;

use App\Repository\StatusHistoryRepository;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatusHistoryRepository::class)]
class StatusHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Status $status = null;

    #[ORM\ManyToOne(inversedBy: 'statusHistories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CustomerCard $customerCard = null;

    #[ORM\ManyToOne(inversedBy: 'statusHistories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $updatedBy = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;


    public function __construct(){
        $this->createdAt = new DateTimeImmutable("now", new DateTimeZone('America/Santo_Domingo'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCustomerCard(): ?CustomerCard
    {
        return $this->customerCard;
    }

    public function setCustomerCard(?CustomerCard $customerCard): self
    {
        $this->customerCard = $customerCard;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

}
