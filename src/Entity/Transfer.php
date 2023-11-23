<?php

namespace App\Entity;

use App\Repository\TransferRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransferRepository::class)]
class Transfer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;



    #[ORM\ManyToOne(inversedBy: 'transfers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CustomerCard $customerCard = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdultsNumber(): ?int
    {
        return $this->adultsNumber;
    }

    public function setAdultsNumber(?int $adultsNumber): self
    {
        $this->adultsNumber = $adultsNumber;

        return $this;
    }

    public function getChildrenNumber(): ?int
    {
        return $this->childrenNumber;
    }

    public function setChildrenNumber(?int $childrenNumber): self
    {
        $this->childrenNumber = $childrenNumber;

        return $this;
    }

    public function getBabiesNumber(): ?int
    {
        return $this->babiesNumber;
    }

    public function setBabiesNumber(?int $babiesNumber): self
    {
        $this->babiesNumber = $babiesNumber;

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

}
