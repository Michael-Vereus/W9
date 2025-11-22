<?php
require_once __DIR__ . '/../model/ItemRepository.php';
require_once __DIR__ . '/../view/ItemView.php';
require_once __DIR__ . '/../view/MenuView.php';

class ItemController {
    private ItemRepository $repo;
    private ItemView $view;
    private MenuView $menu;

    public function __construct(ItemRepository $repo, ItemView $view, MenuView $menu) {
        $this->repo = $repo;
        $this->view = $view;
        $this->menu = $menu;
    }

    public function run(): void {
        while (true) {
            $choice = $this->menu->mainMenu();

            switch ($choice) {
                case 1:
                    $this->addItem();
                    break;
                case 2:
                    $this->view->showItems($this->repo->getAll());
                    break;
                case 3:
                    $this->updateItem();
                    break;
                case 4:
                    $this->deleteItem();
                    break;
                case 5:
                    $this->searchItem();
                    break;
                case 6:
                    $this->showItemsByCategory();
                    break;
                case 7:
                    $this->inventoryReport();
                    break;
                case 8:
                    exit("Keluar...\n");
                default:
                    echo "Pilihan tidak valid!\n";
            }
        }
    }

    private function addItem(): void {
        echo "Nama Barang: ";
        $name = trim(fgets(STDIN));

        echo "Harga: ";
        $price = intval(trim(fgets(STDIN)));

        echo "Stok: ";
        $stock = intval(trim(fgets(STDIN)));

        echo "Kategori: ";
        $category = trim(fgets(STDIN));

        $result = $this->repo->add($name, $price, $stock, $category);

        if ($result === false) {
            $this->view->showMessage("Gagal menambahkan. Harga dan stok tidak boleh negatif.");
            return;
        }

        $this->view->showMessage("Barang berhasil ditambahkan (ID: {$result->getId()}).");
    }

    private function updateItem(): void {
        echo "Masukkan ID barang yang akan diupdate: ";
        $id = intval(trim(fgets(STDIN)));

        $item = $this->repo->getById($id);
        if (!$item) {
            $this->view->showMessage("Barang tidak ditemukan.");
            return;
        }

        echo "Nama Baru ({$item->getName()}): ";
        $name = trim(fgets(STDIN));
        if ($name === '') $name = $item->getName();

        echo "Harga Baru ({$item->getPrice()}): ";
        $priceInput = trim(fgets(STDIN));
        $price = $priceInput === '' ? $item->getPrice() : intval($priceInput);

        echo "Stok Baru ({$item->getStock()}): ";
        $stockInput = trim(fgets(STDIN));
        $stock = $stockInput === '' ? $item->getStock() : intval($stockInput);

        echo "Kategori Baru ({$item->getCategory()}): ";
        $catInput = trim(fgets(STDIN));
        $category = $catInput === '' ? $item->getCategory() : $catInput;

        $ok = $this->repo->update($id, $name, $price, $stock, $category);
        if (!$ok) {
            $this->view->showMessage("Update gagal. Harga dan stok tidak boleh negatif atau item tidak ditemukan.");
            return;
        }

        $this->view->showMessage("Barang berhasil diperbarui.");
    }

    private function deleteItem(): void {
        echo "Masukkan ID barang yang akan dihapus: ";
        $id = intval(trim(fgets(STDIN)));

        if ($this->repo->delete($id)) {
            $this->view->showMessage("Barang berhasil dihapus.");
        } else {
            $this->view->showMessage("Barang tidak ditemukan.");
        }
    }

    private function searchItem(): void {
        echo "Masukkan keyword pencarian: ";
        $keyword = trim(fgets(STDIN));

        $result = $this->repo->search($keyword);
        $this->view->showSearchResult($result, $keyword);
    }

    private function showItemsByCategory(): void {
        echo "Masukkan kategori: ";
        $category = trim(fgets(STDIN));

        $items = $this->repo->getByCategory($category);
        $this->view->showItems($items);
    }

    /* Fitur tambahan
        Laporan inventaris:
        - total nilai inventaris (sum price * stock)
        - low stock alert (stok < 5)
        - sorting berdasarkan harga atau stok (user pilih)
     */
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
}
