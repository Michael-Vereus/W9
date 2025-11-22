<?php
class MenuView {
    public function mainMenu(): int {
        echo "\n=== Sistem Inventaris Barang ===\n";
        echo "1. Tambah Barang\n";
        echo "2. Tampilkan Semua Barang\n";
        echo "3. Update Barang\n";
        echo "4. Hapus Barang\n";
        echo "5. Cari Barang\n";
        echo "6. Tampilkan Barang per Kategori\n";
        echo "7. Laporan Inventaris (Total / Sorting / Low Stock)\n";
        echo "8. Keluar\n";
        echo "Pilih menu: ";
        $input = trim(fgets(STDIN));
        return intval($input);
    }
}
