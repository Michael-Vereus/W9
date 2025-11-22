<?php
class ItemView {
    public function showItems(array $items): void {
        echo "=== Daftar Barang ===\n";
        if (empty($items)) {
            echo "Tidak ada data barang.\n";
            return;
        }

        foreach ($items as $item) {
            echo "{$item->getId()}. {$item->getName()} | Rp {$item->getPrice()} | Stok: {$item->getStock()} | Kategori: {$item->getCategory()}\n";
        }
    }

    public function showSearchResult(array $items, string $keyword = ''): void {
        if ($keyword !== '') {
            echo "=== Hasil Pencarian: '{$keyword}' ===\n";
        } else {
            echo "=== Hasil Pencarian ===\n";
        }

        if (empty($items)) {
            echo "Tidak ada barang yang cocok.\n";
            return;
        }

        foreach ($items as $item) {
            echo "{$item->getId()} - {$item->getName()} (Rp {$item->getPrice()}, Stok {$item->getStock()}, Kategori: {$item->getCategory()})\n";
        }
    }

    public function showMessage(string $message): void {
        echo $message . PHP_EOL;
    }
}
