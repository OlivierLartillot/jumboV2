<?php

namespace App\Entity;

use App\Repository\TransferVehicleDepartureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransferVehicleDepartureRepository::class)]
class TransferVehicleDeparture
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

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $pickUp = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $voucherNumber = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $area = null;

    #[ORM\ManyToOne(inversedBy: 'transferVehicleDepartures')]
    private ?TransportCompany $transportCompany = null;

    #[ORM\OneToOne(mappedBy: 'transferVehicleDeparture', cascade: ['persist', 'remove'])]
    private ?TransferDeparture $transferDeparture = null;


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

    public function getPickUp(): ?string
    {
        return $this->pickUp;
    }

    public function setPickUp(?string $pickUp): self
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

    public function getTransferDeparture(): ?TransferDeparture
    {
        return $this->transferDeparture;
    }

    public function setTransferDeparture(?TransferDeparture $transferDeparture): static
    {
        // unset the owning side of the relation if necessary
        if ($transferDeparture === null && $this->transferDeparture !== null) {
            $this->transferDeparture->setTransferVehicleDeparture(null);
        }

        // set the owning side of the relation if necessary
        if ($transferDeparture !== null && $transferDeparture->getTransferVehicleDeparture() !== $this) {
            $transferDeparture->setTransferVehicleDeparture($this);
        }

        $this->transferDeparture = $transferDeparture;

        return $this;
    }


}
