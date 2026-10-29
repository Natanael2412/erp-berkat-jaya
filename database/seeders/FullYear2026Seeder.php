<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\CashService;
use App\Services\ProductService;
use App\Services\PurchaseService;
use App\Services\SalesService;
use App\Support\Accounts;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

/**
 * Seeder data dummy lengkap 1 tahun penuh (Januari 2026 - 29 Oktober 2026).
 * Mensimulasikan siklus bisnis end-to-end melalui service asli Laravel:
 *  - Setoran modal awal Kas & Bank
 *  - Master produk elektrik multi-satuan & saldo awal persediaan
 *  - Pembelian (PO), Penerimaan barang (GR), Retur pembelian (RB), & Pembayaran utang supplier
 *  - Penjualan Kasir POS reguler harian/mingguan (Cash, Transfer, QRIS) & Void penjualan
 *  - Beban operasional bulanan (Listrik, Air, Internet, Gaji Karyawan)
 *  - Prive pemilik berkala
 */
class FullYear2026Seeder extends Seeder
{
    public function run(
        ProductService $products,
        PurchaseService $purchases,
        SalesService $sales,
        CashService $cash
    ): void {
        $owner = User::where('role', 'owner')->firstOrFail();
        $cashier = User::where('role', 'cashier')->firstOrFail();
        $admin = User::where('role', 'admin')->firstOrFail();

        $kas = Account::where('code', Accounts::KAS)->value('id');
        $bank = Account::where('code', Accounts::BANK)->value('id');

        // Master Satuan Tambahan jika belum ada
        foreach ([
            ['BATANG', 'Batang'],
            ['IKAT', 'Ikat'],
            ['SLOP', 'Slop'],
            ['BKS', 'Bungkus'],
        ] as [$code, $name]) {
            Unit::firstOrCreate(['code' => $code], ['name' => $name]);
        }
        $u = Unit::pluck('id', 'code');

        // Master Supplier Tambahan
        $supSinar = Supplier::where('code', 'SUP-001')->firstOrFail();
        $supKabel = Supplier::where('code', 'SUP-002')->firstOrFail();
        $supPhilips = Supplier::firstOrCreate(['code' => 'SUP-003'], [
            'name' => 'PT Philips Lighting Indonesia',
            'phone' => '021-8899003',
            'address' => 'Kawasan Industri Pulogadung, Jakarta Timur',
        ]);
        $supBroco = Supplier::firstOrCreate(['code' => 'SUP-004'], [
            'name' => 'PT Broco & Schneider Distributor',
            'phone' => '021-6623344',
            'address' => 'Mangga Dua, Jakarta Barat',
        ]);
        $supTeknik = Supplier::firstOrCreate(['code' => 'SUP-005'], [
            'name' => 'Glodok Perkakas & Alat Listrik',
            'phone' => '021-6288776',
            'address' => 'Hayam Wuruk, Glodok, Jakarta Barat',
        ]);

        // =========================================================================
        // 1. MODAL AWAL TAHUN (2 Januari 2026)
        // =========================================================================
        Carbon::setTestNow(Carbon::parse('2026-01-02 08:00:00'));
        Auth::login($owner);

        $cash->record([
            'type' => 'capital',
            'cash_account_id' => $kas,
            'amount' => 50_000_000,
            'description' => 'Setoran modal awal kas fisik toko Berkat Jaya',
            'transaction_date' => '2026-01-02',
        ]);

        $cash->record([
            'type' => 'capital',
            'cash_account_id' => $bank,
            'amount' => 350_000_000,
            'description' => 'Setoran modal awal rekening operasional toko (Bank)',
            'transaction_date' => '2026-01-02',
        ]);

        // =========================================================================
        // 2. MASTER PRODUK & SALDO AWAL PERSEDIAAN (2 Januari 2026)
        // =========================================================================
        $p = [];

        // --- KABEL LISTRIK ---
        $p['kbl_nym_2x15'] = $products->create([
            'sku' => 'KBL-NYM-2X15', 'name' => 'Kabel NYM 2x1.5mm Eterna (SNI)',
            'base_unit_id' => $u['M'], 'min_stock' => 100, 'base_selling_price' => 7500,
            'units' => [['unit_id' => $u['ROLL'], 'conversion_factor' => 50, 'selling_price' => 350000]],
            'opening_qty' => 1000, 'opening_cost' => 5500, 'opening_date' => '2026-01-02',
        ]);

        $p['kbl_nym_2x25'] = $products->create([
            'sku' => 'KBL-NYM-2X25', 'name' => 'Kabel NYM 2x2.5mm Eterna (SNI)',
            'base_unit_id' => $u['M'], 'min_stock' => 100, 'base_selling_price' => 10500,
            'units' => [['unit_id' => $u['ROLL'], 'conversion_factor' => 50, 'selling_price' => 495000]],
            'opening_qty' => 800, 'opening_cost' => 8000, 'opening_date' => '2026-01-02',
        ]);

        $p['kbl_nym_3x25'] = $products->create([
            'sku' => 'KBL-NYM-3X25', 'name' => 'Kabel NYM 3x2.5mm Supreme (SNI)',
            'base_unit_id' => $u['M'], 'min_stock' => 50, 'base_selling_price' => 16500,
            'units' => [['unit_id' => $u['ROLL'], 'conversion_factor' => 50, 'selling_price' => 760000]],
            'opening_qty' => 500, 'opening_cost' => 12500, 'opening_date' => '2026-01-02',
        ]);

        $p['kbl_nya_1x15'] = $products->create([
            'sku' => 'KBL-NYA-1X15', 'name' => 'Kabel NYA 1x1.5mm Eterna Hitam',
            'base_unit_id' => $u['M'], 'min_stock' => 100, 'base_selling_price' => 3800,
            'units' => [['unit_id' => $u['ROLL'], 'conversion_factor' => 100, 'selling_price' => 340000]],
            'opening_qty' => 1000, 'opening_cost' => 2800, 'opening_date' => '2026-01-02',
        ]);

        $p['kbl_twisted'] = $products->create([
            'sku' => 'KBL-TWIST-2X10', 'name' => 'Kabel Twisted SR 2x10mm Saluran Udara',
            'base_unit_id' => $u['M'], 'min_stock' => 100, 'base_selling_price' => 8000,
            'units' => [['unit_id' => $u['ROLL'], 'conversion_factor' => 100, 'selling_price' => 720000]],
            'opening_qty' => 800, 'opening_cost' => 5800, 'opening_date' => '2026-01-02',
        ]);

        // --- LAMPU & PENERANGAN ---
        $p['lmp_philips_8w'] = $products->create([
            'sku' => 'LMP-PHL-8W', 'name' => 'Lampu LED Philips MyCare 8W Putih',
            'base_unit_id' => $u['PCS'], 'min_stock' => 24, 'base_selling_price' => 36000,
            'units' => [['unit_id' => $u['DUS'], 'conversion_factor' => 12, 'selling_price' => 400000]],
            'opening_qty' => 240, 'opening_cost' => 28000, 'opening_date' => '2026-01-02',
        ]);

        $p['lmp_philips_12w'] = $products->create([
            'sku' => 'LMP-PHL-12W', 'name' => 'Lampu LED Philips MyCare 12W Putih',
            'base_unit_id' => $u['PCS'], 'min_stock' => 24, 'base_selling_price' => 49000,
            'units' => [['unit_id' => $u['DUS'], 'conversion_factor' => 12, 'selling_price' => 550000]],
            'opening_qty' => 240, 'opening_cost' => 39000, 'opening_date' => '2026-01-02',
        ]);

        $p['lmp_philips_19w'] = $products->create([
            'sku' => 'LMP-PHL-19W', 'name' => 'Lampu LED Philips MyCare 19W Putih',
            'base_unit_id' => $u['PCS'], 'min_stock' => 10, 'base_selling_price' => 78000,
            'units' => [['unit_id' => $u['DUS'], 'conversion_factor' => 10, 'selling_price' => 720000]],
            'opening_qty' => 150, 'opening_cost' => 60000, 'opening_date' => '2026-01-02',
        ]);

        $p['lmp_downlight_9w'] = $products->create([
            'sku' => 'LMP-DWN-9W', 'name' => 'Downlight LED Philips Meson 5 Inch 9W',
            'base_unit_id' => $u['PCS'], 'min_stock' => 12, 'base_selling_price' => 65000,
            'units' => [['unit_id' => $u['DUS'], 'conversion_factor' => 12, 'selling_price' => 720000]],
            'opening_qty' => 180, 'opening_cost' => 50000, 'opening_date' => '2026-01-02',
        ]);

        // --- SAKLAR & STOP KONTAK ---
        $p['skl_broco_single'] = $products->create([
            'sku' => 'SKL-BRC-SGL', 'name' => 'Saklar Tunggal Broco Gracio Cream',
            'base_unit_id' => $u['PCS'], 'min_stock' => 20, 'base_selling_price' => 17500,
            'units' => [['unit_id' => $u['BOX'], 'conversion_factor' => 10, 'selling_price' => 160000]],
            'opening_qty' => 250, 'opening_cost' => 13000, 'opening_date' => '2026-01-02',
        ]);

        $p['skl_broco_double'] = $products->create([
            'sku' => 'SKL-BRC-DBL', 'name' => 'Saklar Dobel Seri Broco Gracio Cream',
            'base_unit_id' => $u['PCS'], 'min_stock' => 20, 'base_selling_price' => 26000,
            'units' => [['unit_id' => $u['BOX'], 'conversion_factor' => 10, 'selling_price' => 240000]],
            'opening_qty' => 250, 'opening_cost' => 19500, 'opening_date' => '2026-01-02',
        ]);

        $p['stk_broco_arde'] = $products->create([
            'sku' => 'STK-BRC-ARD', 'name' => 'Stop Kontak Arde Broco Gracio Cream',
            'base_unit_id' => $u['PCS'], 'min_stock' => 30, 'base_selling_price' => 23000,
            'units' => [['unit_id' => $u['BOX'], 'conversion_factor' => 10, 'selling_price' => 210000]],
            'opening_qty' => 300, 'opening_cost' => 16500, 'opening_date' => '2026-01-02',
        ]);

        $p['stk_panasonic'] = $products->create([
            'sku' => 'STK-PAN-ARD', 'name' => 'Stop Kontak Arde Panasonic Wide Series',
            'base_unit_id' => $u['PCS'], 'min_stock' => 20, 'base_selling_price' => 33000,
            'units' => [['unit_id' => $u['BOX'], 'conversion_factor' => 10, 'selling_price' => 305000]],
            'opening_qty' => 200, 'opening_cost' => 25000, 'opening_date' => '2026-01-02',
        ]);

        $p['stk_colokan_male'] = $products->create([
            'sku' => 'STK-BRC-PLG', 'name' => 'Steker Colokan Arde Broco Putih',
            'base_unit_id' => $u['PCS'], 'min_stock' => 20, 'base_selling_price' => 14500,
            'units' => [['unit_id' => $u['BOX'], 'conversion_factor' => 20, 'selling_price' => 260000]],
            'opening_qty' => 200, 'opening_cost' => 11000, 'opening_date' => '2026-01-02',
        ]);

        // --- PENGAMAN & MCB ---
        $p['mcb_sch_4a'] = $products->create([
            'sku' => 'MCB-SCH-4A', 'name' => 'MCB Schneider Domae 1P 4A (900W)',
            'base_unit_id' => $u['PCS'], 'min_stock' => 12, 'base_selling_price' => 58000,
            'units' => [['unit_id' => $u['BOX'], 'conversion_factor' => 12, 'selling_price' => 650000]],
            'opening_qty' => 150, 'opening_cost' => 45000, 'opening_date' => '2026-01-02',
        ]);

        $p['mcb_sch_6a'] = $products->create([
            'sku' => 'MCB-SCH-6A', 'name' => 'MCB Schneider Domae 1P 6A (1300W)',
            'base_unit_id' => $u['PCS'], 'min_stock' => 12, 'base_selling_price' => 60000,
            'units' => [['unit_id' => $u['BOX'], 'conversion_factor' => 12, 'selling_price' => 670000]],
            'opening_qty' => 200, 'opening_cost' => 46000, 'opening_date' => '2026-01-02',
        ]);

        $p['mcb_sch_10a'] = $products->create([
            'sku' => 'MCB-SCH-10A', 'name' => 'MCB Schneider Domae 1P 10A (2200W)',
            'base_unit_id' => $u['PCS'], 'min_stock' => 12, 'base_selling_price' => 62000,
            'units' => [['unit_id' => $u['BOX'], 'conversion_factor' => 12, 'selling_price' => 690000]],
            'opening_qty' => 200, 'opening_cost' => 47000, 'opening_date' => '2026-01-02',
        ]);

        $p['box_mcb_4g'] = $products->create([
            'sku' => 'BOX-MCB-4G', 'name' => 'Box MCB Outbow Dexta 4 Group',
            'base_unit_id' => $u['PCS'], 'min_stock' => 10, 'base_selling_price' => 28000,
            'units' => [['unit_id' => $u['DUS'], 'conversion_factor' => 10, 'selling_price' => 250000]],
            'opening_qty' => 120, 'opening_cost' => 20000, 'opening_date' => '2026-01-02',
        ]);

        // --- PIPA, AKSESORIS & ISOLASI ---
        $p['pip_conduit_20'] = $products->create([
            'sku' => 'PIP-CND-20', 'name' => 'Pipa Conduit PVC 20mm Putih (3 Meter)',
            'base_unit_id' => $u['BATANG'], 'min_stock' => 30, 'base_selling_price' => 14000,
            'units' => [['unit_id' => $u['IKAT'], 'conversion_factor' => 10, 'selling_price' => 130000]],
            'opening_qty' => 300, 'opening_cost' => 10000, 'opening_date' => '2026-01-02',
        ]);

        $p['inbow_doos'] = $products->create([
            'sku' => 'ACC-INB-DOS', 'name' => 'Inbow Doos Saklar Broco PVC Hitam',
            'base_unit_id' => $u['PCS'], 'min_stock' => 50, 'base_selling_price' => 4500,
            'units' => [['unit_id' => $u['DUS'], 'conversion_factor' => 50, 'selling_price' => 200000]],
            'opening_qty' => 600, 'opening_cost' => 3200, 'opening_date' => '2026-01-02',
        ]);

        $p['isolasi_nitto'] = $products->create([
            'sku' => 'ISO-NIT-BLK', 'name' => 'Isolasi Listrik Nitto 2107 Hitam (20M)',
            'base_unit_id' => $u['PCS'], 'min_stock' => 30, 'base_selling_price' => 9500,
            'units' => [['unit_id' => $u['SLOP'], 'conversion_factor' => 10, 'selling_price' => 85000]],
            'opening_qty' => 300, 'opening_cost' => 7000, 'opening_date' => '2026-01-02',
        ]);

        // --- ALAT TEKNIK LISTRIK ---
        $p['alat_tespen'] = $products->create([
            'sku' => 'ALT-TSP-CHP', 'name' => 'Tespen Obeng Listrik Champion Asli',
            'base_unit_id' => $u['PCS'], 'min_stock' => 10, 'base_selling_price' => 18500,
            'units' => [['unit_id' => $u['LSN'], 'conversion_factor' => 12, 'selling_price' => 200000]],
            'opening_qty' => 120, 'opening_cost' => 14000, 'opening_date' => '2026-01-02',
        ]);

        $p['alat_tang'] = $products->create([
            'sku' => 'ALT-TNG-7IN', 'name' => 'Tang Kombinasi Listrik Tekiro 7 Inch',
            'base_unit_id' => $u['PCS'], 'min_stock' => 5, 'base_selling_price' => 69000,
            'opening_qty' => 50, 'opening_cost' => 53000, 'opening_date' => '2026-01-02',
        ]);

        $p['alat_multitester'] = $products->create([
            'sku' => 'ALT-MLT-DIG', 'name' => 'Digital Multitester Sanwa CD800a Original',
            'base_unit_id' => $u['PCS'], 'min_stock' => 2, 'base_selling_price' => 295000,
            'opening_qty' => 30, 'opening_cost' => 230000, 'opening_date' => '2026-01-02',
        ]);

        // Helper closures untuk mengambil ID ProductUnit satuan dasar & satuan grosir
        $baseUnit = fn (Product $prod) => $prod->units()->where('unit_id', $prod->base_unit_id)->first();
        $customUnit = fn (Product $prod, string $code) => $prod->units()->where('unit_id', $u[$code])->first();

        // =========================================================================
        // 3. SIMULASI TRANSAKSI BULANAN (JANUARI 2026 s.d. OKTOBER 2026)
        // =========================================================================

        $months = [
            1 => ['days' => 31, 'name' => 'Januari'],
            2 => ['days' => 28, 'name' => 'Februari'],
            3 => ['days' => 31, 'name' => 'Maret'],
            4 => ['days' => 30, 'name' => 'April'],
            5 => ['days' => 31, 'name' => 'Mei'],
            6 => ['days' => 30, 'name' => 'Juni'],
            7 => ['days' => 31, 'name' => 'Juli'],
            8 => ['days' => 31, 'name' => 'Agustus'],
            9 => ['days' => 30, 'name' => 'September'],
            10 => ['days' => 29, 'name' => 'Oktober'],
        ];

        foreach ($months as $month => $meta) {
            $monthPad = str_pad($month, 2, '0', STR_PAD_LEFT);

            // ---------------------------------------------------------------------
            // A. PENGADAAN BARANG (PURCHASE ORDER) - MINGGU KE-1 TIAP BULAN
            // ---------------------------------------------------------------------
            Auth::login($owner);
            $poDate = sprintf('2026-%s-04', $monthPad);
            Carbon::setTestNow(Carbon::parse("{$poDate} 09:30:00"));

            $supplier = match ($month % 4) {
                1 => $supKabel,
                2 => $supPhilips,
                3 => $supBroco,
                0 => $supSinar,
            };

            $poItems = match ($month % 4) {
                1 => [
                    ['product_unit_id' => $customUnit($p['kbl_nym_2x15'], 'ROLL')->id, 'qty_ordered' => 10, 'unit_price' => 295000],
                    ['product_unit_id' => $customUnit($p['kbl_nym_2x25'], 'ROLL')->id, 'qty_ordered' => 8, 'unit_price' => 415000],
                    ['product_unit_id' => $customUnit($p['kbl_nya_1x15'], 'ROLL')->id, 'qty_ordered' => 6, 'unit_price' => 290000],
                ],
                2 => [
                    ['product_unit_id' => $customUnit($p['lmp_philips_8w'], 'DUS')->id, 'qty_ordered' => 15, 'unit_price' => 320000],
                    ['product_unit_id' => $customUnit($p['lmp_philips_12w'], 'DUS')->id, 'qty_ordered' => 15, 'unit_price' => 450000],
                    ['product_unit_id' => $customUnit($p['lmp_downlight_9w'], 'DUS')->id, 'qty_ordered' => 10, 'unit_price' => 580000],
                ],
                3 => [
                    ['product_unit_id' => $customUnit($p['skl_broco_single'], 'BOX')->id, 'qty_ordered' => 20, 'unit_price' => 125000],
                    ['product_unit_id' => $customUnit($p['skl_broco_double'], 'BOX')->id, 'qty_ordered' => 20, 'unit_price' => 180000],
                    ['product_unit_id' => $customUnit($p['stk_broco_arde'], 'BOX')->id, 'qty_ordered' => 20, 'unit_price' => 160000],
                    ['product_unit_id' => $customUnit($p['mcb_sch_6a'], 'BOX')->id, 'qty_ordered' => 10, 'unit_price' => 530000],
                    ['product_unit_id' => $customUnit($p['mcb_sch_10a'], 'BOX')->id, 'qty_ordered' => 10, 'unit_price' => 550000],
                ],
                0 => [
                    ['product_unit_id' => $customUnit($p['pip_conduit_20'], 'IKAT')->id, 'qty_ordered' => 25, 'unit_price' => 98000],
                    ['product_unit_id' => $customUnit($p['inbow_doos'], 'DUS')->id, 'qty_ordered' => 10, 'unit_price' => 155000],
                    ['product_unit_id' => $customUnit($p['isolasi_nitto'], 'SLOP')->id, 'qty_ordered' => 20, 'unit_price' => 68000],
                ],
            };

            $po = $purchases->createOrder([
                'supplier_id' => $supplier->id,
                'order_date' => $poDate,
                'notes' => "Pengadaan stok bulanan {$meta['name']} 2026",
                'items' => $poItems,
            ]);

            // Penerimaan Barang (GR) 2 hari kemudian
            $recvDate = sprintf('2026-%s-06', $monthPad);
            Carbon::setTestNow(Carbon::parse("{$recvDate} 11:00:00"));
            Auth::login($admin);

            $poLines = $po->items()->get()->keyBy('id');
            $receiveData = [];
            foreach ($poLines as $itemId => $item) {
                // Di bulan Februari (month 2) dan Mei (month 5), ada 1 item reject (cacat kemasan)
                $reject = ($month == 2 || $month == 5) && $item === $poLines->first() ? 1 : 0;
                $receiveData[$itemId] = [
                    'qty_received' => (float) $item->qty_ordered,
                    'qty_rejected' => $reject,
                ];
            }
            $gr = $purchases->receive($po, $recvDate, $receiveData, 'Barang masuk dicek gudang, kondisi sesuai.');

            // Retur Pembelian (RB): Khusus di bulan Maret & Agustus ada retur 1 unit karena salah tipe
            if (($month == 3 || $month == 8) && $po->items()->count() > 1) {
                $retDate = sprintf('2026-%s-08', $monthPad);
                Carbon::setTestNow(Carbon::parse("{$retDate} 14:00:00"));
                Auth::login($owner);
                $retItem = $po->items()->first();
                if ($retItem->qtyReturnable() >= 1) {
                    $purchases->returnGoods(
                        $po,
                        $retDate,
                        [$retItem->id => 1],
                        'Salah kirim varian warna/tipe oleh supplier, diretur untuk potong utang.'
                    );
                }
            }

            // Pembayaran Utang Supplier (Bayar bertahap atau lunas via Bank)
            $payDate = sprintf('2026-%s-12', $monthPad);
            Carbon::setTestNow(Carbon::parse("{$payDate} 15:30:00"));
            Auth::login($owner);
            $poFresh = $po->fresh();
            if ($poFresh->outstandingAmount() > 0) {
                $payAmount = ($month % 2 === 0)
                    ? $poFresh->outstandingAmount()
                    : round($poFresh->outstandingAmount() * 0.75, 2);

                if ($payAmount > 0) {
                    $purchases->pay(
                        $poFresh,
                        $payDate,
                        $payAmount,
                        $bank,
                        "Pembayaran faktur PO {$po->po_number} via Transfer Bank"
                    );
                }
            }

            // ---------------------------------------------------------------------
            // B. PENJUALAN KASIR (POS) REGULER SEPANJANG BULAN
            // ---------------------------------------------------------------------
            Auth::login($cashier);

            $saleDays = match ($month) {
                10 => [3, 7, 11, 15, 18, 22, 25, 28, 29],
                default => [3, 6, 9, 13, 16, 20, 23, 27],
            };

            foreach ($saleDays as $day) {
                $dayPad = str_pad($day, 2, '0', STR_PAD_LEFT);
                $saleTime = sprintf('2026-%s-%s 10:15:00', $monthPad, $dayPad);
                Carbon::setTestNow(Carbon::parse($saleTime));

                // 1. Transaksi Retail Rumah Tangga (Meteran, Lampu, Saklar)
                $cartRetail = [
                    ['product_unit_id' => $baseUnit($p['kbl_nym_2x15'])->id, 'qty' => rand(5, 12)],
                    ['product_unit_id' => $baseUnit($p['lmp_philips_8w'])->id, 'qty' => rand(1, 3)],
                    ['product_unit_id' => $baseUnit($p['stk_broco_arde'])->id, 'qty' => rand(2, 4)],
                    ['product_unit_id' => $baseUnit($p['isolasi_nitto'])->id, 'qty' => rand(1, 2)],
                ];
                $sales->checkout($cartRetail, ($day % 3 === 0 ? 'qris' : 'cash'), 500000);

                // 2. Transaksi Retail / Proyek Kecil (Sore Hari)
                $saleTimeSore = sprintf('2026-%s-%s 14:45:00', $monthPad, $dayPad);
                Carbon::setTestNow(Carbon::parse($saleTimeSore));

                $cartMedium = [
                    ['product_unit_id' => $baseUnit($p['lmp_philips_12w'])->id, 'qty' => rand(2, 4)],
                    ['product_unit_id' => $baseUnit($p['skl_broco_single'])->id, 'qty' => rand(2, 4)],
                    ['product_unit_id' => $baseUnit($p['skl_broco_double'])->id, 'qty' => rand(1, 2)],
                    ['product_unit_id' => $baseUnit($p['mcb_sch_6a'])->id, 'qty' => rand(1, 2)],
                    ['product_unit_id' => $baseUnit($p['inbow_doos'])->id, 'qty' => rand(3, 6)],
                ];
                $method = ($day % 2 === 0) ? 'qris' : 'cash';
                $sales->checkout($cartMedium, $method, 500000);

                // 3. Transaksi Grosir / Kontraktor (Setiap hari kelipatan 6)
                if ($day % 6 === 0) {
                    $saleTimeGrosir = sprintf('2026-%s-%s 16:20:00', $monthPad, $dayPad);
                    Carbon::setTestNow(Carbon::parse($saleTimeGrosir));

                    $cartGrosir = [
                        ['product_unit_id' => $customUnit($p['kbl_nym_2x15'], 'ROLL')->id, 'qty' => 1],
                        ['product_unit_id' => $customUnit($p['pip_conduit_20'], 'IKAT')->id, 'qty' => 1],
                        ['product_unit_id' => $customUnit($p['stk_broco_arde'], 'BOX')->id, 'qty' => 1],
                    ];
                    $sales->checkout($cartGrosir, 'transfer', 0);
                }

                // 4. Simulasi Void Penjualan (Khusus di bulan 4 dan bulan 9)
                if (($month === 4 && $day === 16) || ($month === 9 && $day === 20)) {
                    $voidSale = $sales->checkout([
                        ['product_unit_id' => $baseUnit($p['alat_tespen'])->id, 'qty' => 2],
                        ['product_unit_id' => $baseUnit($p['alat_tang'])->id, 'qty' => 1],
                    ], 'cash', 200000);

                    // Owner void transaksi 15 menit kemudian
                    Carbon::setTestNow(Carbon::parse(sprintf('2026-%s-%s 16:35:00', $monthPad, $dayPad)));
                    Auth::login($owner);
                    $sales->void($voidSale, 'Pembatalan kasir: Pelanggan salah membawa barang dan membatalkan pembelian.');
                    Auth::login($cashier);
                }
            }

            // ---------------------------------------------------------------------
            // C. BEBAN OPERASIONAL BULANAN & PRIVE PEMILIK
            // ---------------------------------------------------------------------
            Auth::login($owner);

            // 1. Tagihan Listrik PLN, Air & Internet Toko (Tanggal 10)
            $expDate1 = sprintf('2026-%s-10', $monthPad);
            Carbon::setTestNow(Carbon::parse("{$expDate1} 13:00:00"));
            $cash->record([
                'type' => 'expense',
                'cash_account_id' => $bank,
                'amount' => rand(750000, 950000),
                'description' => "Beban Listrik PLN, PDAM & Internet Toko ({$meta['name']} 2026)",
                'transaction_date' => $expDate1,
            ]);

            // 2. Operasional Harian / Konsumsi & Kebersihan (Tanggal 15)
            $expDate2 = sprintf('2026-%s-15', $monthPad);
            Carbon::setTestNow(Carbon::parse("{$expDate2} 17:00:00"));
            $cash->record([
                'type' => 'expense',
                'cash_account_id' => $kas,
                'amount' => rand(350000, 500000),
                'description' => "Biaya operasional toko, ATK struk kasir & kebersihan ({$meta['name']})",
                'transaction_date' => $expDate2,
            ]);

            // 3. Gaji Karyawan Toko & Kasir (Tanggal 28 atau 29)
            $salaryDay = min(28, $meta['days']);
            $salaryDate = sprintf('2026-%s-%s', $monthPad, str_pad($salaryDay, 2, '0', STR_PAD_LEFT));
            Carbon::setTestNow(Carbon::parse("{$salaryDate} 17:30:00"));
            $cash->record([
                'type' => 'expense',
                'cash_account_id' => $bank,
                'amount' => 5_000_000,
                'description' => "Gaji staf kasir & admin gudang ({$meta['name']} 2026)",
                'transaction_date' => $salaryDate,
            ]);

            // 4. Prive Pemilik (Penarikan Modal Pribadi per Triwulan: Maret, Juni, September)
            if (in_array($month, [3, 6, 9])) {
                $priveDate = sprintf('2026-%s-28', $monthPad);
                Carbon::setTestNow(Carbon::parse("{$priveDate} 18:00:00"));
                $cash->record([
                    'type' => 'drawing',
                    'cash_account_id' => $bank,
                    'amount' => 8_000_000,
                    'description' => "Penarikan Prive Pemilik Toko Triwulan ({$meta['name']} 2026)",
                    'transaction_date' => $priveDate,
                ]);
            }
        }

        // =========================================================================
        // 4. TRANSAKSI KHUSUS HARI INI (29 OKTOBER 2026)
        // =========================================================================
        Carbon::setTestNow(Carbon::parse('2026-10-29 09:15:00'));
        Auth::login($cashier);

        // Penjualan pagi hari ini
        $sales->checkout([
            ['product_unit_id' => $baseUnit($p['kbl_nym_2x25'])->id, 'qty' => 10],
            ['product_unit_id' => $baseUnit($p['stk_panasonic'])->id, 'qty' => 3],
            ['product_unit_id' => $baseUnit($p['inbow_doos'])->id, 'qty' => 3],
        ], 'qris', 0);

        // Penjualan siang hari ini
        Carbon::setTestNow(Carbon::parse('2026-10-29 13:40:00'));
        $sales->checkout([
            ['product_unit_id' => $baseUnit($p['lmp_philips_12w'])->id, 'qty' => 3],
            ['product_unit_id' => $baseUnit($p['mcb_sch_10a'])->id, 'qty' => 1],
            ['product_unit_id' => $baseUnit($p['isolasi_nitto'])->id, 'qty' => 2],
        ], 'cash', 300000);

        // Reset waktu Carbon kembali ke waktu server nyata
        Carbon::setTestNow(null);
        Auth::logout();

        $this->command?->info('Seeding 1 tahun penuh (Januari - 29 Oktober 2026) BERHASIL!');
    }
}
