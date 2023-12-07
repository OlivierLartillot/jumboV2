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

    #[ORM\OneToMany(mappedBy: 'fromStart', targetEntity: TransferArrival::class)]
    private Collection $transferArrivals;

    #[ORM\OneToMany(mappedBy: 'toArrival', targetEntity: TransferArrival::class)]
    private Collection $transferToArrivals;

    #[ORM\OneToMany(mappedBy: 'fromStart', targetEntity: TransferInterHotel::class)]
    private Collection $transferInterHotels;

    #[ORM\OneToMany(mappedBy: 'toArrival', targetEntity: TransferInterHotel::class)]
    private Collection $transferToInterHotels;

    #[ORM\OneToMany(mappedBy: 'fromStart', targetEntity: TransferDeparture::class)]
    private Collection $transferDepartures;

    #[ORM\OneToMany(mappedBy: 'toArrival', targetEntity: TransferDeparture::class)]
    private Collection $transferToDepartures;

    #[ORM\ManyToMany(targetEntity: PrintingOptions::class, mappedBy: 'Airport')]
    private Collection $printingOptions;

    public function __construct()
    {
        $this->transferArrivals = new ArrayCollection();
        $this->transferToArrivals = new ArrayCollection();
        $this->transferInterHotels = new ArrayCollection();
        $this->transferToInterHotels = new ArrayCollection();
        $this->transferDepartures = new ArrayCollection();
        $this->transferToDepartures = new ArrayCollection();
        $this->printingOptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString()
    {
        return ucfirst($this->name);
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
     * @return Collection<int, TransferArrival>
     */
    public function getTransferArrivals(): Collection
    {
        return $this->transferArrivals;
    }

    public function addTransferArrival(TransferArrival $transferArrival): self
    {
        if (!$this->transferArrivals->contains($transferArrival)) {
            $this->transferArrivals->add($transferArrival);
            $transferArrival->setFromStart($this);
        }

        return $this;
    }

    public function removeTransferArrival(TransferArrival $transferArrival): self
    {
        if ($this->transferArrivals->removeElement($transferArrival)) {
            // set the owning side to null (unless already changed)
            if ($transferArrival->getFromStart() === $this) {
                $transferArrival->setFromStart(null);
            }
        }

        return $this;
    }    

    /**
     * @return Collection<int, TransferArrival>
     */
    public function getTransferToArrivals(): Collection
    {
        return $this->transferArrivals;
    }

    public function addTransferToArrival(TransferArrival $transferArrival): self
    {
        if (!$this->transferToArrivals->contains($transferArrival)) {
            $this->transferToArrivals->add($transferArrival);
            $transferArrival->setFromStart($this);
        }

        return $this;
    }

    public function removeTransferToArrival(TransferArrival $transferArrival): self
    {
        if ($this->transferToArrivals->removeElement($transferArrival)) {
            // set the owning side to null (unless already changed)
            if ($transferArrival->getFromStart() === $this) {
                $transferArrival->setFromStart(null);
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

    public function addTransferInterHotel(TransferInterHotel $transferInterHotel): self
    {
        if (!$this->transferInterHotels->contains($transferInterHotel)) {
            $this->transferInterHotels->add($transferInterHotel);
            $transferInterHotel->setFromStart($this);
        }

        return $this;
    }

    public function removeTransferInterHotel(TransferInterHotel $transferInterHotel): self
    {
        if ($this->transferInterHotels->removeElement($transferInterHotel)) {
            // set the owning side to null (unless already changed)
            if ($transferInterHotel->getFromStart() === $this) {
                $transferInterHotel->setFromStart(null);
            }
        }

        return $this;
    }


 /**
     * @return Collection<int, TransferInterHotel>
     */
    public function getTransferToInterHotels(): Collection
    {
        return $this->transferToInterHotels;
    }

    public function addTransferToInterHotel(TransferInterHotel $transferInterHotel): self
    {
        if (!$this->transferToInterHotels->contains($transferInterHotel)) {
            $this->transferToInterHotels->add($transferInterHotel);
            $transferInterHotel->setFromStart($this);
        }

        return $this;
    }

    public function removeTransferToInterHotel(TransferInterHotel $transferInterHotel): self
    {
        if ($this->transferToInterHotels->removeElement($transferInterHotel)) {
            // set the owning side to null (unless already changed)
            if ($transferInterHotel->getFromStart() === $this) {
                $transferInterHotel->setFromStart(null);
            }
        }

        return $this;
    }



   /**
     * @return Collection<int, TransferDeparture>
     */
    public function getTransferDepartures(): Collection
    {
        return $this->transferDepartures;
    }

    public function addTransferDeparture(TransferDeparture $transferDeparture): self
    {
        if (!$this->transferDepartures->contains($transferDeparture)) {
            $this->transferDepartures->add($transferDeparture);
            $transferDeparture->setFromStart($this);
        }

        return $this;
    }

    public function removeTransferDeparture(TransferDeparture $transferDeparture): self
    {
        if ($this->transferDepartures->removeElement($transferDeparture)) {
            // set the owning side to null (unless already changed)
            if ($transferDeparture->getFromStart() === $this) {
                $transferDeparture->setFromStart(null);
            }
        }

        return $this;
    }

       /**
     * @return Collection<int, TransferDeparture>
     */
    public function getTransferToDepartures(): Collection
    {
        return $this->transferToDepartures;
    }

    public function addTransferToDeparture(TransferDeparture $transferDeparture): self
    {
        if (!$this->transferToDepartures->contains($transferDeparture)) {
            $this->transferToDepartures->add($transferDeparture);
            $transferDeparture->setFromStart($this);
        }

        return $this;
    }

    public function removeTransferToDeparture(TransferDeparture $transferDeparture): self
    {
        if ($this->transferToDepartures->removeElement($transferDeparture)) {
            // set the owning side to null (unless already changed)
            if ($transferDeparture->getFromStart() === $this) {
                $transferDeparture->setFromStart(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, PrintingOptions>
     */
    public function getPrintingOptions(): Collection
    {
        return $this->printingOptions;
    }

    public function addPrintingOption(PrintingOptions $printingOption): self
    {
        if (!$this->printingOptions->contains($printingOption)) {
            $this->printingOptions->add($printingOption);
            $printingOption->addAirport($this);
        }

        return $this;
    }

    public function removePrintingOption(PrintingOptions $printingOption): self
    {
        if ($this->printingOptions->removeElement($printingOption)) {
            $printingOption->removeAirport($this);
        }

        return $this;
    }


}
