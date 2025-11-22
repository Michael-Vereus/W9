<?php
require_once __DIR__ . '/Item.php';

class ItemRepository {
    private array $items = [];
    private int $lastId = 0;
    private string $file;

    public function __construct() {
        // data.json berada diluar folder, keluar folder model terlebih dahulu
        $this->file = __DIR__ . '/../data.json';
        $this->load();
    }

    // load data dri json, cek json apakah ada atau tdk
    private function load(): void {
        if (!file_exists($this->file)) {
            // buat file kosongan kalau g ada
            @file_put_contents($this->file, json_encode([], JSON_PRETTY_PRINT)); //json pretty print biar lebih rapi 
            return;
        }

        $json = @file_get_contents($this->file);
        if ($json === false || trim($json) === '') return;

        $data = json_decode($json, true);
        if (!is_array($data)) return;

        foreach ($data as $row) {
            if (!isset($row['id'], $row['name'], $row['price'], $row['stock'], $row['category'])) continue;
            $id = (int)$row['id'];
            $item = new Item($id, (string)$row['name'], (int)$row['price'], (int)$row['stock'], (string)$row['category']);
            $this->items[$id] = $item;
            if ($id > $this->lastId) $this->lastId = $id;
        }
    }

    // simpan data ke json 
    private function save(): void {
        $data = [];
        foreach ($this->items as $item) {
            $data[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'price' => $item->getPrice(),
                'stock' => $item->getStock(),
                'category' => $item->getCategory(),
            ];
        }
        @file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT));
    }

    /*
        Tambah barang, tolak kalo harga dan stok dibawah atau nol
    */
    public function add(string $name, int $price, int $stock, string $category) {
        if ($price < 0 || $stock < 0) return false;

        $this->lastId++;
        $item = new Item($this->lastId, $name, $price, $stock, $category);
        $this->items[$this->lastId] = $item;
        $this->save();
        return $item;
    }

    // return semua barang 
    public function getAll(): array {
        return array_values($this->items);
    }

    public function getById(int $id): ?Item {
        return $this->items[$id] ?? null;
    }

    // update barang, kalo dibawah atau nol tolak di line 78 bag if price < 0 || stock < 0 
    public function update(int $id, string $name, int $price, int $stock, string $category): bool {
        if (!isset($this->items[$id])) return false;
        if ($price < 0 || $stock < 0) return false;

        $item = $this->items[$id];
        $item->setName($name);
        $item->setPrice($price);
        $item->setStock($stock);
        $item->setCategory($category);
        $this->save();
        return true;
    }

    public function delete(int $id): bool {
        if (!isset($this->items[$id])) return false;
        unset($this->items[$id]);
        $this->save();
        return true;
    }

    /* search by name, ubah ke lowcase supaya sama  */
    public function search(string $keyword): array {
        $keyword = strtolower($keyword);
        $result = [];
        foreach ($this->items as $item) {
            if (strpos(strtolower($item->getName()), $keyword) !== false) {
                $result[] = $item;
            }
        }
        return array_values($result);
    }

    /* cari item berdasarkan category, tidak beda jauh dengan function search tinggal ganti param nya */
    public function getByCategory(string $category): array {
        $category = strtolower($category);
        $result = [];
        foreach ($this->items as $item) {
            if (strtolower($item->getCategory()) === $category) {
                $result[] = $item;
            }
        }
        return array_values($result);
    }
}
