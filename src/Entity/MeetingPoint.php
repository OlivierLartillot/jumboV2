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

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $es = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $fr = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $it = null;

    #[ORM\OneToMany(mappedBy: 'meetingPoint', targetEntity: TransferArrival::class)]
    private Collection $transferArrivals;

    public function __construct()
    {
        $this->transferArrivals = new ArrayCollection();
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

    /**
     * @return Collection<int, TransferArrival>
     */
    public function getTransferArrivals(): Collection
    {
        return $this->transferArrivals;
    }

    public function addTransferArrival(TransferArrival $transferArrival): static
    {
        if (!$this->transferArrivals->contains($transferArrival)) {
            $this->transferArrivals->add($transferArrival);
            $transferArrival->setMeetingPoint($this);
        }

        return $this;
    }

    public function removeTransferArrival(TransferArrival $transferArrival): static
    {
        if ($this->transferArrivals->removeElement($transferArrival)) {
            // set the owning side to null (unless already changed)
            if ($transferArrival->getMeetingPoint() === $this) {
                $transferArrival->setMeetingPoint(null);
            }
        }

        return $this;
    }
}
