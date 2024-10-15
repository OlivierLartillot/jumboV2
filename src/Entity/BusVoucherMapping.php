<?php

namespace App\Entity;

use App\Repository\BusVoucherMappingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BusVoucherMappingRepository::class)]

class BusVoucherMapping
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['bus_correspondence_admin'])]
    private ?int $id = null;

    #[ORM\Column(length: 15)]
    #[Groups(groups: ['bus_correspondence_admin'])]
    private ?string $busNumber = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $voucherNumber = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $correspondenceDate = null;

    #[Groups(groups: ['bus_correspondence_admin'])]
    public function getAdminText(): ?string
    {
        return 'tu es bien co en admin!';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBusNumber(): ?string
    {
        return $this->busNumber;
    }

    public function setBusNumber(string $busNumber): static
    {
        $this->busNumber = $busNumber;

        return $this;
    }

    public function getVoucherNumber(): ?int
    {
        return $this->voucherNumber;
    }

    public function setVoucherNumber(int $voucherNumber): static
    {
        $this->voucherNumber = $voucherNumber;

        return $this;
    }

    public function getCorrespondenceDate(): ?\DateTimeInterface
    {
        return $this->correspondenceDate;
    }

    public function setCorrespondenceDate(\DateTimeInterface $correspondenceDate): static
    {
        $this->correspondenceDate = $correspondenceDate;

        return $this;
    }
}
