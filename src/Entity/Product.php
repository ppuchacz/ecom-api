<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    public string $name;

    #[ORM\Column]
    public ?string $description;

    #[ORM\Column]
    public float $price;

    #[ORM\Column]
    public ?string $brand;

    #[ORM\Column]
    public int $max_quantity;

    #[ORM\Column]
    public ?string $picture;

    public function getId(): ?int
    {
        return $this->id;
    }
}