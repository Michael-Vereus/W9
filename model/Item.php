<?php
class Item {
    private int $id;
    private string $name;
    private int $price;
    private int $stock;
    private string $category;

    public function __construct(int $id, string $name, int $price, int $stock, string $category) {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->stock = $stock;
        $this->category = $category;
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getPrice(): int { return $this->price; }
    public function getStock(): int { return $this->stock; }
    public function getCategory(): string { return $this->category; }

    public function setName(string $name): void { $this->name = $name; }
    public function setPrice(int $price): void { $this->price = $price; }
    public function setStock(int $stock): void { $this->stock = $stock; }
    public function setCategory(string $category): void { $this->category = $category; }
}
