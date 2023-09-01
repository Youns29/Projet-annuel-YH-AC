<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true, nullable:true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $address = null;

    #[ORM\Column(nullable:true)]
    private ?int $stockageSpace = null;

    #[ORM\Column(nullable: true)]
    private ?float $useSpace = null;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $inscriptionDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable:true)]
    private ?\DateTimeInterface $lastConnection = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable:true)]
    private ?\DateTimeInterface $lastAccountUpdate = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Files::class, orphanRemoval: true)]
    private Collection $files;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Invoice::class, orphanRemoval: true)]
    private Collection $invoices;

    #[ORM\OneToMany(mappedBy: 'invoiceAddress', targetEntity: Invoice::class, orphanRemoval: true)]
    private Collection $invoicesAddress;

    #[ORM\Column(length: 150,  nullable:true)]
    private ?string $nomtitulaire = null;

    #[ORM\Column(length: 20,  nullable:true)]
    private ?string $cartnumber = null;

    #[ORM\Column(length: 7,  nullable:true)]
    private ?string $expirationdate = null;

    #[ORM\Column(length: 3,  nullable:true)]
    private ?string $numbercvc = null;

    public function __construct()
    {
        $this->files = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->invoicesAddress = new ArrayCollection();
        $this->inscriptionDate = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
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

    public function setRoles(array $roles): static
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

    /**
     * @see UserInterface
     */

    public function getSalt(): ?string
     {
         return null;
     }
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstname;
    }

    public function setFirstName(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getStockageSpace(): ?int
    {
        return $this->stockageSpace;
    }

    public function setStockageSpace(int $stockageSpace): static
    {
        $this->stockageSpace = $stockageSpace;

        return $this;
    }

    public function getUseSpace(): ?float
    {
        return $this->useSpace;
    }

    public function setUseSpace(?float $useSpace): self
    {
        $this->useSpace = $useSpace;

        return $this;
    }

    public function getInscriptionDate(): ?\DateTimeInterface
    {
        return $this->inscriptionDate;
    }

    public function setInscriptionDate(\DateTimeInterface $inscriptionDate): self
    {
        $this->inscriptionDate = $inscriptionDate;

        return $this;
    }

    public function getLastConnection(): ?\DateTimeInterface
    {
        return $this->lastConnection;
    }

    public function setLastConnection(\DateTimeInterface $lastConnection): static
    {
        $this->lastConnection = $lastConnection;

        return $this;
    }

    public function getLastAccountUpdate(): ?\DateTimeInterface
    {
        return $this->lastAccountUpdate;
    }

    public function setLastAccountUpdate(\DateTimeInterface $lastAccountUpdate): static
    {
        $this->lastAccountUpdate = $lastAccountUpdate;

        return $this;
    }

    /**
     * @return Collection<int, Files>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(Files $file): static
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setUser($this);
        }

        return $this;
    }

    public function removeFile(Files $file): static
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getUser() === $this) {
                $file->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): static
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices->add($invoice);
            $invoice->setUser($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): static
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getUser() === $this) {
                $invoice->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function getInvoicesAddress(): Collection
    {
        return $this->invoicesAddress;
    }

    public function addInvoicesAddress(Invoice $invoicesAddress): static
    {
        if (!$this->invoicesAddress->contains($invoicesAddress)) {
            $this->invoicesAddress->add($invoicesAddress);
            $invoicesAddress->setInvoiceAddress($this);
        }

        return $this;
    }

    public function removeInvoicesAddress(Invoice $invoicesAddress): static
    {
        if ($this->invoicesAddress->removeElement($invoicesAddress)) {
            // set the owning side to null (unless already changed)
            if ($invoicesAddress->getInvoiceAddress() === $this) {
                $invoicesAddress->setInvoiceAddress(null);
            }
        }

        return $this;
    }

    public function getNomtitulaire(): ?string
    {
        return $this->nomtitulaire;
    }

    public function setNomtitulaire(string $nomtitulaire): static
    {
        $this->nomtitulaire = $nomtitulaire;

        return $this;
    }

    public function getCartnumber(): ?string
    {
        return $this->cartnumber;
    }

    public function setCartnumber(string $cartnumber): static
    {
        $this->cartnumber = $cartnumber;

        return $this;
    }

    public function getExpirationdate(): ?string
    {
        return $this->expirationdate;
    }

    public function setExpirationdate(string $expirationdate): static
    {
        $this->expirationdate = $expirationdate;

        return $this;
    }

    public function getNumbercvc(): ?string
    {
        return $this->numbercvc;
    }

    public function setNumbercvc(string $numbercvc): static
    {
        $this->numbercvc = $numbercvc;

        return $this;
    }
}
