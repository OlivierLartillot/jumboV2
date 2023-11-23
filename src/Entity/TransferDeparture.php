<?php

namespace App\Entity;

use App\Repository\TransferDepartureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransferDepartureRepository::class)]
class TransferDeparture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transferDepartures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CustomerCard $customerCard = null;

    #[ORM\ManyToOne(inversedBy: 'transferDepartures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AirportHotel $fromStart = null;

    #[ORM\ManyToOne(inversedBy: 'transferDepartures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AirportHotel $toArrival = null;

    #[ORM\ManyToOne(inversedBy: 'transferDepartures')]
    #[ORM\JoinColumn(nullable: false)]
    private TransportCompany $transportCompany;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $flightNumber = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $hour = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $pickUp = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $vehicleNumber = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $vehicleType = null;

    #[ORM\Column]
    private ?bool $isCollective = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $voucherNumber = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $area = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $adultsNumber = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $childrenNumber = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $babiesNumber = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $duplicateIgnored = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable('now');
        $this->duplicateIgnored = false;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function isIsCollective(): ?bool
    {
        return $this->isCollective;
    }

    public function setIsCollective(bool $isCollective): self
    {
        $this->isCollective = $isCollective;

        return $this;
    }

    public function getVehicleNumber(): ?int
    {
        return $this->vehicleNumber;
    }

    public function setVehicleNumber(?int $vehicleNumber): self
    {
        $this->vehicleNumber = $vehicleNumber;

        return $this;
    }

    public function getVehicleType(): ?string
    {
        return $this->vehicleType;
    }

    public function setVehicleType(?string $vehicleType): self
    {
        $this->vehicleType = $vehicleType;

        return $this;
    }

    public function getPickUp(): ?\DateTimeImmutable
    {
        return $this->pickUp;
    }

    public function setPickUp(\DateTimeImmutable $pickUp): self
    {
        $this->pickUp = $pickUp;

        return $this;
    }

    public function getVoucherNumber(): ?string
    {
        return $this->voucherNumber;
    }

    public function setVoucherNumber(?string $voucherNumber): self
    {
        $this->voucherNumber = $voucherNumber;

        return $this;
    }

    public function getArea(): ?string
    {
        return $this->area;
    }

    public function setArea(?string $area): self
    {
        $this->area = $area;

        return $this;
    }

    public function getTypeTransfer() {
        return 'vehicleDeparture';
    }

    public function getTransportCompany(): ?TransportCompany
    {
        return $this->transportCompany;
    }

    public function setTransportCompany(?TransportCompany $transportCompany): static
    {
        $this->transportCompany = $transportCompany;

        return $this;
    }

    public function getAdultsNumber(): ?int
    {
        return $this->adultsNumber;
    }

    public function setAdultsNumber(?int $adultsNumber): static
    {
        $this->adultsNumber = $adultsNumber;

        return $this;
    }

    public function getChildrenNumber(): ?int
    {
        return $this->childrenNumber;
    }

    public function setChildrenNumber(?int $childrenNumber): static
    {
        $this->childrenNumber = $childrenNumber;

        return $this;
    }

    public function getBabiesNumber(): ?int
    {
        return $this->babiesNumber;
    }

    public function setBabiesNumber(?int $babiesNumber): static
    {
        $this->babiesNumber = $babiesNumber;

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

    public function isDuplicateIgnored(): ?bool
    {
        return $this->duplicateIgnored;
    }

    public function setDuplicateIgnored(?bool $duplicateIgnored): static
    {
        $this->duplicateIgnored = $duplicateIgnored;

        return $this;
    }

}

