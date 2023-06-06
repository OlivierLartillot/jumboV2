<?php

namespace App\Entity;

use App\Repository\TransferJoanRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransferJoanRepository::class)]
class TransferJoan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $fromStartJoan = null;

    #[ORM\Column(length: 255)]
    private ?string $toArrivalJoan = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateHourJoan = null;

    #[ORM\Column(length: 24, nullable: true)]
    private ?string $flightNumberJoan = null;

    #[ORM\Column(length: 24, nullable: true)]
    private ?string $privateCollectiveJoan = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $pickupTime = null;

    #[ORM\Column(length: 60)]
    private ?string $transportCompany = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $vehicleNumber = null;

    #[ORM\Column(length: 24)]
    private ?string $vehicleType = null;

    #[ORM\Column(length: 24)]
    private ?string $transferArea = null;

    #[ORM\Column(length: 24)]
    private ?string $voucherNumber = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $adultsNumber = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $chuldrenNumber = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $babiesNumber = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromStartJoan(): ?string
    {
        return $this->fromStartJoan;
    }

    public function setFromStartJoan(string $fromStartJoan): self
    {
        $this->fromStartJoan = $fromStartJoan;

        return $this;
    }

    public function getToArrivalJoan(): ?string
    {
        return $this->toArrivalJoan;
    }

    public function setToArrivalJoan(string $toArrivalJoan): self
    {
        $this->toArrivalJoan = $toArrivalJoan;

        return $this;
    }

    public function getDateHourJoan(): ?\DateTimeImmutable
    {
        return $this->dateHourJoan;
    }

    public function setDateHourJoan(\DateTimeImmutable $dateHourJoan): self
    {
        $this->dateHourJoan = $dateHourJoan;

        return $this;
    }

    public function getFlightNumberJoan(): ?string
    {
        return $this->flightNumberJoan;
    }

    public function setFlightNumberJoan(?string $flightNumberJoan): self
    {
        $this->flightNumberJoan = $flightNumberJoan;

        return $this;
    }

    public function getPrivateCollectiveJoan(): ?string
    {
        return $this->privateCollectiveJoan;
    }

    public function setPrivateCollectiveJoan(?string $privateCollectiveJoan): self
    {
        $this->privateCollectiveJoan = $privateCollectiveJoan;

        return $this;
    }

    public function getPickupTime(): ?\DateTimeImmutable
    {
        return $this->pickupTime;
    }

    public function setPickupTime(\DateTimeImmutable $pickupTime): self
    {
        $this->pickupTime = $pickupTime;

        return $this;
    }

    public function getTransportCompany(): ?string
    {
        return $this->transportCompany;
    }

    public function setTransportCompany(string $transportCompany): self
    {
        $this->transportCompany = $transportCompany;

        return $this;
    }

    public function getVehicleNumber(): ?int
    {
        return $this->vehicleNumber;
    }

    public function setVehicleNumber(int $vehicleNumber): self
    {
        $this->vehicleNumber = $vehicleNumber;

        return $this;
    }

    public function getVehicleType(): ?string
    {
        return $this->vehicleType;
    }

    public function setVehicleType(string $vehicleType): self
    {
        $this->vehicleType = $vehicleType;

        return $this;
    }

    public function getTransferArea(): ?string
    {
        return $this->transferArea;
    }

    public function setTransferArea(string $transferArea): self
    {
        $this->transferArea = $transferArea;

        return $this;
    }

    public function getVoucherNumber(): ?string
    {
        return $this->voucherNumber;
    }

    public function setVoucherNumber(string $voucherNumber): self
    {
        $this->voucherNumber = $voucherNumber;

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

    public function getChuldrenNumber(): ?int
    {
        return $this->chuldrenNumber;
    }

    public function setChuldrenNumber(?int $chuldrenNumber): self
    {
        $this->chuldrenNumber = $chuldrenNumber;

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
}
