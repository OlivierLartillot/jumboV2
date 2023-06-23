<?php

namespace App\Entity;

use App\Repository\AirportHotelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AirportHotelRepository::class)]
class AirportHotel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $isAirport = null;

    #[ORM\OneToMany(mappedBy: 'fromStart', targetEntity: Transfer::class)]
    private Collection $transfers;

    #[ORM\OneToMany(mappedBy: 'toArrival', targetEntity: Transfer::class)]
    private Collection $transfersArrival;

    public function __construct()
    {
        $this->transfers = new ArrayCollection();
        $this->transfersArrival = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isIsAirport(): ?bool
    {
        return $this->isAirport;
    }

    public function setIsAirport(bool $IsAirport): self
    {
        $this->isAirport = $IsAirport;

        return $this;
    }

    /**
     * @return Collection<int, Transfer>
     */
    public function getTransfers(): Collection
    {
        return $this->transfers;
    }

    public function addTransfer(Transfer $transfer): self
    {
        if (!$this->transfers->contains($transfer)) {
            $this->transfers->add($transfer);
            $transfer->setFromStart($this);
        }

        return $this;
    }

    public function removeTransfer(Transfer $transfer): self
    {
        if ($this->transfers->removeElement($transfer)) {
            // set the owning side to null (unless already changed)
            if ($transfer->getFromStart() === $this) {
                $transfer->setFromStart(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transfer>
     */
    public function getTransfersArrival(): Collection
    {
        return $this->transfersArrival;
    }

    public function addTransfersArrival(Transfer $transfersArrival): self
    {
        if (!$this->transfersArrival->contains($transfersArrival)) {
            $this->transfersArrival->add($transfersArrival);
            $transfersArrival->setToArrival($this);
        }

        return $this;
    }

    public function removeTransfersArrival(Transfer $transfersArrival): self
    {
        if ($this->transfersArrival->removeElement($transfersArrival)) {
            // set the owning side to null (unless already changed)
            if ($transfersArrival->getToArrival() === $this) {
                $transfersArrival->setToArrival(null);
            }
        }

        return $this;
    }


    
}
