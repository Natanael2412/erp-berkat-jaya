<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Kasir (POS) · Toko Listrik Berkat Jaya</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- AlpineJS -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="font-sans antialiased bg-slate-100 text-slate-800 min-h-screen select-none" 
          x-data="posSystem()"
          @keydown.window.f2.prevent="checkout()"
          @keydown.window.f4.prevent="$refs.searchInput.focus()"
          @keydown.window.escape.prevent="if(cart.length > 0 && confirm('Kosongkan keranjang belanja?')) cart = []">
        
        {{-- Header Bar --}}
        <header class="bg-brand-ink text-white px-5 py-3 flex justify-between items-center shadow-md border-b border-white/10">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/20 text-emerald-400 font-black text-lg">
                    ⚡
                </span>
                <div>
                    <h1 class="text-lg font-black tracking-tight leading-none text-white">POS · Berkat Jaya</h1>
                    <span class="text-[11px] text-slate-400 font-medium">Kasir Toko Listrik & Instalasi</span>
                </div>
            </div>

            <div class="flex items-center gap-4 text-xs font-semibold">
                <div class="hidden sm:flex items-center gap-3 bg-white/10 px-3 py-1.5 rounded-lg border border-white/10">
                    <span class="text-slate-300">Kasir: <strong class="text-white">{{ auth()->user()->name }}</strong></span>
                    <span class="text-slate-500">|</span>
                    <span class="text-emerald-400">Transaksi Hari Ini: <strong>{{ $todayCount ?? 0 }}</strong> ({{ rp($todayTotal ?? 0) }})</span>
                </div>
                <a href="{{ route('dashboard') }}" class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition flex items-center gap-1.5">
                    <span>&larr; Dashboard</span>
                </a>
            </div>
        </header>

        {{-- Main Layout --}}
        <div class="flex h-[calc(100vh-61px)] overflow-hidden">
            
            {{-- Bagian Kiri: Katalog & Pencarian Produk --}}
            <div class="w-7/12 lg:w-8/12 p-4 flex flex-col bg-white border-r border-slate-200">
                
                {{-- Search & Shortcut Header --}}
                <div class="mb-3 flex items-center gap-3">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" 
                               x-ref="searchInput" 
                               x-model="searchQuery" 
                               @keydown.enter.prevent="handleBarcodeScan" 
                               placeholder="[F4] Cari nama produk atau scan barcode SKU..." 
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-medium focus:bg-white focus:border-brand-teal focus:ring-2 focus:ring-brand-teal/20 transition">
                    </div>
                    <span class="text-xs text-slate-400 font-semibold hidden md:inline-block">
                        Tekan <kbd class="px-1.5 py-0.5 bg-slate-100 border rounded font-mono text-[10px]">Enter</kbd> untuk scan otomatis
                    </span>
                </div>

                {{-- Product Grid --}}
                <div class="flex-1 overflow-y-auto pr-1 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 content-start">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="addToCart(product)" 
                             class="p-3.5 rounded-xl border border-slate-200 hover:border-brand-teal bg-white hover:bg-teal-50/20 cursor-pointer shadow-sm hover:shadow transition-all flex flex-col justify-between group">
                            
                            <div>
                                <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                                    <span class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-[10px] group-hover:bg-teal-100 text-slate-700 transition" x-text="product.sku"></span>
                                    <template x-if="product.base_allows_decimal">
                                        <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">
                                            Meteran
                                        </span>
                                    </template>
                                </div>
                                <h3 class="font-bold text-slate-900 text-sm line-clamp-2 leading-snug group-hover:text-brand-teal transition" x-text="product.name"></h3>
                            </div>

                            <div class="mt-3 pt-2 border-t border-slate-100 flex items-end justify-between">
                                <div>
                                    <div class="text-[11px] text-slate-400">Harga Satuan:</div>
                                    <div class="text-sm font-extrabold text-brand-teal">
                                        Rp <span x-text="formatNumber(product.default_price)"></span>
                                        <span class="text-[10px] text-slate-500 font-normal">/<span x-text="product.base_unit"></span></span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block text-[11px] px-2 py-0.5 rounded-md font-semibold"
                                          :class="product.stock > 10 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : (product.stock > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-red-50 text-red-700 border border-red-200')">
                                        Stok: <span x-text="product.formatted_stock"></span>
                                    </span>
                                </div>
                            </div>

                        </div>
                    </template>

                    <div x-show="filteredProducts.length === 0" class="col-span-full py-16 text-center text-slate-400 text-sm">
                        Produk tidak ditemukan dengan kata kunci "<span class="font-bold text-slate-600" x-text="searchQuery"></span>"
                    </div>
                </div>

            </div>

            {{-- Bagian Kanan: Keranjang Kasir & Pembayaran --}}
            <div class="w-5/12 lg:w-4/12 bg-slate-50 flex flex-col justify-between h-full border-l border-slate-200 shadow-lg">
                
                {{-- Cart Header --}}
                <div class="p-4 bg-white border-b border-slate-200 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <span class="text-base font-bold text-slate-900">Keranjang Belanja</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-brand-teal/10 text-brand-teal" x-text="cart.length + ' item'"></span>
                    </div>
                    <button type="button" 
                            x-show="cart.length > 0" 
                            @click="if(confirm('Kosongkan keranjang?')) cart = []" 
                            class="text-xs text-red-600 hover:text-red-700 font-semibold hover:underline">
                        Kosongkan [Esc]
                    </button>
                </div>

                {{-- Cart Items List --}}
                <div class="flex-1 overflow-y-auto p-3 space-y-2.5">
                    <template x-for="(item, index) in cart" :key="item.product_id + '-' + item.product_unit_id">
                        <div class="bg-white p-3 rounded-xl border border-slate-200/90 shadow-sm relative group">
                            
                            {{-- Remove Button --}}
                            <button type="button" 
                                    @click="removeFromCart(index)" 
                                    class="absolute top-2.5 right-2.5 w-6 h-6 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 flex items-center justify-center font-bold text-sm transition"
                                    title="Hapus baris">
                                &times;
                            </button>

                            {{-- Product Title --}}
                            <div class="pr-6">
                                <div class="font-bold text-slate-900 text-xs sm:text-sm line-clamp-1" x-text="item.name"></div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5" x-text="item.sku"></div>
                            </div>

                            {{-- Unit Dropdown & Pricing --}}
                            <div class="mt-2.5 flex items-center justify-between gap-2">
                                
                                {{-- Dropdown Pilihan Satuan (Jika produk punya > 1 satuan) --}}
                                <div class="flex-1">
                                    <template x-if="item.available_units.length > 1">
                                        <select x-model.number="item.product_unit_id" 
                                                @change="onUnitChanged(item)" 
                                                class="w-full text-xs py-1 px-2 border border-slate-300 rounded-lg bg-slate-50 font-semibold text-slate-700 focus:ring-1 focus:ring-brand-teal focus:border-brand-teal">
                                            <template x-for="u in item.available_units" :key="u.id">
                                                <option :value="u.id" 
                                                        :selected="u.id === item.product_unit_id" 
                                                        x-text="u.name + ' (' + u.formatted_price + ')'">
                                                </option>
                                            </template>
                                        </select>
                                    </template>
                                    <template x-if="item.available_units.length <= 1">
                                        <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-2 py-1 rounded-md" x-text="item.unit_name"></span>
                                    </template>
                                </div>

                                {{-- Price per Unit Info --}}
                                <div class="text-right">
                                    <span class="text-xs font-bold text-slate-600">@ Rp <span x-text="formatNumber(item.price)"></span></span>
                                </div>
                            </div>

                            {{-- Qty Counter & Subtotal --}}
                            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                                
                                {{-- Qty Input Stepper --}}
                                <div class="flex items-center border border-slate-300 rounded-lg overflow-hidden bg-slate-50">
                                    <button type="button" 
                                            @click="updateQty(index, item.allows_decimal ? -0.5 : -1)" 
                                            class="w-7 h-7 flex items-center justify-center font-bold text-slate-600 hover:bg-slate-200 transition">
                                        -
                                    </button>
                                    
                                    <input type="number" 
                                           x-model.number="item.qty" 
                                           @input="validateItemQty(item)" 
                                           :step="item.allows_decimal ? '0.1' : '1'" 
                                           :min="item.allows_decimal ? '0.1' : '1'" 
                                           class="w-16 h-7 text-center border-0 p-0 text-xs font-bold text-slate-900 bg-white focus:ring-0">

                                    <button type="button" 
                                            @click="updateQty(index, item.allows_decimal ? 0.5 : 1)" 
                                            class="w-7 h-7 flex items-center justify-center font-bold text-slate-600 hover:bg-slate-200 transition">
                                        +
                                    </button>
                                </div>

                                {{-- Decimal badge notification --}}
                                <div class="text-[11px] text-slate-400 font-medium">
                                    <span x-text="item.unit_name"></span>
                                    <span x-show="item.allows_decimal" class="text-[10px] text-amber-600 font-semibold">(Pecahan)</span>
                                </div>

                                {{-- Subtotal --}}
                                <div class="text-right font-black text-sm text-slate-900">
                                    Rp <span x-text="formatNumber(item.qty * item.price)"></span>
                                </div>
                            </div>

                        </div>
                    </template>

                    {{-- Empty State --}}
                    <div x-show="cart.length === 0" class="py-16 text-center text-slate-400 text-sm">
                        <div class="text-3xl mb-2">🛒</div>
                        <p class="font-semibold text-slate-600">Keranjang Masih Kosong</p>
                        <p class="text-xs text-slate-400 mt-1">Klik produk di sebelah kiri atau scan barcode SKU</p>
                    </div>
                </div>

                {{-- Payment Panel (Bawah) --}}
                <div class="p-4 bg-white border-t border-slate-200 shadow-md space-y-3.5">
                    
                    {{-- Total Amount --}}
                    <div class="flex items-baseline justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Pembayaran</span>
                        <div class="text-2xl font-black text-brand-teal">
                            Rp <span x-text="formatNumber(totalAmount)"></span>
                        </div>
                    </div>

                    {{-- Metode Pembayaran --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Metode Pembayaran</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" 
                                    @click="paymentMethod = 'cash'" 
                                    :class="paymentMethod === 'cash' ? 'bg-brand-teal text-white font-bold border-brand-teal' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-300'"
                                    class="py-2 text-xs rounded-xl border transition text-center">
                                Tunai / Cash
                            </button>
                            <button type="button" 
                                    @click="paymentMethod = 'transfer'" 
                                    :class="paymentMethod === 'transfer' ? 'bg-brand-teal text-white font-bold border-brand-teal' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-300'"
                                    class="py-2 text-xs rounded-xl border transition text-center">
                                Transfer Bank
                            </button>
                            <button type="button" 
                                    @click="paymentMethod = 'qris'" 
                                    :class="paymentMethod === 'qris' ? 'bg-brand-teal text-white font-bold border-brand-teal' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-300'"
                                    class="py-2 text-xs rounded-xl border transition text-center">
                                QRIS
                            </button>
                        </div>
                    </div>

                    {{-- Input Uang Tunai & Kembalian (Khusus Tunai) --}}
                    <div x-show="paymentMethod === 'cash'" class="space-y-2 pt-1 border-t border-slate-100">
                        <div class="flex items-center justify-between gap-3">
                            <label class="text-xs font-bold text-slate-600">Uang Diterima (Rp):</label>
                            <input type="number" 
                                   x-model.number="amountPaid" 
                                   min="0" 
                                   step="1000"
                                   placeholder="0"
                                   class="w-36 text-right py-1.5 px-2.5 border border-slate-300 rounded-lg text-sm font-bold text-slate-900 focus:ring-1 focus:ring-brand-teal focus:border-brand-teal">
                        </div>

                        {{-- Quick amount pills --}}
                        <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                            <button type="button" @click="amountPaid = totalAmount" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded text-slate-700 font-semibold transition">Uang Pas</button>
                            <button type="button" @click="amountPaid = 50000" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded text-slate-700 transition">50.000</button>
                            <button type="button" @click="amountPaid = 100000" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded text-slate-700 transition">100.000</button>
                            <button type="button" @click="amountPaid = 200000" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded text-slate-700 transition">200.000</button>
                            <button type="button" @click="amountPaid = 500000" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded text-slate-700 transition">500.000</button>
                        </div>

                        {{-- Kembalian --}}
                        <div class="flex items-baseline justify-between pt-1">
                            <span class="text-xs font-semibold text-slate-500">Kembalian:</span>
                            <span class="text-sm font-black" :class="changeAmount >= 0 ? 'text-emerald-600' : 'text-red-500'">
                                Rp <span x-text="formatNumber(changeAmount >= 0 ? changeAmount : 0)"></span>
                            </span>
                        </div>
                    </div>

                    {{-- Tombol Checkout F2 --}}
                    <button type="button" 
                            @click="checkout()" 
                            :disabled="cart.length === 0 || isProcessing || (paymentMethod === 'cash' && amountPaid < totalAmount)" 
                            class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-brand-teal to-emerald-700 hover:from-teal-700 hover:to-emerald-800 disabled:from-slate-300 disabled:to-slate-300 disabled:cursor-not-allowed text-white font-extrabold text-sm uppercase tracking-wide shadow-md transition-all">
                        <span x-show="!isProcessing">[F2] Bayar & Cetak Struk</span>
                        <span x-show="isProcessing" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Memproses Transaksi...
                        </span>
                    </button>

                </div>

            </div>

        </div>

        {{-- POS JavaScript Logic --}}
        <script>
            function posSystem() {
                return {
                    searchQuery: '',
                    products: @json($products),
                    cart: [],
                    paymentMethod: 'cash',
                    amountPaid: 0,
                    isProcessing: false,
                    
                    init() {
                        this.$nextTick(() => { 
                            if (this.$refs.searchInput) {
                                this.$refs.searchInput.focus(); 
                            }
                        });
                    },
                    
                    get filteredProducts() {
                        if (!this.searchQuery || this.searchQuery.trim() === '') {
                            return this.products;
                        }
                        const q = this.searchQuery.toLowerCase().trim();
                        return this.products.filter(p => 
                            (p.name && p.name.toLowerCase().includes(q)) || 
                            (p.sku && p.sku.toLowerCase().includes(q))
                        );
                    },

                    handleBarcodeScan() {
                        if (!this.searchQuery || this.searchQuery.trim() === '') return;
                        const q = this.searchQuery.toLowerCase().trim();
                        
                        const exactSku = this.products.find(p => p.sku && p.sku.toLowerCase() === q);
                        if (exactSku) {
                            this.addToCart(exactSku);
                            this.searchQuery = '';
                            return;
                        }

                        if (this.filteredProducts.length === 1) {
                            this.addToCart(this.filteredProducts[0]);
                            this.searchQuery = '';
                        }
                    },

                    addToCart(product) {
                        // Cari default product_unit_id
                        const defaultUnitId = product.default_unit_id || (product.units && product.units[0] ? product.units[0].id : null);
                        const selectedUnit = product.units.find(u => u.id === defaultUnitId) || product.units[0];

                        if (!selectedUnit) {
                            alert('Produk ini belum memiliki satuan aktif.');
                            return;
                        }

                        // Cek apakah item dengan product_id & product_unit_id yang sama sudah ada di keranjang
                        const existing = this.cart.find(i => i.product_id === product.id && i.product_unit_id === selectedUnit.id);
                        
                        if (existing) {
                            const addQty = existing.allows_decimal ? 1 : 1;
                            existing.qty = Math.round((existing.qty + addQty) * 100) / 100;
                        } else {
                            this.cart.push({
                                product_id: product.id,
                                product_unit_id: selectedUnit.id,
                                sku: product.sku,
                                name: product.name,
                                unit_name: selectedUnit.name,
                                allows_decimal: !!selectedUnit.allows_decimal,
                                price: parseFloat(selectedUnit.price) || 0,
                                qty: 1,
                                max_stock_base: parseFloat(product.stock) || 0,
                                available_units: product.units || []
                            });
                        }
                    },

                    onUnitChanged(item) {
                        const newUnit = item.available_units.find(u => u.id === item.product_unit_id);
                        if (newUnit) {
                            item.unit_name = newUnit.name;
                            item.price = parseFloat(newUnit.price) || 0;
                            item.allows_decimal = !!newUnit.allows_decimal;
                            this.validateItemQty(item);
                        }
                    },

                    validateItemQty(item) {
                        let val = parseFloat(item.qty);
                        if (isNaN(val) || val <= 0) {
                            val = item.allows_decimal ? 0.1 : 1;
                        }

                        // KETAT: Jika satuan TIDAK mengizinkan desimal (e.g. Pcs, Box, Dus, Batang), bulatkan ke integer!
                        if (!item.allows_decimal) {
                            val = Math.max(1, Math.round(val));
                        } else {
                            // Untuk satuan desimal (e.g. Meter), izinkan hingga 2 tempat desimal
                            val = Math.round(val * 100) / 100;
                        }

                        item.qty = val;
                    },

                    updateQty(index, delta) {
                        const item = this.cart[index];
                        if (!item) return;

                        let newQty = parseFloat(item.qty) + delta;
                        if (!item.allows_decimal) {
                            newQty = Math.round(newQty);
                        } else {
                            newQty = Math.round(newQty * 100) / 100;
                        }

                        if (newQty > 0) {
                            item.qty = newQty;
                        } else {
                            this.removeFromCart(index);
                        }
                    },

                    removeFromCart(index) {
                        this.cart.splice(index, 1);
                    },

                    get totalAmount() {
                        return this.cart.reduce((total, item) => {
                            const q = parseFloat(item.qty) || 0;
                            const p = parseFloat(item.price) || 0;
                            return total + Math.round(q * p);
                        }, 0);
                    },

                    get changeAmount() {
                        if (this.paymentMethod !== 'cash') return 0;
                        const paid = parseFloat(this.amountPaid) || 0;
                        return paid - this.totalAmount;
                    },

                    formatNumber(val) {
                        const num = parseFloat(val);
                        if (isNaN(num)) return '0';
                        return Math.round(num).toLocaleString('id-ID');
                    },

                    formatQty(val, allowsDecimal) {
                        const num = parseFloat(val);
                        if (isNaN(num)) return '0';
                        if (!allowsDecimal || Math.floor(num) === num) {
                            return Math.floor(num).toLocaleString('id-ID');
                        }
                        return num.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
                    },

                    async checkout() {
                        if (this.cart.length === 0) {
                            alert('Keranjang belanja masih kosong.');
                            return;
                        }

                        if (this.paymentMethod === 'cash') {
                            if (this.amountPaid < this.totalAmount) {
                                alert('Uang yang diterima kurang dari total belanja (Rp ' + this.formatNumber(this.totalAmount) + ').');
                                return;
                            }
                        }

                        this.isProcessing = true;
                        
                        try {
                            const payload = {
                                payment_method: this.paymentMethod,
                                amount_paid: this.paymentMethod === 'cash' ? this.amountPaid : this.totalAmount,
                                cart: this.cart.map(item => ({
                                    product_unit_id: item.product_unit_id,
                                    qty: item.qty
                                }))
                            };

                            const response = await fetch('{{ route("pos.checkout") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify(payload)
                            });

                            const data = await response.json();

                            if (response.ok && data.message) {
                                if (data.receipt_url) {
                                    window.open(data.receipt_url, '_blank', 'width=420,height=600');
                                }
                                alert('Transaksi ' + (data.invoice_number || '') + ' BERHASIL!');
                                window.location.reload();
                            } else {
                                alert(data.message || 'Terjadi kesalahan saat memproses transaksi.');
                            }
                        } catch (err) {
                            console.error(err);
                            alert('Terjadi kesalahan jaringan/koneksi saat checkout.');
                        } finally {
                            this.isProcessing = false;
                        }
                    }
                };
            }
        </script>
    </body>
</html>
