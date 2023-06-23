<?php

namespace App\Entity;

use App\Repository\TransferArrivalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransferArrivalRepository::class)]
class TransferArrival
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $serviceNumber = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateHour = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $flightNumber = null;

    #[ORM\ManyToOne(inversedBy: 'transferArrivals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CustomerCard $customerCard = null;

    #[ORM\ManyToOne(inversedBy: 'transferArrivals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AirportHotel $fromStart = null;

    #[ORM\ManyToOne(inversedBy: 'transferArrivals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AirportHotel $toArrival = null;

    #[ORM\Column]
    private ?bool $isCollective = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getServiceNumber(): ?int
    {
        return $this->serviceNumber;
    }

    public function setServiceNumber(int $serviceNumber): self
    {
        $this->serviceNumber = $serviceNumber;

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

    public function getCustomerCard(): ?CustomerCard
    {
        return $this->customerCard;
    }

    public function setCustomerCard(?CustomerCard $customerCard): self
    {
        $this->customerCard = $customerCard;

        return $this;
    }

    public function getFromStart(): ?AirportHotel
    {
        return $this->fromStart;
    }

    public function setFromStart(?AirportHotel $fromStart): self
    {
        $this->fromStart = $fromStart;

        return $this;
    }

    public function getToArrival(): ?AirportHotel
    {
        return $this->toArrival;
    }

    public function setToArrival(?AirportHotel $toArrival): self
    {
        $this->toArrival = $toArrival;

        return $this;
    }

    public function isIsCollective(): ?bool
    {
        return $this->isCollective;
    }

    public function setIsCollective(bool $isCollective): self
    {
        $this->isCollective = $isCollective;

        return $this;
    }
}
