<?php

namespace App\Entity;

use App\Repository\TransportCompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransportCompanyRepository::class)]
class TransportCompany
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'transportCompany', targetEntity: TransferVehicleArrival::class)]
    private Collection $transferVehicleArrivals;

    #[ORM\OneToMany(mappedBy: 'transportCompany', targetEntity: TransferVehicleInterHotel::class)]
    private Collection $transferVehicleInterHotels;

    #[ORM\OneToMany(mappedBy: 'transportCompany', targetEntity: TransferVehicleDeparture::class)]
    private Collection $transferVehicleDepartures;

    public function __construct()
    {
        $this->transferVehicleArrivals = new ArrayCollection();
        $this->transferVehicleInterHotels = new ArrayCollection();
        $this->transferVehicleDepartures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, TransferVehicleArrival>
     */
    public function getTransferVehicleArrivals(): Collection
    {
        return $this->transferVehicleArrivals;
    }

    public function addTransferVehicleArrival(TransferVehicleArrival $transferVehicleArrival): static
    {
        if (!$this->transferVehicleArrivals->contains($transferVehicleArrival)) {
            $this->transferVehicleArrivals->add($transferVehicleArrival);
            $transferVehicleArrival->setTransportCompany($this);
        }

        return $this;
    }

    public function removeTransferVehicleArrival(TransferVehicleArrival $transferVehicleArrival): static
    {
        if ($this->transferVehicleArrivals->removeElement($transferVehicleArrival)) {
            // set the owning side to null (unless already changed)
            if ($transferVehicleArrival->getTransportCompany() === $this) {
                $transferVehicleArrival->setTransportCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TransferVehicleInterHotel>
     */
    public function getTransferVehicleInterHotels(): Collection
    {
        return $this->transferVehicleInterHotels;
    }

    public function addTransferVehicleInterHotel(TransferVehicleInterHotel $transferVehicleInterHotel): static
    {
        if (!$this->transferVehicleInterHotels->contains($transferVehicleInterHotel)) {
            $this->transferVehicleInterHotels->add($transferVehicleInterHotel);
            $transferVehicleInterHotel->setTransportCompany($this);
        }

        return $this;
    }

    public function removeTransferVehicleInterHotel(TransferVehicleInterHotel $transferVehicleInterHotel): static
    {
        if ($this->transferVehicleInterHotels->removeElement($transferVehicleInterHotel)) {
            // set the owning side to null (unless already changed)
            if ($transferVehicleInterHotel->getTransportCompany() === $this) {
                $transferVehicleInterHotel->setTransportCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TransferVehicleDeparture>
     */
    public function getTransferVehicleDepartures(): Collection
    {
        return $this->transferVehicleDepartures;
    }

    public function addTransferVehicleDeparture(TransferVehicleDeparture $transferVehicleDeparture): static
    {
        if (!$this->transferVehicleDepartures->contains($transferVehicleDeparture)) {
            $this->transferVehicleDepartures->add($transferVehicleDeparture);
            $transferVehicleDeparture->setTransportCompany($this);
        }

        return $this;
    }

    public function removeTransferVehicleDeparture(TransferVehicleDeparture $transferVehicleDeparture): static
    {
        if ($this->transferVehicleDepartures->removeElement($transferVehicleDeparture)) {
            // set the owning side to null (unless already changed)
            if ($transferVehicleDeparture->getTransportCompany() === $this) {
                $transferVehicleDeparture->setTransportCompany(null);
            }
        }

        return $this;
    }
}
