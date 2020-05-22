<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"index", "show"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"index", "show"})
     * @Assert\NotBlank(message="Ce champs ne peut être vide.")
     * @Assert\Length(min="5", max="15",
     *   minMessage="Ce champ doit contenir un minimum de {{ limit }} caractères.",
     *   maxMessage="Ce champ doit contenir un maximum de {{ limit }} caractères."
     * )
     */
    private $reference;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"index", "show"})
     * @Assert\NotBlank(message="Ce champs ne peut être vide.")
     * @Assert\Length(min="5", max="100",
     *   minMessage="Ce champ doit contenir un minimum de {{ limit }} caractères.",
     *   maxMessage="Ce champ doit contenir un maximum de {{ limit }} caractères.")
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Groups({"show"})
     * @Assert\NotBlank(message="Ce champs ne peut être vide.")
     */
    private $description;

    /**
     * @ORM\Column(type="float")
     * @Groups({"index", "show"})
     * @Assert\NotBlank(message="Ce champs ne peut être vide.")
     * @Assert\Range(min="500", max="1500",
     *   minMessage="Ce champ doit être supérieur ou égale à {{ limit }}.",
     *   maxMessage="Ce champ doit être inférieur ou égale à {{ limit }}."
     * )
     */
    private $price;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"show"})
     * @Assert\NotBlank(message="Ce champs ne peut être vide.")
     * @Assert\Range(min="5", max="1000",
     *  minMessage="Ce champ doit être supérieur ou égale à {{ limit }}.",
     *  maxMessage="Ce champ doit être inférieur ou égale à {{ limit }}."
     * )
     */
    private $quantity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }
}
