<?php

namespace App\Entity;

use App\Repository\AgencyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgencyRepository::class)]
class Agency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'agency', targetEntity: CustomerCard::class)]
    private Collection $customerCards;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $language = null;

    #[ORM\ManyToMany(targetEntity: PrintingOptions::class, mappedBy: 'Agencies')]
    private Collection $printingOptions;

    public function __construct()
    {
        $this->language = 'en';
        $this->customerCards = new ArrayCollection();
        $this->printingOptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
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
            $customerCard->setAgency($this);
        }

        return $this;
    }

    public function removeCustomerCard(CustomerCard $customerCard): self
    {
        if ($this->customerCards->removeElement($customerCard)) {
            // set the owning side to null (unless already changed)
            if ($customerCard->getAgency() === $this) {
                $customerCard->setAgency(null);
            }
        }

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;

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
            $printingOption->addAgency($this);
        }

        return $this;
    }

    public function removePrintingOption(PrintingOptions $printingOption): self
    {
        if ($this->printingOptions->removeElement($printingOption)) {
            $printingOption->removeAgency($this);
        }

        return $this;
    }
}
