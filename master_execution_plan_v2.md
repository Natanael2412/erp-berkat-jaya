# Master Execution Plan V2 (Penyempurnaan ERP Berkat Jaya)

Dokumen ini adalah peta jalan (Roadmap) eksekusi fitur tambahan untuk membawa ERP Berkat Jaya ke tahap *Enterprise-Ready*.

---

## FASE 1: Supercharge POS & Kasir (Selesai Hari Ini)
Fokus: Membuat transaksi kasir jauh lebih cepat dan profesional.
1. **Sistem Hotkeys**: Implementasi Alpine.js untuk mendengarkan *keyboard shortcut* (F2 untuk Bayar, ESC untuk Batal, F4 untuk Fokus Cari).
2. **Support Barcode Scanner**: Optimalisasi *input search* agar ketika di-scan oleh alat, barang otomatis masuk ke keranjang.
3. **Cetak Struk Thermal**: Membuat desain *view* (`pos.receipt`) khusus ukuran kertas thermal (58mm/80mm) dengan fitur *auto-print* window.

## FASE 2: Invoice Generator (Nota B2B / Proyek)
Fokus: Terkadang toko listrik menjual partai besar ke Kontraktor/Proyek yang butuh Nota (Invoice) formal berukuran A4.
1. **Invoice View**: Membuat *template* Invoice A4 yang elegan (memuat Logo, Info Perusahaan, Kepada Yth, Tabel Barang, Total, Tanda Tangan).
2. **Print/PDF Export**: Fitur untuk langsung mencetak atau menyimpan bukti Penjualan (Sale) ke dalam format cetak standar perusahaan.

## FASE 3: Dashboard Eksekutif & Visualisasi (Analitik)
Fokus: Memberikan gambaran bisnis sekilas untuk Owner.
1. **Widget Statistik**: Pendapatan Hari Ini, Total Transaksi, Laba Kotor Hari Ini.
2. **Grafik (Chart.js / ApexCharts)**: Tren Penjualan 7 Hari terakhir (Grafik Garis).
3. **Top Produk**: Tabel kecil 5 barang paling laku bulan ini.
4. **Low Stock Alert**: Notifikasi barang-barang yang stoknya kritis (misal < 5).

## FASE 4: Kartu Stok (Master Inventori)
Fokus: Pelacakan *Inventory Control* (Mencegah Kehilangan Barang).
1. **Halaman Stock Card**: Menambahkan tab/halaman di detail produk (`products.show`) yang melacak seluruh riwayat mutasi: 
   *(Contoh: +10 dari PO #12, -2 dari Penjualan #89)*.

## FASE 5: Laporan Lanjutan & Ekspor Keuangan
Fokus: Keperluan pembukuan akhir bulan & pelaporan pajak.
1. **PDF / Excel Export**: Laba Rugi, Neraca, dan Neraca Saldo bisa diunduh ke Excel.
2. **Buku Hutang Supplier (Accounts Payable)**: Laporan ringkasan total PO yang belum lunas per Supplier.

---
*Prosedur Eksekusi: Agen AI akan mengerjakan per-Fase secara berurutan, memberikan update setelah setiap fase selesai diimplementasikan ke dalam source code.*
