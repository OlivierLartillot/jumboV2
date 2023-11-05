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

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $vehicleType = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $voucherNumber = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $area = null;

    #[ORM\ManyToOne(inversedBy: 'transferVehicleArrivals')]
    private ?TransportCompany $transportCompany = null;

    #[ORM\OneToOne(inversedBy: 'transferVehicleArrival', cascade: ['persist', 'remove'])]
    private ?TransferArrival $transferArrival = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $adultsNumber = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $childrenNumber = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $babiesNumber = null;

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

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;

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
        return 'vehicleArrival';
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

    public function getTransferArrival(): ?TransferArrival
    {
        return $this->transferArrival;
    }

    public function setTransferArrival(?TransferArrival $transferArrival): static
    {
        $this->transferArrival = $transferArrival;

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

}
