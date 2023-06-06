<?php

namespace App\Entity;

use App\Repository\CustomerCardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerCardRepository::class)]
class CustomerCard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 24)]
    private ?string $reservationNumber = null;

    #[ORM\Column(length: 24)]
    private ?string $jumboNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $holder = null;

    #[ORM\Column(length: 255)]
    private ?string $agency = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $adultsNumber = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $childrenNumber = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $babiesNumber = null;

    #[ORM\ManyToOne(inversedBy: 'customerCards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Status $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $statusUpdatedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $statusUpdatedBy = null;

    #[ORM\ManyToOne(inversedBy: 'customerCards')]
    private ?MeetingPoint $meetingPoint = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $meetingAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $reservationCancelled = null;

    #[ORM\ManyToOne(inversedBy: 'customerCards')]
    private ?User $staff = null;

    #[ORM\OneToMany(mappedBy: 'customerCard', targetEntity: CustomerReport::class)]
    private Collection $customerReports;

    #[ORM\OneToMany(mappedBy: 'customerCard', targetEntity: Transfer::class)]
    private Collection $transfers;

    public function __construct()
    {
        $this->customerReports = new ArrayCollection();
        $this->transfers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    
    public function getReservationNumber(): ?string
    {
        return $this->reservationNumber;
    }
    
    public function setReservationNumber(string $reservationNumber): self
    {
        $this->reservationNumber = $reservationNumber;
        
        return $this;
    }
    
    public function getJumboNumber(): ?string
    {
        return $this->jumboNumber;
    }
    
    public function setJumboNumber(string $jumboNumber): self
    {
        $this->jumboNumber = $jumboNumber;
        
        return $this;
    }
    
    public function getHolder(): ?string
    {
        return $this->holder;
    }
    
    public function setHolder(string $holder): self
    {
        $this->holder = $holder;
        
        return $this;
    }
    
    public function getAgency(): ?string
    {
        return $this->agency;
    }
    
    public function setAgency(string $agency): self
    {
        $this->agency = $agency;
        
        return $this;
    }
    
    public function getAdultsNumber(): ?int
    {
        return $this->adultsNumber;
    }
    
    public function setAdultsNumber(?int $adultsNumber): self
    {
        $this->adultsNumber = $adultsNumber;
        
        return $this;
    }
    
    public function getChildrenNumber(): ?int
    {
        return $this->childrenNumber;
    }
    
    public function setChildrenNumber(?int $childrenNumber): self
    {
        $this->childrenNumber = $childrenNumber;
        
        return $this;
    }
    
    public function getBabiesNumber(): ?int
    {
        return $this->babiesNumber;
    }
    
    public function setBabiesNumber(?int $babiesNumber): self
    {
        $this->babiesNumber = $babiesNumber;
        
        return $this;
    }
    
    public function getStatus(): ?Status
    {
        return $this->status;
    }
    
    public function setStatus(?Status $status): self
    {
        $this->status = $status;
        
        return $this;
    }

    public function getStatusUpdatedAt(): ?\DateTimeInterface
    {
        return $this->statusUpdatedAt;
    }

    public function setStatusUpdatedAt(\DateTimeInterface $statusUpdatedAt): self
    {
        $this->statusUpdatedAt = $statusUpdatedAt;
        
        return $this;
    }
    
    public function getStatusUpdatedBy(): ?User
    {
        return $this->statusUpdatedBy;
    }
    
    public function setStatusUpdatedBy(?User $statusUpdatedBy): self
    {
        $this->statusUpdatedBy = $statusUpdatedBy;
        
        return $this;
    }

    public function getMeetingPoint(): ?MeetingPoint
    {
        return $this->meetingPoint;
    }
    
    public function setMeetingPoint(?MeetingPoint $meetingPoint): self
    {
        $this->meetingPoint = $meetingPoint;
        
        return $this;
    }
    
    public function getMeetingAt(): ?\DateTimeImmutable
    {
        return $this->meetingAt;
    }
    
    public function setMeetingAt(\DateTimeImmutable $meetingAt): self
    {
        $this->meetingAt = $meetingAt;
        
        return $this;
    }

    public function isReservationCancelled(): ?bool
    {
        return $this->reservationCancelled;
    }
    
    public function setReservationCancelled(?bool $reservationCancelled): self
    {
        $this->reservationCancelled = $reservationCancelled;
        
        return $this;
    }

    public function getStaff(): ?user
    {
        return $this->staff;
    }
    
    public function setStaff(?User $staff): self
    {
        $this->staff = $staff;
        
        return $this;
    }
    
    /**
     * @return Collection<int, CustomerReport>
     */
    public function getCustomerReports(): Collection
    {
        return $this->customerReports;
    }
    
    public function addCustomerReport(CustomerReport $customerReport): self
    {
        if (!$this->customerReports->contains($customerReport)) {
            $this->customerReports->add($customerReport);
            $customerReport->setCustomerCard($this);
        }
        
        return $this;
    }
    
    public function removeCustomerReport(CustomerReport $customerReport): self
    {
        if ($this->customerReports->removeElement($customerReport)) {
            // set the owning side to null (unless already changed)
            if ($customerReport->getCustomerCard() === $this) {
                $customerReport->setCustomerCard(null);
            }
        }
        
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
            $transfer->setCustomerCard($this);
        }
        
        return $this;
    }
    
    public function removeTransfer(Transfer $transfer): self
    {
        if ($this->transfers->removeElement($transfer)) {
            // set the owning side to null (unless already changed)
            if ($transfer->getCustomerCard() === $this) {
                $transfer->setCustomerCard(null);
            }
        }
        
        return $this;
    }

    public function getMeetingAtDate() {
        return $this->meetingAt->format('d-m-Y');
    }

    public function getMeetingAtTime() {
        return $this->meetingAt->format('H:i');
    }



    public function __toString()
    {
        return 'Nombre: ' . $this->getHolder() . 'Reservation: ' . $this->getReservationNumber() . ' - Jumbo: ' . $this->getJumboNumber();
    }
}
