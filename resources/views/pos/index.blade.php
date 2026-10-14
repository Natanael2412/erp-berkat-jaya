<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>POS - ERP Berkat Jaya</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- AlpineJS for POS interaction -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="font-sans antialiased bg-brand-base text-brand-dark min-h-screen" 
          x-data="posSystem()"
          @keydown.window.f2.prevent="checkout()"
          @keydown.window.f4.prevent="$refs.searchInput.focus()"
          @keydown.window.escape.prevent="if(confirm('Batalkan seluruh keranjang?')) cart = []">
        
        <header class="bg-brand-dark text-white p-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold">POS - Berkat Jaya</h1>
            <div class="flex gap-4 items-center">
                <span>Kasir: {{ auth()->user()->name }}</span>
                <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-700 rounded hover:bg-gray-600">Ke Dashboard</a>
            </div>
        </header>

        <div class="flex h-[calc(100vh-72px)] overflow-hidden">
            <!-- Kiri: Daftar Produk -->
            <div class="w-2/3 p-4 flex flex-col border-r border-gray-200 bg-white">
                <div class="mb-4">
                    <input type="text" x-ref="searchInput" x-model="searchQuery" @keydown.enter="handleBarcodeScan" placeholder="[F4] Cari / Scan Barcode SKU..." class="w-full p-3 border rounded-lg border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary focus:ring-opacity-50">
                </div>
                
                <div class="flex-1 overflow-y-auto pr-2 grid grid-cols-2 md:grid-cols-3 gap-4 auto-rows-max">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="addToCart(product)" class="p-4 border rounded-lg cursor-pointer hover:border-brand-primary hover:shadow transition">
                            <div class="text-xs text-gray-500 mb-1" x-text="product.sku"></div>
                            <div class="font-bold text-lg mb-2" x-text="product.name"></div>
                            <div class="flex justify-between items-end">
                                <span class="text-brand-primary font-bold">Rp <span x-text="product.price"></span></span>
                                <span class="text-xs px-2 py-1 bg-gray-100 rounded text-gray-600">Stok: <span x-text="product.current_stock"></span></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Kanan: Keranjang & Kalkulasi -->
            <div class="w-1/3 bg-gray-50 flex flex-col">
                <div class="p-4 border-b border-gray-200 bg-white shadow-sm font-bold text-lg">Keranjang</div>
                
                <div class="flex-1 overflow-y-auto p-4">
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="bg-white p-3 rounded shadow-sm border border-gray-100 mb-3 relative">
                            <button @click="removeFromCart(index)" class="absolute top-2 right-2 text-brand-danger font-bold hover:bg-red-50 px-2 rounded">&times;</button>
                            <div class="font-bold mb-2" x-text="item.name"></div>
                            <div class="flex justify-between items-center">
                                <div class="flex border rounded w-24">
                                    <button @click="updateQty(index, -1)" class="w-8 text-center bg-gray-100 hover:bg-gray-200">-</button>
                                    <input type="number" x-model.number="item.qty" class="w-full text-center border-0 p-1 text-sm focus:ring-0" min="0.1" step="0.1">
                                    <button @click="updateQty(index, 1)" class="w-8 text-center bg-gray-100 hover:bg-gray-200">+</button>
                                </div>
                                <div class="font-bold text-brand-dark">Rp <span x-text="item.qty * item.selling_price"></span></div>
                            </div>
                        </div>
                    </template>
                    <div x-show="cart.length === 0" class="text-center text-gray-400 mt-10">
                        Keranjang Kosong
                    </div>
                </div>

                <div class="p-4 bg-white border-t border-gray-200">
                    <div class="flex justify-between mb-4">
                        <span class="text-gray-600">Total Pembayaran</span>
                        <span class="text-2xl font-black text-brand-primary">Rp <span x-text="totalAmount"></span></span>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
                        <select x-model="paymentMethod" class="w-full border-gray-300 rounded focus:ring-brand-primary focus:border-brand-primary">
                            <option value="Tunai">Tunai</option>
                            <option value="Transfer">Transfer</option>
                            <option value="QRIS">QRIS</option>
                        </select>
                    </div>

                    <button @click="checkout" :disabled="cart.length === 0 || isProcessing" class="w-full py-3 bg-brand-primary text-white rounded font-bold text-lg hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isProcessing">[F2] Bayar / Selesai</span>
                        <span x-show="isProcessing">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>

        <script>
            function posSystem() {
                return {
                    searchQuery: '',
                    products: @json($products).map(p => ({...p, price: p.current_hpp * 1.2})), // dummy selling price
                    cart: [],
                    paymentMethod: 'Tunai',
                    isProcessing: false,
                    
                    init() {
                        this.$nextTick(() => { this.$refs.searchInput.focus(); });
                    },
                    
                    get filteredProducts() {
                        if(this.searchQuery === '') return this.products;
                        const lowerCaseQuery = this.searchQuery.toLowerCase();
                        return this.products.filter(p => 
                            p.name.toLowerCase().includes(lowerCaseQuery) || 
                            p.sku.toLowerCase().includes(lowerCaseQuery)
                        );
                    },

                    handleBarcodeScan() {
                        const exactMatch = this.products.find(p => p.sku.toLowerCase() === this.searchQuery.toLowerCase());
                        if (exactMatch) {
                            this.addToCart(exactMatch);
                            this.searchQuery = ''; // reset search setelah scan
                        } else if (this.filteredProducts.length === 1) {
                            this.addToCart(this.filteredProducts[0]);
                            this.searchQuery = '';
                        }
                    },

                    addToCart(product) {
                        const existingItem = this.cart.find(i => i.product_id === product.id);
                        if(existingItem) {
                            if(existingItem.qty + 1 <= product.current_stock) {
                                existingItem.qty += 1;
                            } else {
                                alert('Peringatan: Stok tidak mencukupi!');
                            }
                        } else {
                            this.cart.push({
                                product_id: product.id,
                                name: product.name,
                                qty: 1,
                                selling_price: product.price,
                                max_stock: product.current_stock
                            });
                        }
                    },

                    updateQty(index, amount) {
                        const item = this.cart[index];
                        const newQty = item.qty + amount;
                        if(newQty > 0 && newQty <= item.max_stock) {
                            item.qty = newQty;
                        } else if(newQty > item.max_stock) {
                            alert('Peringatan: Stok tidak mencukupi!');
                        }
                    },

                    removeFromCart(index) {
                        this.cart.splice(index, 1);
                    },

                    get totalAmount() {
                        return this.cart.reduce((total, item) => total + (item.qty * item.selling_price), 0);
                    },

                    async checkout() {
                        this.isProcessing = true;
                        
                        try {
                            const response = await fetch('{{ route("pos.checkout") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    payment_method: this.paymentMethod,
                                    cart: this.cart
                                })
                            });

                            const data = await response.json();

                            if(response.ok && data.message) {
                                // Buka struk thermal di tab/window baru untuk print
                                window.open(data.receipt_url, '_blank', 'width=400,height=600');
                                // Refresh halaman kasir
                                window.location.reload();
                            } else {
                                alert('Error: ' + data.message);
                            }
                        } catch(err) {
                            alert('Terjadi kesalahan koneksi.');
                        } finally {
                            this.isProcessing = false;
                        }
                    }
                }
            }
        </script>
    </body>
</html>
