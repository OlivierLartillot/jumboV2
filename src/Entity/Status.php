<?php

namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'status', targetEntity: CustomerCard::class)]
    private Collection $customerCards;

    public function __construct()
    {
        $this->customerCards = new ArrayCollection();
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

    /**
     * @return Collection<int, CustomerCard>
     */
    public function getCustomerCards(): Collection
    {
        return $this->customerCards;
    }

    public function addCustomerCard(CustomerCard $customerCard): self
    {
        if (!$this->customerCards->contains($customerCard)) {
            $this->customerCards->add($customerCard);
            $customerCard->setStatus($this);
        }

        return $this;
    }

    public function removeCustomerCard(CustomerCard $customerCard): self
    {
        if ($this->customerCards->removeElement($customerCard)) {
            // set the owning side to null (unless already changed)
            if ($customerCard->getStatus() === $this) {
                $customerCard->setStatus(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name; 
    }
}
