<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
#[ApiResource(
    paginationItemsPerPage: 15,
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ]
)]
#[ApiFilter(BooleanFilter::class, properties: ['online'])]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'name' => 'partial'])]
#[ORM\HasLifecycleCallbacks]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre du film est obligatoire")]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractère",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 5000,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: "La durée doit être un nombre positif")]
    #[Assert\Range(
        min: 1,
        max: 600,
        notInRangeMessage: "La durée doit être entre {{ min }} et {{ max }} minutes"
    )]
    private ?int $duration = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $releaseDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $online = false;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "Le nombre d'entrées doit être positif ou zéro")]
    #[Assert\Range(
        min: 0,
        max: 1000000000,
        notInRangeMessage: "Le nombre d'entrées doit être entre {{ min }} et {{ max }}"
    )]
    private ?int $nbEntries = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url(message: "L'URL n'est pas valide")]
    #[Assert\Length(
        max: 500,
        maxMessage: "L'URL ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $url = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Assert\PositiveOrZero(message: "Le budget doit être positif ou zéro")]
    #[Assert\Range(
        min: 0,
        max: 1000000000,
        notInRangeMessage: "Le budget doit être entre {{ min }} et {{ max }}"
    )]
    private ?float $budget = null;

    #[ORM\ManyToOne(inversedBy: 'movies')]
    #[Assert\NotNull(message: "Le réalisateur est obligatoire", groups: ['create'])]
    private ?Director $director = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, mappedBy: 'movies')]
    private Collection $categories;

    /**
     * @var Collection<int, Actor>
     */
    #[ORM\ManyToMany(targetEntity: Actor::class, mappedBy: 'movies')]
    private Collection $actors;

    /**
     * @var Collection<int, MediaObject>
     */
    #[ORM\OneToMany(targetEntity: MediaObject::class, mappedBy: 'movie', cascade: ['persist', 'remove'])]
    private Collection $mediaObjects;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->actors = new ArrayCollection();
        $this->mediaObjects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getReleaseDate(): ?\DateTime
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(?\DateTime $releaseDate): static
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }


    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new \DateTimeImmutable();

    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): static
    {
        $this->online = $online;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->addMovie($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            $category->removeMovie($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Actor>
     */
    public function getActors(): Collection
    {
        return $this->actors;
    }

    public function addActor(Actor $actor): static
    {
        if (!$this->actors->contains($actor)) {
            $this->actors->add($actor);
            $actor->addMovie($this);
        }

        return $this;
    }

    public function removeActor(Actor $actor): static
    {
        if ($this->actors->removeElement($actor)) {
            $actor->removeMovie($this);
        }

        return $this;
    }

    public function getNbEntries(): ?int
    {
        return $this->nbEntries;
    }

    public function setNbEntries(?int $nbEntries): static
    {
        $this->nbEntries = $nbEntries;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getBudget(): ?float
    {
        return $this->budget;
    }

    public function setBudget(?float $budget): static
    {
        $this->budget = $budget;

        return $this;
    }

    public function getDirector(): ?Director
    {
        return $this->director;
    }

    public function setDirector(?Director $director): static
    {
        $this->director = $director;

        return $this;
    }

    /**
     * @return Collection<int, MediaObject>
     */
    public function getMediaObjects(): Collection
    {
        return $this->mediaObjects;
    }

    public function addMediaObject(MediaObject $mediaObject): static
    {
        if (!$this->mediaObjects->contains($mediaObject)) {
            $this->mediaObjects->add($mediaObject);
            $mediaObject->setMovie($this);
        }

        return $this;
    }

    public function removeMediaObject(MediaObject $mediaObject): static
    {
        if ($this->mediaObjects->removeElement($mediaObject)) {
            if ($mediaObject->getMovie() === $this) {
                $mediaObject->setMovie(null);
            }
        }

        return $this;
    }
}
