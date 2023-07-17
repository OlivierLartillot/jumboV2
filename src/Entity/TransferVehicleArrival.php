<?php

namespace App\Entity;

use App\Repository\TransferVehicleArrivalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransferVehicleArrivalRepository::class)]
class TransferVehicleArrival
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $isCollective = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $vehicleNumber = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $vehicleType = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $pickUp = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $transportCompany = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $voucherNumber = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $area = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?CustomerCard $customerCard = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function setPickUp(?\DateTimeImmutable $pickUp): self
    {
        $this->pickUp = $pickUp;

        return $this;
    }

    public function getTransportCompany(): ?string
    {
        return $this->transportCompany;
    }

    public function setTransportCompany(?string $transportCompany): self
    {
        $this->transportCompany = $transportCompany;

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

    public function getCustomerCard(): ?CustomerCard
    {
        return $this->customerCard;
    }

    public function setCustomerCard(CustomerCard $customerCard): self
    {
        $this->customerCard = $customerCard;

        return $this;
    }
}
