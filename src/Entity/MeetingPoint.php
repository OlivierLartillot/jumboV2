<?php

namespace App\Entity;

use App\Repository\MeetingPointRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MeetingPointRepository::class)]
class MeetingPoint
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $en = null;

    #[ORM\OneToMany(mappedBy: 'meetingPoint', targetEntity: CustomerCard::class)]
    private Collection $customerCards;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $es = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $fr = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $it = null;

    public function __construct()
    {
        $this->customerCards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEn(): ?string
    {
        return $this->en;
    }

    public function setEn(string $en): self
    {
        $this->en = $en;

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
            $customerCard->setMeetingPoint($this);
        }

        return $this;
    }

    public function removeCustomerCard(CustomerCard $customerCard): self
    {
        if ($this->customerCards->removeElement($customerCard)) {
            // set the owning side to null (unless already changed)
            if ($customerCard->getMeetingPoint() === $this) {
                $customerCard->setMeetingPoint(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->en; 
    }

    public function getEs(): ?string
    {
        return $this->es;
    }

    public function setEs(?string $es): self
    {
        $this->es = $es;

        return $this;
    }

    public function getFr(): ?string
    {
        return $this->fr;
    }

    public function setFr(?string $fr): self
    {
        $this->fr = $fr;

        return $this;
    }

    public function getIt(): ?string
    {
        return $this->it;
    }

    public function setIt(?string $it): self
    {
        $this->it = $it;

        return $this;
    }

    public function checklanguage($lang) {

        if ($lang == null) {
            $lang = 'en';
        }

        return $this->$lang;
    }
}
