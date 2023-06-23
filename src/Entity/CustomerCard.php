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

    #[ORM\OneToMany(mappedBy: 'customerCard', targetEntity: StatusHistory::class, orphanRemoval: true)]
    private Collection $statusHistories;

    #[ORM\OneToMany(mappedBy: 'customerCard', targetEntity: TransferJoan::class)]
    private Collection $transferJoans;

    #[ORM\OneToMany(mappedBy: 'customerCard', targetEntity: Comment::class)]
    private Collection $comments;

    #[ORM\ManyToOne(inversedBy: 'customerCards')]
    private ?Agency $agency = null;

    #[ORM\OneToMany(mappedBy: 'customerCard', targetEntity: TransferArrival::class)]
    private Collection $transferArrivals;

    #[ORM\OneToMany(mappedBy: 'customerCard', targetEntity: TransferInterHotel::class)]
    private Collection $transferInterHotels;

    #[ORM\OneToMany(mappedBy: 'customerCard', targetEntity: TransferDeparture::class)]
    private Collection $transferDepartures;


    public function __construct()
    {
        $this->customerReports = new ArrayCollection();
        $this->statusHistories = new ArrayCollection();
        $this->transferJoans = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->transferInterHotels = new ArrayCollection(); 
        $this->transferDepartures = new ArrayCollection(); 
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

    /**
     * @return Collection<int, StatusHistory>
     */
    public function getStatusHistories(): Collection
    {
        return $this->statusHistories;
    }

    public function addStatusHistory(StatusHistory $statusHistory): self
    {
        if (!$this->statusHistories->contains($statusHistory)) {
            $this->statusHistories->add($statusHistory);
            $statusHistory->setCustomerCard($this);
        }

        return $this;
    }

    public function removeStatusHistory(StatusHistory $statusHistory): self
    {
        if ($this->statusHistories->removeElement($statusHistory)) {
            // set the owning side to null (unless already changed)
            if ($statusHistory->getCustomerCard() === $this) {
                $statusHistory->setCustomerCard(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TransferJoan>
     */
    public function getTransferJoans(): Collection
    {
        return $this->transferJoans;
    }

    public function addTransferJoan(TransferJoan $transferJoan): self
    {
        if (!$this->transferJoans->contains($transferJoan)) {
            $this->transferJoans->add($transferJoan);
            $transferJoan->setCustomerCard($this);
        }

        return $this;
    }

    public function removeTransferJoan(TransferJoan $transferJoan): self
    {
        if ($this->transferJoans->removeElement($transferJoan)) {
            // set the owning side to null (unless already changed)
            if ($transferJoan->getCustomerCard() === $this) {
                $transferJoan->setCustomerCard(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setCustomerCard($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getCustomerCard() === $this) {
                $comment->setCustomerCard(null);
            }
        }

        return $this;
    }

    public function getAgency(): ?Agency
    {
        return $this->agency;
    }

    public function setAgency(?Agency $agency): self
    {
        $this->agency = $agency;

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
            $transferArrival->setCustomerCard($this);
        }

        return $this;
    }

    public function removeTransferArrival(TransferArrival $transferArrival): self
    {
        if ($this->transferArrivals->removeElement($transferArrival)) {
            // set the owning side to null (unless already changed)
            if ($transferArrival->getCustomerCard() === $this) {
                $transferArrival->setCustomerCard(null);
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
            $transferInterHotel->setCustomerCard($this);
        }

        return $this;
    }

    public function removeTransferInterHotel(TransferInterHotel $transferInterHotel): self
    {
        if ($this->transferInterHotels->removeElement($transferInterHotel)) {
            // set the owning side to null (unless already changed)
            if ($transferInterHotel->getCustomerCard() === $this) {
                $transferInterHotel->setCustomerCard(null);
            }
        }

        return $this;
    }

/**
     * @return Collection<int, TransferDeparture>
     */
    public function getTransferDeparture(): Collection
    {
        return $this->transferDepartures;
    }

    public function addTransferDeparture(TransferDeparture $transferDeparture): self
    {
        if (!$this->transferDepartures->contains($transferDeparture)) {
            $this->transferDepartures->add($transferDeparture);
            $transferDeparture->setCustomerCard($this);
        }

        return $this;
    }

    public function removeTransferDeparture(TransferDeparture $transferDeparture): self
    {
        if ($this->transferDepartures->removeElement($transferDeparture)) {
            // set the owning side to null (unless already changed)
            if ($transferDeparture->getCustomerCard() === $this) {
                $transferDeparture->setCustomerCard(null);
            }
        }

        return $this;
    }

}
