<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity('username')]
#[ApiResource]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
/* #[ORM\EntityListeners(['App\EntityListener\UserListener'])] */
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('User')]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank()]
    #[Assert\Length(min:2, max:180)]
    private ?string $username = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $usageName = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank()]
    private ?string $password = null;

    private ?string $plainPassword = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastConnection = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Area $area = null;

    #[ORM\OneToMany(mappedBy: 'createdBy', targetEntity: Comment::class)]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'updatedBy', targetEntity: StatusHistory::class)]
    private Collection $statusHistories;

    #[ORM\OneToMany(mappedBy: 'statusUpdatedBy', targetEntity: TransferArrival::class)]
    private Collection $statusUpdatedBy;

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $language = null;

    #[ORM\Column(nullable: true)]
    private ?bool $deactivate = null;

    #[ORM\OneToMany(mappedBy: 'staff', targetEntity: TransferArrival::class)]
    private Collection $transferArrivals;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: WhatsAppMessage::class)]
    private Collection $whatsAppMessages;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?AirportHotel $airport = null;

    #[ORM\OneToMany(mappedBy: 'updatedBy', targetEntity: CheckedHistory::class)]
    private Collection $checkedHistories;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->statusHistories = new ArrayCollection();
        $this->transferArrivals = new ArrayCollection();
        $this->whatsAppMessages = new ArrayCollection();
        $this->checkedHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }


    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getLastConnection(): ?\DateTimeInterface
    {
        return $this->lastConnection;
    }

    public function setLastConnection(?\DateTimeInterface $lastConnection): self
    {
        $this->lastConnection = $lastConnection;

        return $this;
    }

    public function getArea(): ?Area
    {
        return $this->area;
    }

    public function setArea(?Area $area): self
    {
        $this->area = $area;

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
            $comment->setCreatedBy($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getCreatedBy() === $this) {
                $comment->setCreatedBy(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getUserIdentifier();
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
            $statusHistory->setUpdatedBy($this);
        }

        return $this;
    }

    public function removeStatusHistory(StatusHistory $statusHistory): self
    {
        if ($this->statusHistories->removeElement($statusHistory)) {
            // set the owning side to null (unless already changed)
            if ($statusHistory->getUpdatedBy() === $this) {
                $statusHistory->setUpdatedBy(null);
            }
        }

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

    public function isDeactivate(): ?bool
    {
        return $this->deactivate;
    }

    public function setDeactivate(?bool $deactivate): self
    {
        $this->deactivate = $deactivate;

        return $this;
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
            $transferArrival->setStaff($this);
        }

        return $this;
    }

    public function removeTransferArrival(TransferArrival $transferArrival): static
    {
        if ($this->transferArrivals->removeElement($transferArrival)) {
            // set the owning side to null (unless already changed)
            if ($transferArrival->getStaff() === $this) {
                $transferArrival->setStaff(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WhatsAppMessage>
     */
    public function getWhatsAppMessages(): Collection
    {
        return $this->whatsAppMessages;
    }

    public function addWhatsAppMessage(WhatsAppMessage $whatsAppMessage): static
    {
        if (!$this->whatsAppMessages->contains($whatsAppMessage)) {
            $this->whatsAppMessages->add($whatsAppMessage);
            $whatsAppMessage->setUser($this);
        }

        return $this;
    }

    public function removeWhatsAppMessage(WhatsAppMessage $whatsAppMessage): static
    {
        if ($this->whatsAppMessages->removeElement($whatsAppMessage)) {
            // set the owning side to null (unless already changed)
            if ($whatsAppMessage->getUser() === $this) {
                $whatsAppMessage->setUser(null);
            }
        }

        return $this;
    }

    public function getAirport(): ?AirportHotel
    {
        return $this->airport;
    }

    public function setAirport(?AirportHotel $airport): static
    {
        $this->airport = $airport;

        return $this;
    }

    /**
     * @return Collection<int, CheckedHistory>
     */
    public function getCheckedHistories(): Collection
    {
        return $this->checkedHistories;
    }

    public function addCheckedHistory(CheckedHistory $checkedHistory): static
    {
        if (!$this->checkedHistories->contains($checkedHistory)) {
            $this->checkedHistories->add($checkedHistory);
            $checkedHistory->setUpdatedBy($this);
        }

        return $this;
    }

    public function removeCheckedHistory(CheckedHistory $checkedHistory): static
    {
        if ($this->checkedHistories->removeElement($checkedHistory)) {
            // set the owning side to null (unless already changed)
            if ($checkedHistory->getUpdatedBy() === $this) {
                $checkedHistory->setUpdatedBy(null);
            }
        }

        return $this;
    }

    public function getUsageName(): ?string
    {
        return $this->usageName;
    }

    public function setUsageName(?string $usageName): static
    {
        $this->usageName = $usageName;

        return $this;
    }

}
