<template>
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Receive Goods</h2>
                    <p class="text-gray-600">Add new products or receive stock for existing products in one step</p>
                </div>
                <div class="flex space-x-3">
                    <Link
                        :href="route('inventory.transactions.index')"
                        class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700"
                    >
                        Back to Transactions
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Quick Barcode Scanner Section -->
                <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-xl shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1v-2a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-900">Quick Add by Barcode</h3>
                            </div>
                            
                            <div class="flex space-x-4">
                                <div class="flex-1">
                                    <div class="flex">
                                        <input
                                            type="text"
                                            v-model="quickAddInput"
                                            @keyup.enter="handleQuickAdd"
                                            placeholder="Scan barcode or enter SKU"
                                            class="flex-1 border-gray-300 rounded-l-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            ref="quickAddInput"
                                        />
                                        <button
                                            @click="handleQuickAdd"
                                            class="px-6 py-3 bg-blue-600 text-white font-medium rounded-r-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                            Add Product
                                        </button>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-600">
                                        Press Enter or click Add to search products by barcode/SKU
                                    </p>
                                </div>
                                
                                <button
                                    @click="toggleScanner"
                                    class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-medium rounded-lg hover:from-green-600 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 flex items-center"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ isScannerActive ? 'Stop Scanner' : 'Camera Scanner' }}
                                </button>
                            </div>
                            
                            <div v-if="quickAddError" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-sm text-red-700">{{ quickAddError }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Scanner Preview -->
                    <div v-if="isScannerActive" class="mt-6">
                        <div class="border-2 border-blue-400 rounded-xl overflow-hidden shadow-lg">
                            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-4">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        <svg class="w-6 h-6 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <h4 class="text-lg font-semibold text-white">Live Barcode Scanner</h4>
                                    </div>
                                    <button
                                        @click="stopScanner"
                                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center"
                                    >
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Stop
                                    </button>
                                </div>
                            </div>
                            
                            <div class="relative bg-black p-4">
                                <!-- Scanner video will be inserted here -->
                                <div id="scanner-container" class="rounded-lg overflow-hidden mx-auto" style="max-width: 640px;">
                                    <div v-if="!scannerActive" class="flex flex-col items-center justify-center h-64 bg-gray-900">
                                        <svg class="w-16 h-16 text-blue-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <p class="text-lg font-medium text-blue-300 mb-3">Camera Ready</p>
                                        <button
                                            @click="startScanner"
                                            class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-medium rounded-lg hover:from-blue-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                            Start Camera Scanner
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Scanning indicator -->
                                <div v-if="scannerActive" class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-green-400 to-transparent animate-pulse"></div>
                                
                                <div v-if="scannerActive" class="mt-4 text-center">
                                    <p class="text-green-400 font-medium animate-pulse">Scanning... Point camera at barcode</p>
                                    <p class="text-sm text-gray-400 mt-1">Ensure good lighting and steady camera</p>
                                </div>
                            </div>
                            
                            <!-- Recent Scans -->
                            <div v-if="recentScans.length > 0" class="bg-gray-50 p-4 border-t">
                                <h5 class="text-sm font-medium text-gray-700 mb-3">Recent Scans</h5>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                    <button
                                        v-for="(scan, index) in recentScans.slice(0, 6)"
                                        :key="index"
                                        @click="useScannedBarcode(scan)"
                                        class="group p-3 bg-white hover:bg-blue-50 rounded-lg border border-gray-200 hover:border-blue-300 transition-all duration-200"
                                    >
                                        <div class="flex justify-between items-center">
                                            <div class="text-left">
                                                <span class="text-sm font-mono font-medium text-gray-900 group-hover:text-blue-600">{{ scan }}</span>
                                                <div class="text-xs text-gray-500 group-hover:text-blue-500">Click to use</div>
                                            </div>
                                            <svg class="w-5 h-5 text-blue-500 group-hover:text-blue-600 transform group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Form -->
                <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                    <!-- Form Header -->
                    <div class="bg-gradient-to-r from-gray-800 to-gray-900 p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-xl font-bold text-white">Goods Receiving Form</h3>
                                <p class="text-gray-300">Complete the form below to receive goods</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="px-3 py-1 bg-blue-500 text-white text-sm font-medium rounded-full">
                                    {{ form.items.length }} Item(s)
                                </span>
                                <button
                                    @click="addItem"
                                    class="px-4 py-2 bg-green-500 text-white font-medium rounded-lg hover:bg-green-600 flex items-center"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Add Item
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Receiving Information -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Warehouse Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <span class="text-red-500">*</span> Warehouse
                                </label>
                                <select
                                    v-model="form.warehouse_id"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required
                                    @change="onWarehouseChange"
                                >
                                    <option value="">Select Warehouse</option>
                                    <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                                        {{ warehouse.name }} ({{ warehouse.code }})
                                    </option>
                                </select>
                                <div v-if="selectedWarehouse" class="mt-2 text-sm text-gray-600">
                                    <span class="font-medium">Location:</span> {{ selectedWarehouse.location }}
                                </div>
                            </div>

                            <!-- Supplier Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                                <select
                                    v-model="form.supplier_id"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">Select Supplier</option>
                                    <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                        {{ supplier.name }} ({{ supplier.supplier_code }})
                                    </option>
                                </select>
                            </div>

                            <!-- Reference Number -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                                <div class="flex">
                                    <input
                                        type="text"
                                        v-model="form.reference_number"
                                        placeholder="Auto-generated"
                                        class="flex-1 border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        readonly
                                    >
                                    <button
                                        type="button"
                                        @click="generateReferenceNumber"
                                        class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
                                        title="Generate New Reference"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Details</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Quantity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Cost Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Selling Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Total Cost</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(item, index) in form.items" :key="index" 
                                        :class="item.type === 'new' ? 'bg-blue-50' : 'bg-white'">
                                        <!-- Type Selection -->
                                        <td class="px-6 py-4">
                                            <div class="relative">
                                                <select
                                                    v-model="item.type"
                                                    @change="handleItemTypeChange(index)"
                                                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                >
                                                    <option value="existing">Existing</option>
                                                    <option value="new">New Product</option>
                                                </select>
                                                <div v-if="item.type === 'new'" class="absolute -top-2 -right-2">
                                                    <span class="px-2 py-1 text-xs font-medium bg-blue-500 text-white rounded-full">NEW</span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Product Details -->
                                        <td class="px-6 py-4">
                                            <div v-if="item.type === 'existing'" class="space-y-3">
                                                <!-- Product Search -->
                                                <div>
                                                    <select
                                                        v-model="item.product_id"
                                                        @change="handleProductSelect(index)"
                                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    >
                                                        <option value="">Search Product...</option>
                                                        <option v-for="product in filteredProducts" :key="product.id" :value="product.id">
                                                            {{ product.name }} ({{ product.sku }}) - Stock: {{ product.stock_quantity || 0 }}
                                                        </option>
                                                    </select>
                                                </div>
                                                
                                                <!-- Product Info -->
                                                <div v-if="selectedProductDetails[index]" class="bg-gray-50 p-3 rounded-lg">
                                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                                        <div>
                                                            <span class="text-gray-600">Current Stock:</span>
                                                            <span class="ml-2 font-medium">{{ selectedProductDetails[index].stock_quantity || 0 }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-gray-600">Last Cost:</span>
                                                            <span class="ml-2 font-medium">₵{{ selectedProductDetails[index].cost_price || 0 }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- New Product Form -->
                                            <div v-else class="space-y-4">
                                                <!-- Product Name -->
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                                                    <input
                                                        type="text"
                                                        v-model="item.name"
                                                        placeholder="Enter product name"
                                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                        required
                                                    />
                                                </div>
                                                
                                                <!-- SKU & Barcode -->
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                                                        <div class="flex">
                                                            <input
                                                                type="text"
                                                                v-model="item.sku"
                                                                placeholder="Auto-generated"
                                                                class="flex-1 border-gray-300 rounded-l-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                            />
                                                            <button
                                                                type="button"
                                                                @click="generateSku(index)"
                                                                class="px-3 py-2 bg-gray-200 border border-l-0 border-gray-300 rounded-r-lg hover:bg-gray-300"
                                                                title="Generate SKU"
                                                            >
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Enhanced Barcode Section -->
                                                    <div>
                                                        <div class="flex justify-between items-center mb-1">
                                                            <label class="block text-sm font-medium text-gray-700">Barcode</label>
                                                            <div class="flex space-x-2">
                                                                <button
                                                                    type="button"
                                                                    @click="toggleBarcodeScanner(index)"
                                                                    class="text-xs text-blue-600 hover:text-blue-800 flex items-center"
                                                                >
                                                                    {{ item.showBarcodeScanner ? 'Manual Entry' : 'Scan' }}
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    @click="openBarcodeOptions(index)"
                                                                    class="text-xs text-gray-600 hover:text-gray-800 flex items-center"
                                                                    title="More barcode options"
                                                                >
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Barcode Input Area -->
                                                        <div class="relative">
                                                            <!-- Manual Input -->
                                                            <div v-if="!item.showBarcodeScanner">
                                                                <div class="flex">
                                                                    <input
                                                                        type="text"
                                                                        v-model="item.barcode"
                                                                        placeholder="Click to enter barcode"
                                                                        class="flex-1 border-gray-300 rounded-l-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                                        @focus="openManualBarcodeEntry(index)"
                                                                        readonly
                                                                        :ref="'barcodeInput_' + index"
                                                                    />
                                                                    <div class="flex">
                                                                        <button
                                                                            type="button"
                                                                            @click="openManualBarcodeEntry(index)"
                                                                            class="px-3 py-2 bg-blue-100 border border-gray-300 text-blue-600 hover:bg-blue-200"
                                                                            title="Enter barcode manually"
                                                                        >
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                            </svg>
                                                                        </button>
                                                                        <button
                                                                            type="button"
                                                                            @click="generateBarcode(index)"
                                                                            class="px-3 py-2 bg-gray-200 border border-l-0 border-gray-300 rounded-r-lg hover:bg-gray-300"
                                                                            title="Generate barcode"
                                                                        >
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Barcode Scanner for this item -->
                                                            <div v-else class="space-y-2">
                                                                <div class="relative">
                                                                    <div :id="'item-scanner-container-' + index" class="bg-gray-100 rounded-lg h-32 flex items-center justify-center">
                                                                        <div v-if="!item.scannerActive" class="text-center">
                                                                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1v-2a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                                                            </svg>
                                                                            <button
                                                                                @click="startItemScanner(index)"
                                                                                class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"
                                                                            >
                                                                                Start Scanner
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                    <button
                                                                        v-if="item.scannerActive"
                                                                        @click="stopItemScanner(index)"
                                                                        class="absolute top-2 right-2 p-2 bg-red-500 text-white rounded-full hover:bg-red-600"
                                                                    >
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                                
                                                                <!-- Manual entry fallback in scanner mode -->
                                                                <div class="pt-2 border-t border-gray-200">
                                                                    <button
                                                                        @click="openManualBarcodeEntry(index)"
                                                                        class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-sm flex items-center justify-center"
                                                                    >
                                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                        </svg>
                                                                        Enter barcode manually
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Current barcode preview -->
                                                            <div v-if="item.barcode" class="mt-2 flex items-center justify-between">
                                                                <div class="text-sm text-gray-600">
                                                                    <span class="font-medium">Current:</span>
                                                                    <span class="ml-2 font-mono text-blue-600">{{ item.barcode }}</span>
                                                                </div>
                                                                <button
                                                                    @click="copyToClipboard(item.barcode)"
                                                                    class="text-xs text-blue-600 hover:text-blue-800"
                                                                >
                                                                    Copy
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Category & Unit -->
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                                        <select
                                                            v-model="item.category_id"
                                                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                        >
                                                            <option value="">Select Category</option>
                                                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                                                {{ category.name }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                                                        <input
                                                            type="text"
                                                            v-model="item.unit"
                                                            placeholder="pcs, kg, liter"
                                                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                        />
                                                    </div>
                                                </div>
                                                
                                                <!-- Barcode Preview -->
                                                <div v-if="item.barcode" class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                                    <div class="flex justify-between items-center mb-2">
                                                        <div>
                                                            <span class="text-sm font-medium text-gray-700">Barcode:</span>
                                                            <span class="ml-2 font-mono text-sm text-blue-600">{{ item.barcode }}</span>
                                                        </div>
                                                        <button
                                                            type="button"
                                                            @click="copyToClipboard(item.barcode)"
                                                            class="text-xs text-blue-600 hover:text-blue-800 flex items-center"
                                                        >
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                            </svg>
                                                            Copy
                                                        </button>
                                                    </div>
                                                    <!-- Barcode visual -->
                                                    <div class="mt-2 text-center">
                                                        <div class="font-mono text-xs tracking-widest inline-block bg-white px-4 py-2 rounded border">
                                                            {{ formatBarcodeVisual(item.barcode) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Quantity -->
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-2">
                                                <input
                                                    type="number"
                                                    v-model="item.quantity"
                                                    min="0.01"
                                                    step="0.01"
                                                    class="w-24 border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center"
                                                    @input="calculateItemTotal(index)"
                                                    required
                                                />
                                                <span class="text-sm text-gray-500">{{ item.unit || 'pcs' }}</span>
                                            </div>
                                        </td>

                                        <!-- Cost Price -->
                                        <td class="px-6 py-4">
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">₵</span>
                                                </div>
                                                <input
                                                    type="number"
                                                    v-model="item.unit_cost"
                                                    min="0"
                                                    step="0.01"
                                                    class="pl-7 w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    @input="calculateItemTotal(index)"
                                                    required
                                                />
                                            </div>
                                        </td>

                                        <!-- Selling Price -->
                                        <td class="px-6 py-4">
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">₵</span>
                                                </div>
                                                <input
                                                    type="number"
                                                    v-model="item.selling_price"
                                                    min="0"
                                                    step="0.01"
                                                    class="pl-7 w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    :placeholder="item.type === 'new' ? 'Auto: cost × 1.3' : ''"
                                                />
                                            </div>
                                            <div v-if="item.unit_cost > 0 && item.selling_price > 0" class="mt-1">
                                                <span class="text-xs" :class="calculateMargin(item.unit_cost, item.selling_price) >= 30 ? 'text-green-600' : 'text-yellow-600'">
                                                    {{ calculateMargin(item.unit_cost, item.selling_price) }}% margin
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Total Cost -->
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-lg text-blue-600">
                                                ₵{{ calculateItemTotal(index).toFixed(2) }}
                                            </div>
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-6 py-4">
                                            <button
                                                type="button"
                                                @click="removeItem(index)"
                                                class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-50"
                                                :disabled="form.items.length <= 1"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                
                                <!-- Summary Footer -->
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-right font-medium text-gray-700">
                                            <div class="flex justify-between items-center">
                                                <div class="text-left">
                                                    <div class="text-sm text-gray-600">
                                                        {{ form.items.filter(i => i.type === 'new').length }} new product(s)
                                                    </div>
                                                    <div class="text-sm text-gray-600">
                                                        {{ form.items.filter(i => i.type === 'existing').length }} existing product(s)
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-lg font-bold text-gray-900">
                                                        Total Value: ₵{{ calculateGrandTotal().toFixed(2) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-2xl font-bold text-green-600">
                                                ₵{{ calculateGrandTotal().toFixed(2) }}
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Additional Information & Actions -->
                    <div class="p-6 bg-gray-50 border-t border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Number</label>
                                <input
                                    type="text"
                                    v-model="form.invoice_number"
                                    placeholder="Supplier invoice number"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                                <select
                                    v-model="form.payment_method"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">Select Method</option>
                                    <option v-for="(method, key) in paymentMethods" :key="key" :value="key">
                                        {{ method }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                placeholder="Any additional notes about this receiving..."
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            ></textarea>
                        </div>
                        
                        <!-- Submit Actions -->
                        <div class="mt-8 flex justify-end space-x-4">
                            <Link
                                :href="route('inventory.transactions.index')"
                                class="px-8 py-3 bg-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500"
                            >
                                Cancel
                            </Link>
                            <button
                                type="button"
                                @click="submitForm"
                                :disabled="isSubmitting || !isFormValid"
                                class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                            >
                                <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span v-if="isSubmitting">Processing...</span>
                                <span v-else>Receive {{ form.items.length }} Item(s)</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL FOR MANUAL BARCODE ENTRY -->
        <div v-if="showBarcodeModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <!-- Modal header -->
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1v-2a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                <h3 class="text-lg font-semibold text-white">Enter Barcode</h3>
                            </div>
                            <button
                                @click="closeBarcodeModal"
                                class="text-white hover:text-gray-200"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal content -->
                    <div class="px-6 py-8">
                        <div class="space-y-6">
                            <!-- Current product name -->
                            <div v-if="currentBarcodeItem" class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-600 mb-1">Product:</div>
                                <div class="font-medium text-gray-900">
                                    {{ currentBarcodeItem.name || 'New Product' }}
                                </div>
                            </div>

                            <!-- Barcode input -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Enter barcode number
                                </label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        v-model="manualBarcodeInput"
                                        placeholder="Type or scan barcode here"
                                        class="block w-full px-4 py-4 text-xl border-2 border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center font-mono tracking-widest"
                                        @keyup.enter="saveManualBarcode"
                                        ref="barcodeModalInput"
                                        autofocus
                                    />
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <button
                                            @click="pasteBarcode"
                                            class="text-blue-600 hover:text-blue-800"
                                            title="Paste from clipboard"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">
                                    Press Enter to save, or click Save button below
                                </p>
                            </div>

                            <!-- Quick barcode options -->
                            <div class="border-t border-gray-200 pt-6">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">Quick Options</h4>
                                <div class="grid grid-cols-2 gap-3">
                                    <button
                                        @click="generateEAN13"
                                        class="p-4 bg-gray-100 hover:bg-gray-200 rounded-lg text-center"
                                    >
                                        <div class="font-mono text-sm">EAN-13</div>
                                        <div class="text-xs text-gray-600 mt-1">Generate EAN-13</div>
                                    </button>
                                    <button
                                        @click="generateUPC"
                                        class="p-4 bg-gray-100 hover:bg-gray-200 rounded-lg text-center"
                                    >
                                        <div class="font-mono text-sm">UPC-A</div>
                                        <div class="text-xs text-gray-600 mt-1">Generate UPC-A</div>
                                    </button>
                                    <button
                                        @click="generateCode128"
                                        class="p-4 bg-gray-100 hover:bg-gray-200 rounded-lg text-center"
                                    >
                                        <div class="font-mono text-sm">Code 128</div>
                                        <div class="text-xs text-gray-600 mt-1">Generate Code 128</div>
                                    </button>
                                    <button
                                        @click="generateRandomBarcode"
                                        class="p-4 bg-gray-100 hover:bg-gray-200 rounded-lg text-center"
                                    >
                                        <div class="font-mono text-sm">Random</div>
                                        <div class="text-xs text-gray-600 mt-1">Generate random</div>
                                    </button>
                                </div>
                            </div>

                            <!-- Barcode preview -->
                            <div v-if="manualBarcodeInput" class="border-t border-gray-200 pt-6">
                                <div class="flex justify-between items-center mb-3">
                                    <h4 class="text-sm font-medium text-gray-700">Preview</h4>
                                    <div class="flex space-x-2">
                                        <button
                                            @click="copyToClipboard(manualBarcodeInput)"
                                            class="px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded hover:bg-blue-200"
                                        >
                                            Copy
                                        </button>
                                        <button
                                            @click="printBarcode(manualBarcodeInput)"
                                            class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200"
                                        >
                                            Print
                                        </button>
                                    </div>
                                </div>
                                <div class="bg-white p-6 border border-gray-200 rounded-lg">
                                    <!-- Simple barcode visual representation -->
                                    <div class="font-mono text-center text-sm tracking-widest mb-2">
                                        {{ formatBarcodeVisual(manualBarcodeInput) }}
                                    </div>
                                    <div class="text-center text-gray-500 text-sm">
                                        {{ manualBarcodeInput.length }} characters
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div class="bg-gray-50 px-6 py-4 flex justify-between">
                        <button
                            @click="closeBarcodeModal"
                            type="button"
                            class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
                        >
                            Cancel
                        </button>
                        <div class="flex space-x-3">
                            <button
                                @click="clearBarcode"
                                type="button"
                                class="px-6 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200"
                            >
                                Clear
                            </button>
                            <button
                                @click="saveManualBarcode"
                                type="button"
                                class="px-8 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            >
                                Save Barcode
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL FOR BARCODE OPTIONS -->
        <div v-if="showBarcodeOptionsModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <div class="bg-white px-6 py-8">
                        <div class="space-y-6">
                            <div class="text-center">
                                <svg class="w-12 h-12 text-blue-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1v-2a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Barcode Options</h3>
                                <p class="text-sm text-gray-600">Choose how to enter barcode</p>
                            </div>

                            <div class="space-y-3">
                                <button
                                    @click="openManualBarcodeEntry(currentBarcodeOptionsIndex)"
                                    class="w-full p-4 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg text-left flex items-center"
                                >
                                    <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    <div>
                                        <div class="font-medium text-gray-900">Manual Entry</div>
                                        <div class="text-sm text-gray-600">Type barcode manually</div>
                                    </div>
                                </button>

                                <button
                                    @click="toggleBarcodeScanner(currentBarcodeOptionsIndex)"
                                    class="w-full p-4 bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg text-left flex items-center"
                                >
                                    <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <div>
                                        <div class="font-medium text-gray-900">Camera Scanner</div>
                                        <div class="text-sm text-gray-600">Use camera to scan barcode</div>
                                    </div>
                                </button>

                                <button
                                    @click="generateBarcode(currentBarcodeOptionsIndex)"
                                    class="w-full p-4 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg text-left flex items-center"
                                >
                                    <svg class="w-6 h-6 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    <div>
                                        <div class="font-medium text-gray-900">Auto-generate</div>
                                        <div class="text-sm text-gray-600">Generate random barcode</div>
                                    </div>
                                </button>

                                <button
                                    v-if="currentBarcodeOptionsIndex !== null && form.items[currentBarcodeOptionsIndex]?.barcode"
                                    @click="viewBarcode(currentBarcodeOptionsIndex)"
                                    class="w-full p-4 bg-yellow-50 hover:bg-yellow-100 border border-yellow-200 rounded-lg text-left flex items-center"
                                >
                                    <svg class="w-6 h-6 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <div>
                                        <div class="font-medium text-gray-900">View Current</div>
                                        <div class="text-sm text-gray-600 truncate">{{ form.items[currentBarcodeOptionsIndex].barcode }}</div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4">
                        <button
                            @click="closeBarcodeOptionsModal"
                            type="button"
                            class="w-full px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'
import Quagga from '@ericblade/quagga2'

const props = defineProps({
    warehouses: Array,
    suppliers: Array,
    categories: Array,
    existingProducts: Array,
    paymentMethods: Object,
})

// Form state
const form = reactive({
    warehouse_id: '',
    supplier_id: '',
    reference_number: 'PUR-' + new Date().toISOString().slice(0, 10).replace(/-/g, '') + '-001',
    invoice_number: '',
    payment_method: '',
    notes: '',
    items: [
        {
            type: 'existing',
            product_id: '',
            quantity: 1,
            unit_cost: 0,
            selling_price: 0,
            barcode: '',
            sku: '',
            name: '',
            category_id: '',
            unit: 'pcs',
            description: '',
            showBarcodeScanner: false,
            scannerActive: false,
        }
    ],
})

// UI state
const selectedProductDetails = ref({})
const selectedWarehouse = ref(null)
const isSubmitting = ref(false)
const isScannerActive = ref(false)
const scannerActive = ref(false)
const quickAddInput = ref('')
const quickAddError = ref('')
const recentScans = ref([])

// Barcode modal state
const showBarcodeModal = ref(false)
const showBarcodeOptionsModal = ref(false)
const currentBarcodeItemIndex = ref(null)
const currentBarcodeOptionsIndex = ref(null)
const currentBarcodeItem = ref(null)
const manualBarcodeInput = ref('')
const barcodeModalInput = ref(null)

// Computed properties
const filteredProducts = computed(() => {
    return props.existingProducts.filter(product => {
        return product.name.toLowerCase().includes(quickAddInput.value.toLowerCase()) ||
               product.sku.toLowerCase().includes(quickAddInput.value.toLowerCase()) ||
               product.barcode?.toLowerCase().includes(quickAddInput.value.toLowerCase())
    }).slice(0, 50)
})

const isFormValid = computed(() => {
    if (!form.warehouse_id) return false
    
    return form.items.every(item => {
        // Basic validation
        if (item.quantity <= 0 || item.unit_cost <= 0) return false
        
        // Type-specific validation
        if (item.type === 'existing') {
            return !!item.product_id
        } else {
            return !!item.name && item.name.trim() !== ''
        }
    })
})

// Methods
const addItem = () => {
    form.items.push({
        type: 'existing',
        product_id: '',
        quantity: 1,
        unit_cost: 0,
        selling_price: 0,
        barcode: '',
        sku: '',
        name: '',
        category_id: '',
        unit: 'pcs',
        description: '',
        showBarcodeScanner: false,
        scannerActive: false,
    })
}

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1)
    }
}

const handleItemTypeChange = (index) => {
    const item = form.items[index]
    
    if (item.type === 'new') {
        // Auto-generate SKU
        if (!item.sku) {
            generateSku(index)
        }
        // Auto-generate barcode if empty
        if (!item.barcode) {
            generateBarcode(index)
        }
    } else {
        // Clear new product fields
        item.name = ''
        item.sku = ''
        item.barcode = ''
        item.category_id = ''
        item.unit = 'pcs'
        item.description = ''
    }
}

const handleProductSelect = async (index) => {
    const item = form.items[index]
    if (!item.product_id) return
    
    const product = props.existingProducts.find(p => p.id == item.product_id)
    if (product) {
        selectedProductDetails.value[index] = {
            stock_quantity: product.stock_quantity,
            cost_price: product.cost_price,
            selling_price: product.selling_price,
            unit: product.unit
        }
        
        // Auto-fill data
        item.unit_cost = product.cost_price
        item.selling_price = product.selling_price
        item.unit = product.unit || 'pcs'
        calculateItemTotal(index)
    }
}

const generateSku = (index) => {
    const prefix = 'PROD'
    const random = Math.random().toString(36).substr(2, 6).toUpperCase()
    form.items[index].sku = `${prefix}-${random}`
}

const generateBarcode = (index) => {
    const timestamp = Date.now().toString()
    const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0')
    form.items[index].barcode = `BAR${timestamp.substr(-6)}${random}`
}

const generateBarcodeIfEmpty = (index) => {
    if (!form.items[index].barcode) {
        generateBarcode(index)
    }
}

const toggleBarcodeScanner = (index) => {
    form.items[index].showBarcodeScanner = !form.items[index].showBarcodeScanner
    if (!form.items[index].showBarcodeScanner) {
        form.items[index].scannerActive = false
    }
    showBarcodeOptionsModal.value = false
}

const formatBarcodeVisual = (barcode) => {
    if (!barcode) return ''
    // Add spaces every 4 characters for readability
    return barcode.replace(/(.{4})/g, '$1 ').trim()
}

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!')
    })
}

const calculateItemTotal = (index) => {
    const item = form.items[index]
    const quantity = parseFloat(item.quantity) || 0
    const cost = parseFloat(item.unit_cost) || 0
    
    // Auto-calculate selling price for new products (30% markup)
    if (item.type === 'new' && cost > 0 && !item.selling_price) {
        item.selling_price = (cost * 1.3).toFixed(2)
    }
    
    return quantity * cost
}

const calculateMargin = (cost, selling) => {
    if (!cost || !selling || cost <= 0) return 0
    return (((selling - cost) / cost) * 100).toFixed(1)
}

const calculateGrandTotal = () => {
    return form.items.reduce((total, item, index) => {
        return total + calculateItemTotal(index)
    }, 0)
}

const generateReferenceNumber = () => {
    const timestamp = Date.now().toString().substr(-6)
    const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0')
    form.reference_number = `PUR-${new Date().toISOString().slice(0, 10).replace(/-/g, '')}-${timestamp}${random}`
}

const onWarehouseChange = () => {
    selectedWarehouse.value = props.warehouses.find(w => w.id == form.warehouse_id)
}

// Quick add functionality
const handleQuickAdd = async () => {
    if (!quickAddInput.value.trim()) {
        quickAddError.value = 'Please enter a barcode or SKU'
        return
    }
    
    // Search for existing product
    const existingProduct = props.existingProducts.find(p => 
        p.barcode === quickAddInput.value || p.sku === quickAddInput.value
    )
    
    if (existingProduct) {
        // Add as existing product
        form.items.push({
            type: 'existing',
            product_id: existingProduct.id,
            quantity: 1,
            unit_cost: existingProduct.cost_price,
            selling_price: existingProduct.selling_price,
            barcode: existingProduct.barcode,
            sku: existingProduct.sku,
            name: existingProduct.name,
            category_id: existingProduct.category_id,
            unit: existingProduct.unit || 'pcs',
            description: '',
            showBarcodeScanner: false,
            scannerActive: false,
        })
        
        // Add to recent scans
        addToRecentScans(quickAddInput.value)
        quickAddInput.value = ''
        quickAddError.value = ''
    } else {
        // Add as new product
        form.items.push({
            type: 'new',
            product_id: null,
            quantity: 1,
            unit_cost: 0,
            selling_price: 0,
            barcode: quickAddInput.value,
            sku: 'PROD-' + quickAddInput.value.toUpperCase().substr(0, 8),
            name: 'Product ' + quickAddInput.value,
            category_id: '',
            unit: 'pcs',
            description: '',
            showBarcodeScanner: false,
            scannerActive: false,
        })
        
        // Add to recent scans
        addToRecentScans(quickAddInput.value)
        quickAddInput.value = ''
        quickAddError.value = ''
    }
}

const addToRecentScans = (barcode) => {
    recentScans.value = [barcode, ...recentScans.value.filter(b => b !== barcode)].slice(0, 10)
}

const useScannedBarcode = (barcode) => {
    quickAddInput.value = barcode
    handleQuickAdd()
}

// Barcode modal methods
const openBarcodeOptions = (index) => {
    currentBarcodeOptionsIndex.value = index
    showBarcodeOptionsModal.value = true
}

const closeBarcodeOptionsModal = () => {
    showBarcodeOptionsModal.value = false
    currentBarcodeOptionsIndex.value = null
}

const openManualBarcodeEntry = (index) => {
    currentBarcodeItemIndex.value = index
    currentBarcodeItem.value = form.items[index]
    manualBarcodeInput.value = form.items[index].barcode || ''
    showBarcodeModal.value = true
    showBarcodeOptionsModal.value = false
    
    // Focus on input after modal opens
    nextTick(() => {
        if (barcodeModalInput.value) {
            barcodeModalInput.value.focus()
            barcodeModalInput.value.select()
        }
    })
}

const closeBarcodeModal = () => {
    showBarcodeModal.value = false
    currentBarcodeItemIndex.value = null
    currentBarcodeItem.value = null
    manualBarcodeInput.value = ''
}

const saveManualBarcode = () => {
    if (currentBarcodeItemIndex.value !== null && manualBarcodeInput.value.trim()) {
        form.items[currentBarcodeItemIndex.value].barcode = manualBarcodeInput.value.trim()
    }
    closeBarcodeModal()
}

const clearBarcode = () => {
    manualBarcodeInput.value = ''
    if (barcodeModalInput.value) {
        barcodeModalInput.value.focus()
    }
}

const pasteBarcode = async () => {
    try {
        const text = await navigator.clipboard.readText()
        manualBarcodeInput.value = text.trim()
        
        // Auto-save if it looks like a valid barcode
        if (text.length >= 8 && text.length <= 13 && /^\d+$/.test(text)) {
            setTimeout(() => {
                saveManualBarcode()
            }, 500)
        }
    } catch (err) {
        console.error('Failed to paste:', err)
    }
}

const generateEAN13 = () => {
    // Generate valid EAN-13 barcode
    let barcode = '8' + Math.floor(Math.random() * 100000000000).toString().padStart(11, '0')
    
    // Calculate check digit
    let sum = 0
    for (let i = 0; i < 12; i++) {
        sum += parseInt(barcode.charAt(i)) * (i % 2 === 0 ? 1 : 3)
    }
    const checkDigit = (10 - (sum % 10)) % 10
    manualBarcodeInput.value = barcode + checkDigit
}

const generateUPC = () => {
    // Generate valid UPC-A barcode
    let barcode = Math.floor(Math.random() * 10000000000).toString().padStart(10, '0')
    
    // Calculate check digit
    let sum = 0
    for (let i = 0; i < 10; i++) {
        sum += parseInt(barcode.charAt(i)) * (i % 2 === 0 ? 3 : 1)
    }
    const checkDigit = (10 - (sum % 10)) % 10
    manualBarcodeInput.value = '0' + barcode + checkDigit
}

const generateCode128 = () => {
    // Generate Code 128 style barcode (alphanumeric)
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
    let barcode = 'C128'
    for (let i = 0; i < 8; i++) {
        barcode += chars.charAt(Math.floor(Math.random() * chars.length))
    }
    manualBarcodeInput.value = barcode
}

const generateRandomBarcode = () => {
    // Generate a simple random barcode
    const timestamp = Date.now().toString().substr(-8)
    const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0')
    manualBarcodeInput.value = `BR${timestamp}${random}`
}

const printBarcode = (barcode) => {
    const printWindow = window.open('', '_blank')
    printWindow.document.write(`
        <html>
            <head>
                <title>Print Barcode</title>
                <style>
                    body { font-family: Arial, sans-serif; text-align: center; padding: 40px; }
                    .barcode { font-family: 'Libre Barcode 128', monospace; font-size: 48px; margin: 20px 0; }
                    .numbers { font-family: monospace; font-size: 16px; letter-spacing: 8px; }
                </style>
                <script>
                    window.onload = function() { window.print(); }
                <\/script>
            </head>
            <body>
                <h2>Barcode Label</h2>
                <div class="barcode">${barcode}</div>
                <div class="numbers">${barcode}</div>
                <p>Scan this barcode to verify</p>
            </body>
        </html>
    `)
    printWindow.document.close()
}
const viewBarcode = (index) => {
    const barcode = form.items[index]?.barcode
    if (barcode) {
        alert(`Current barcode: ${barcode}\n\nClick OK to copy to clipboard`)
        copyToClipboard(barcode)
    }
    closeBarcodeOptionsModal()
}

// Barcode scanner methods
const toggleScanner = () => {
    if (isScannerActive.value) {
        stopScanner()
    } else {
        isScannerActive.value = true
        setTimeout(() => {
            startScanner()
        }, 100)
    }
}

const startScanner = () => {
    if (scannerActive.value) return
    
    Quagga.init({
        inputStream: {
            name: "Live",
            type: "LiveStream",
            target: document.querySelector('#scanner-container'),
            constraints: {
                width: 640,
                height: 480,
                facingMode: "environment"
            },
        },
        decoder: {
            readers: ["code_128_reader", "ean_reader", "ean_8_reader", "code_39_reader", "upc_reader"]
        }
    }, function(err) {
        if (err) {
            console.error(err)
            quickAddError.value = 'Failed to start scanner: ' + err.message
            return
        }
        scannerActive.value = true
        Quagga.start()
    })
    
    Quagga.onDetected(function(result) {
        const code = result.codeResult.code
        quickAddInput.value = code
        handleQuickAdd()
        
        // Visual feedback
        const scannerContainer = document.querySelector('#scanner-container')
        scannerContainer.classList.add('ring-4', 'ring-green-500')
        setTimeout(() => {
            scannerContainer.classList.remove('ring-4', 'ring-green-500')
        }, 1000)
    })
}

const stopScanner = () => {
    if (scannerActive.value) {
        Quagga.stop()
        scannerActive.value = false
    }
    isScannerActive.value = false
}

// Item scanner methods
const startItemScanner = (index) => {
    const containerId = `item-scanner-container-${index}`
    const container = document.getElementById(containerId)
    
    if (!container) return
    
    Quagga.init({
        inputStream: {
            name: "Live",
            type: "LiveStream",
            target: container,
            constraints: {
                width: 320,
                height: 240,
                facingMode: "environment"
            },
        },
        decoder: {
            readers: ["code_128_reader", "ean_reader", "ean_8_reader", "code_39_reader", "upc_reader"]
        }
    }, function(err) {
        if (err) {
            console.error(err)
            return
        }
        form.items[index].scannerActive = true
        Quagga.start()
    })
    
    Quagga.onDetected(function(result) {
        const code = result.codeResult.code
        form.items[index].barcode = code
        form.items[index].showBarcodeScanner = false
        form.items[index].scannerActive = false
        Quagga.stop()
        
        // Visual feedback
        container.classList.add('ring-4', 'ring-green-500')
        setTimeout(() => {
            container.classList.remove('ring-4', 'ring-green-500')
        }, 1000)
    })
}

const stopItemScanner = (index) => {
    if (form.items[index].scannerActive) {
        Quagga.stop()
        form.items[index].scannerActive = false
    }
}

// Form submission
const submitForm = async () => {
    if (!isFormValid.value) {
        alert('Please fill in all required fields correctly')
        return
    }
    
    isSubmitting.value = true
    
    try {
        const response = await axios.post(route('inventory.receive.store'), form)
        
        if (response.data.success) {
            alert(response.data.message)
            router.visit(route('inventory.transactions.index'))
        } else {
            alert('Error: ' + response.data.message)
        }
    } catch (error) {
        alert('Error submitting form: ' + (error.response?.data?.message || error.message))
    } finally {
        isSubmitting.value = false
    }
}

// Initialize
onMounted(() => {
    generateReferenceNumber()
    
    // Focus on quick add input
    setTimeout(() => {
        const input = document.querySelector('input[placeholder="Scan barcode or enter SKU"]')
        if (input) input.focus()
    }, 100)
})

// Cleanup
onUnmounted(() => {
    // Stop all item scanners
    form.items.forEach((item, index) => {
        if (item.scannerActive) {
            stopItemScanner(index)
        }
    })
    
    // Stop main scanner
    if (scannerActive.value) {
        Quagga.stop()
    }
})
</script>

<style scoped>
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}
</style>