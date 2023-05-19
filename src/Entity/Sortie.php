<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SortieRepository::class)
 */
class Sortie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=127)
     */
    private $nom;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private $debut;

    /**
     * @ORM\Column(type="dateinterval")
     */
    private $duree;

    /**
     * @ORM\Column(type="date")
     */
    private $fin_inscription;

    /**
     * @ORM\Column(type="integer")
     */
    private $inscriptions_max;

    /**
     * @ORM\ManyToOne(targetEntity=Etat::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $etat;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity=Lieu::class, inversedBy="sorties")
     */
    private $lieu;

    /**
     * @ORM\Column(type="boolean")
     */
    private $archivee;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="organise_sortie")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organisateur;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="inscrit")
     */
    private $participants;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $campus_origine;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDebut(): ?\DateTimeInterface
    {
        return $this->debut;
    }

    public function setDebut(\DateTimeInterface $debut): self
    {
        $this->debut = $debut;

        return $this;
    }

    public function getDuree(): ?\DateInterval
    {
        return $this->duree;
    }

    public function setDuree(\DateInterval $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getFinInscription(): ?\DateTimeInterface
    {
        return $this->fin_inscription;
    }

    public function setFinInscription(\DateTimeInterface $fin_inscription): self
    {
        $this->fin_inscription = $fin_inscription;

        return $this;
    }

    public function getInscriptionsMax(): ?int
    {
        return $this->inscriptions_max;
    }

    public function setInscriptionsMax(int $inscriptions_max): self
    {
        $this->inscriptions_max = $inscriptions_max;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function isArchivee(): ?bool
    {
        return $this->archivee;
    }

    public function setArchivee(bool $archivee): self
    {
        $this->archivee = $archivee;

        return $this;
    }

    public function getOrganisateur(): ?User
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?User $organisateur): self
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
            $participant->addInscrit($this);
        }

        return $this;
    }

    public function removeParticipant(User $participant): self
    {
        if ($this->participants->removeElement($participant)) {
            $participant->removeInscrit($this);
        }

        return $this;
    }

    public function getCampusOrigine(): ?Campus
    {
        return $this->campus_origine;
    }

    public function setCampusOrigine(?Campus $campus_origine): self
    {
        $this->campus_origine = $campus_origine;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function nbInscrits(): int {
        return sizeof($this->participants->toArray());
    }

    public function dateFin(): \DateTimeInterface {
        return date_add($this->debut, $this->fin_inscription);

    }
}
