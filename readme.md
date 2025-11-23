# CLI STORAGE MANAGEMENT SYSTEM

## Made by : Michael Vereus Limantara

Folder Structure : 
```txt
W9/
│── data.json
│── index.php
│── README.md
│
├── controller/
│   └── ItemController.php
│
├── model/
│   ├── Item.php
│   └── ItemRepository.php
│
└── view/
    ├── ItemView.php
    └── MenuView.php
```

Fitur Program 
| No | Fitur                                                |
| -- | ---------------------------------------------------- |
| 1  | Tambah barang (dengan validasi tidak negatif)        |
| 2  | Tampilkan semua barang                               |
| 3  | Update barang                                        |
| 4  | Hapus barang                                         |
| 5  | Cari barang                                          |
| 6  | Filter barang berdasarkan kategori                   |
| 7  | Laporan inventaris (total nilai, sorting, low stock) |

## Implemntasi pada code 
### Tugas A
- Validasi Harga dan Stok ( cek tidak boleh nol atau minus )
```php
public function add(string $name, int $price, int $stock, string $category) {
    if ($price < 0 || $stock < 0) return false;

    $this->lastId++;
    $item = new Item($this->lastId, $name, $price, $stock, $category);
    $this->items[$this->lastId] = $item;
    $this->save();
    return $item;
}
```
``` php
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
```
- Tambahkan Kolom category untuk barang
Kolom category tinggal menambahkan attribut category pada class item.php
```php
Class Item {
    ....
    private string $category;

    public function __construct(int $id, string $name, int $price, int $stock, string $category) {
        .....
        $this->category = $category;
    }

    .....
    public function getCategory(): string { return $this->category; }

    .....
    public function setCategory(string $category): void { $this->category = $category; }
}
```
> **Serta sisanya mengikuti di param dan tinggal menambahkan input user untuk category**

- Tambahkan menu Tampilkan berdasarkan category 
implementasi kode nya ada di bagian itemcontroller dan item repository seperti berikut : 
``` php
private function showItemsByCategory(): void { // di itemcontroller
    echo "Masukkan kategori: ";
    $category = trim(fgets(STDIN));

    $items = $this->repo->getByCategory($category);
    $this->view->showItems($items);
}
```
``` php 
public function getByCategory(string $category): array { // di itemrepository
    $category = strtolower($category);
    $result = [];
    foreach ($this->items as $item) {
        if (strtolower($item->getCategory()) === $category) {
            $result[] = $item;
        }
    }
    return array_values($result);
}
```
### Tugas B 
- Menyimpan data ke JSON file 
Untuk ini sudah aku siapkan 2 function yaitu load save yang mirip dengan fungsi get dan set ( kedua fungsi tersebut berada di itemRepository karena itemRepository = function logic untuk CRUD data sedangkan itemController = controller interaksi dari user )
``` php 
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
```
``` php
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
```
- Memuat data saat aplikasi mulai
Saat aplikasi / software cli ini mulai sistem menjalankan index, di index kemudian memanggil ini : 
``` php 
$controller = new ItemController($repo, $view, $menu);

$controller->run();
``` 
Yang kemudian didalam ItemController ada 
``` php 
public function __construct(ItemRepository $repo, ItemView $view, MenuView $menu) {
    $this->repo = $repo;
    $this->view = $view;
    $this->menu = $menu;
}
```
Nah ketika repo di class / object repo di construct di dalam itemRepository terjalankan kode ini :
``` php 
public function __construct() {
    // data.json berada diluar folder, keluar folder model terlebih dahulu
    $this->file = __DIR__ . '/../data.json';
    $this->load();
}
```
Yang berarti ketika class itemRepository dibuat maka otomatis fungsi construct ini akan dijalankan yang kemudian memanggil fungsi load untuk mengambil data dari file json. Jadi ketika sistem berjalan, secara otomatis data akan terambil dari json lewat function load di itemRepository yang dijalankan ketika class itemRepository dipanggil saat itemController mulai.

- Menyimpan ulang 
Seperti yang bisa dilihat di function update dia memanggil fungsi save yang akan menyimpan perubahan yang terjadi
``` php
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
```
### Tugas C 
- Tampilkan nilai total inventaris
- Sort by berdasarkan input user
- Fitur low stock alert > 5

Silahkan bisa dilihat sebagai berikut dibawah ; 
``` php 
private function inventoryReport(): void {
    echo "\n=== Laporan Inventaris ===\n";

    $items = $this->repo->getAll();
    if (empty($items)) {
        echo "Tidak ada data barang.\n";
        return;
    }

    // total nilai inventaris
    $total = 0;
    foreach ($items as $item) {
        $total += $item->getPrice() * $item->getStock();
    }
    echo "Total Nilai Inventaris: Rp $total\n";

    // low stock Alert
    echo "\n=== Low Stock (Stok < 5) ===\n";
    $low = array_filter($items, function($i) { return $i->getStock() < 5; });
    if (empty($low)) {
        echo "Tidak ada barang dengan stok < 5.\n";
    } else {
        foreach ($low as $item) {
            echo "- {$item->getId()}. {$item->getName()} (Stok: {$item->getStock()}, Kategori: {$item->getCategory()})\n";
        }
    }

    // sorting algorithm
    echo "\nUrutkan daftar berdasarkan: (1) Harga  (2) Stok  (lain = batal): ";
    $opt = intval(trim(fgets(STDIN)));

    if ($opt === 1) {
        usort($items, function($a, $b) { return $a->getPrice() <=> $b->getPrice(); });
        echo "\n=== Daftar Barang (Terurut Harga) ===\n";
    } elseif ($opt === 2) {
        usort($items, function($a, $b) { return $a->getStock() <=> $b->getStock(); });
        echo "\n=== Daftar Barang (Terurut Stok) ===\n";
    } else {
        echo "Operasi sorting dibatalkan.\n";
        return;
    }

    foreach ($items as $item) {
        echo "{$item->getId()}. {$item->getName()} | Rp {$item->getPrice()} | Stok: {$item->getStock()} | Kategori: {$item->getCategory()}\n";
    }
}
```

