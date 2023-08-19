<?php

namespace App\Entity;

use App\Repository\PrintingOptionsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrintingOptionsRepository::class)]
class PrintingOptions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToMany(targetEntity: AirportHotel::class, inversedBy: 'printingOptions')]
    private Collection $Airport;



    public function __construct()
    {
        $this->Airport = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, AirportHotel>
     */
    public function getAirport(): Collection
    {
        return $this->Airport;
    }

    public function addAirport(AirportHotel $airport): self
    {
        if (!$this->Airport->contains($airport)) {
            $this->Airport->add($airport);
        }

        return $this;
    }

    public function removeAirport(AirportHotel $airport): self
    {
        $this->Airport->removeElement($airport);

        return $this;
    }


}
