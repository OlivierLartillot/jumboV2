<?php

namespace App\Entity;

use App\Repository\TransferDepartureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransferDepartureRepository::class)]
class TransferDeparture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateHour = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $flightNumber = null;

    #[ORM\ManyToOne(inversedBy: 'transferDepartures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CustomerCard $customerCard = null;

    #[ORM\ManyToOne(inversedBy: 'transferDepartures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AirportHotel $fromStart = null;

    #[ORM\ManyToOne(inversedBy: 'transferDepartures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AirportHotel $toArrival = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $hour = null;

    #[ORM\OneToOne(inversedBy: 'transferDeparture', cascade: ['persist', 'remove'])]
    private ?TransferVehicleDeparture $transferVehicleDeparture = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getHour(): ?\DateTimeImmutable
    {
        return $this->hour;
    }

    public function setHour(\DateTimeImmutable $hour): self
    {
        $this->hour = $hour;

        return $this;
    }

    public function getTransferVehicleDeparture(): ?TransferVehicleDeparture
    {
        return $this->transferVehicleDeparture;
    }

    public function setTransferVehicleDeparture(?TransferVehicleDeparture $transferVehicleDeparture): static
    {
        $this->transferVehicleDeparture = $transferVehicleDeparture;

        return $this;
    }
}

