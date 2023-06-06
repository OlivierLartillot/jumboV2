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

    #[ORM\Column(length: 24, nullable: true)]
    private ?string $serviceNumber = null;

    #[ORM\Column(length: 24)]
    private ?string $natureTransfer = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateHour = null;

    #[ORM\Column(length: 24, nullable: true)]
    private ?string $flightNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $fromStart = null;

    #[ORM\Column(length: 255)]
    private ?string $toArrival = null;

    #[ORM\Column(length: 24, nullable: true)]
    private ?string $privateCollective = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $adultsNumber = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $childrenNumber = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $babiesNumber = null;

    #[ORM\ManyToOne(inversedBy: 'transfers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CustomerCard $customerCard = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getServiceNumber(): ?string
    {
        return $this->serviceNumber;
    }

    public function setServiceNumber(?string $serviceNumber): self
    {
        $this->serviceNumber = $serviceNumber;

        return $this;
    }

    public function getNatureTransfer(): ?string
    {
        return $this->natureTransfer;
    }

    public function setNatureTransfer(string $natureTransfer): self
    {
        $this->natureTransfer = $natureTransfer;

        return $this;
    }

    public function getDateHour(): ?\DateTimeImmutable
    {
        return $this->dateHour;
    }

    public function setDateHour(\DateTimeImmutable $dateHour): self
    {
        $this->dateHour = $dateHour;

        return $this;
    }

    public function getFlightNumber(): ?string
    {
        return $this->flightNumber;
    }

    public function setFlightNumber(?string $flightNumber): self
    {
        $this->flightNumber = $flightNumber;

        return $this;
    }

    public function getFromStart(): ?string
    {
        return $this->fromStart;
    }

    public function setFromStart(string $fromStart): self
    {
        $this->fromStart = $fromStart;

        return $this;
    }

    public function getToArrival(): ?string
    {
        return $this->toArrival;
    }

    public function setToArrival(string $toArrival): self
    {
        $this->toArrival = $toArrival;

        return $this;
    }

    public function getPrivateCollective(): ?string
    {
        return $this->privateCollective;
    }

    public function setPrivateCollective(?string $privateCollective): self
    {
        $this->privateCollective = $privateCollective;

        return $this;
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
