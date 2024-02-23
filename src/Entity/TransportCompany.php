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

    #[ORM\OneToMany(mappedBy: 'transportCompany', targetEntity: TransferInterHotel::class)]
    private Collection $transferInterHotels;


    #[ORM\OneToMany(mappedBy: 'transportCompany', targetEntity: TransferDeparture::class)]
    private Collection $transferDepartures;


    public function __construct()
    {
        $this->transferVehicleArrivals = new ArrayCollection();
        $this->transferInterHotels = new ArrayCollection();
        $this->transferDepartures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {

        return (string) ucfirst(strtolower($this->name));
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
     * @return Collection<int, TransferInterHotel>
     */
    public function getTransferInterHotels(): Collection
    {
        return $this->transferInterHotels;
    }

    public function addTransferInterHotel(TransferInterHotel $transferInterHotel): static
    {
        if (!$this->transferInterHotels->contains($transferInterHotel)) {
            $this->transferInterHotels->add($transferInterHotel);
            $transferInterHotel->setTransportCompany($this);
        }

        return $this;
    }

    public function removeTransferInterHotel(TransferInterHotel $transferInterHotel): static
    {
        if ($this->transferInterHotels->removeElement($transferInterHotel)) {
            // set the owning side to null (unless already changed)
            if ($transferInterHotel->getTransportCompany() === $this) {
                $transferInterHotel->setTransportCompany(null);
            }
        }

        return $this;
    }
    public function addTransferDeparture(TransferDeparture $transferDeparture): static
    {
        if (!$this->transferDepartures->contains($transferDeparture)) {
            $this->transferDepartures->add($transferDeparture);
            $transferDeparture->setTransportCompany($this);
        }

        return $this;
    }

    public function removeTransferDeparture(TransferDeparture $transferDeparture): static
    {
        if ($this->transferDepartures->removeElement($transferDeparture)) {
            // set the owning side to null (unless already changed)
            if ($transferDeparture->getTransportCompany() === $this) {
                $transferDeparture->setTransportCompany(null);
            }
        }

        return $this;
    }
}
