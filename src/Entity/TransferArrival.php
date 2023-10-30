<?php

namespace App\Entity;

use App\Repository\TransferArrivalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransferArrivalRepository::class)]
class TransferArrival
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(length: 50)]
    private ?string $serviceNumber = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateHour = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $flightNumber = null;

    #[ORM\ManyToOne(inversedBy: 'transferArrivals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CustomerCard $customerCard = null;

    #[ORM\ManyToOne(inversedBy: 'transferArrivals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AirportHotel $fromStart = null;

    #[ORM\ManyToOne(inversedBy: 'transferArrivals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AirportHotel $toArrival = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $hour = null;

    #[ORM\OneToOne(mappedBy: 'transferArrival', cascade: ['persist', 'remove'])]
    private ?TransferVehicleArrival $transferVehicleArrival = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $adultsNumber = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $childrenNumber = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $babiesNumber = null;

    #[ORM\ManyToOne(inversedBy: 'transferArrivals')]
    private ?MeetingPoint $meetingPoint = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $meetingAt = null;

    #[ORM\ManyToOne(inversedBy: 'transferArrivals')]
    private ?User $staff = null;

    #[ORM\ManyToOne(inversedBy: 'transferArrivals')]
    private ?Status $status = null;

    #[ORM\ManyToOne(inversedBy: 'transferArrivals')]
    private ?User $statusUpdatedBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $statusUpdatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getServiceNumber(): ?string
    {
        return $this->serviceNumber;
    }

    public function setServiceNumber(string $serviceNumber): self
    {
        $this->serviceNumber = $serviceNumber;

        return $this;
    }

    public function getDateHour(): ?\DateTimeImmutable
    {
        return $this->dateHour;
    }

    public function setDateHour(\DateTimeImmutable $dateHour): self
    {
        $this->dateHour = $dateHour;

        return $this;
    }

    public function getFlightNumber(): ?string
    {
        return $this->flightNumber;
    }

    public function setFlightNumber(?string $flightNumber): self
    {
        $this->flightNumber = $flightNumber;

        return $this;
    }

    public function getCustomerCard(): ?CustomerCard
    {
        return $this->customerCard;
    }

    public function setCustomerCard(?CustomerCard $customerCard): self
    {
        $this->customerCard = $customerCard;

        return $this;
    }

    public function getFromStart(): ?AirportHotel
    {
        return $this->fromStart;
    }

    public function setFromStart(?AirportHotel $fromStart): self
    {
        $this->fromStart = $fromStart;

        return $this;
    }

    public function getToArrival(): ?AirportHotel
    {
        return $this->toArrival;
    }

    public function setToArrival(?AirportHotel $toArrival): self
    {
        $this->toArrival = $toArrival;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getHour(): ?\DateTimeImmutable
    {
        return $this->hour;
    }

    public function setHour(\DateTimeImmutable $hour): self
    {
        $this->hour = $hour;

        return $this;
    }

    public function getTransferVehicleArrival(): ?TransferVehicleArrival
    {
        return $this->transferVehicleArrival;
    }

    public function setTransferVehicleArrival(?TransferVehicleArrival $transferVehicleArrival): static
    {
        // unset the owning side of the relation if necessary
        if ($transferVehicleArrival === null && $this->transferVehicleArrival !== null) {
            $this->transferVehicleArrival->setTransferArrival(null);
        }

        // set the owning side of the relation if necessary
        if ($transferVehicleArrival !== null && $transferVehicleArrival->getTransferArrival() !== $this) {
            $transferVehicleArrival->setTransferArrival($this);
        }

        $this->transferVehicleArrival = $transferVehicleArrival;

        return $this;
    }

    public function getAdultsNumber(): ?int
    {
        return $this->adultsNumber;
    }

    public function setAdultsNumber(?int $adultsNumber): static
    {
        $this->adultsNumber = $adultsNumber;

        return $this;
    }

    public function getChildrenNumber(): ?int
    {
        return $this->childrenNumber;
    }

    public function setChildrenNumber(?int $childrenNumber): static
    {
        $this->childrenNumber = $childrenNumber;

        return $this;
    }

    public function getBabiesNumber(): ?int
    {
        return $this->babiesNumber;
    }

    public function setBabiesNumber(?int $babiesNumber): static
    {
        $this->babiesNumber = $babiesNumber;

        return $this;
    }

    public function getMeetingPoint(): ?MeetingPoint
    {
        return $this->meetingPoint;
    }

    public function setMeetingPoint(?MeetingPoint $meetingPoint): static
    {
        $this->meetingPoint = $meetingPoint;

        return $this;
    }

    public function getMeetingAt(): ?\DateTimeImmutable
    {
        return $this->meetingAt;
    }

    public function setMeetingAt(?\DateTimeImmutable $meetingAt): static
    {
        $this->meetingAt = $meetingAt;

        return $this;
    }
    public function getMeetingAtDate() {
        return $this->meetingAt->format('d-m-Y');
    }

    public function getMeetingAtTime() {
        return $this->meetingAt->format('H:i');
    }

    public function getStaff(): ?User
    {
        return $this->staff;
    }

    public function setStaff(?User $staff): static
    {
        $this->staff = $staff;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusUpdatedBy(): ?User
    {
        return $this->statusUpdatedBy;
    }

    public function setStatusUpdatedBy(?User $statusUpdatedBy): static
    {
        $this->statusUpdatedBy = $statusUpdatedBy;

        return $this;
    }

    public function getStatusUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->statusUpdatedAt;
    }

    public function setStatusUpdatedAt(?\DateTimeImmutable $statusUpdatedAt): static
    {
        $this->statusUpdatedAt = $statusUpdatedAt;

        return $this;
    }


}
