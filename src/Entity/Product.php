<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation\Exclusion;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 * @UniqueEntity(fields={"reference"}, message="Ce produit existe déjà.")
 * 
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "app_product_index",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true,
 *      )
 * )
 * 
 * @Hateoas\Relation(
 *      "modify",
 *      href = @Hateoas\Route(
 *          "app_product_update",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 *  exclusion = @Hateoas\Exclusion(groups={"default"})
 * )
 * 
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "app_product_delete",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 * exclusion = @Hateoas\Exclusion(groups={"default"})
 * 
 * )
 * 
 */
class Product
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"product_list","product_detail","default"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Serializer\Groups({"product_list","product_detail","default"})
     * @Assert\NotBlank(message="Ce champs ne peut être vide.")
     * @Assert\Length(min="5", max="15",
     *   minMessage="Ce champ doit contenir un minimum de {{ limit }} caractères.",
     *   maxMessage="Ce champ doit contenir un maximum de {{ limit }} caractères."
     * )
     */
    private $reference;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({"product_list","product_detail"})
     * @Assert\NotBlank(message="Ce champs ne peut être vide.")
     * @Assert\Length(min="5", max="100",
     *   minMessage="Ce champ doit contenir un minimum de {{ limit }} caractères.",
     *   maxMessage="Ce champ doit contenir un maximum de {{ limit }} caractères.")
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="Ce champs ne peut être vide.")
     * @Serializer\Groups({"product_detail"})
     */
    private $description;

    /**
     * @ORM\Column(type="float")
     * @Serializer\Groups({"product_list","product_detail"})
     * @Assert\NotBlank(message="Ce champs ne peut être vide.")
     * @Assert\Range(min="500", max="1500",
     *   minMessage="Ce champ doit être supérieur ou égale à {{ limit }}.",
     *   maxMessage="Ce champ doit être inférieur ou égale à {{ limit }}."
     * )
     */
    private $price;

    /**
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"product_detail"})
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
