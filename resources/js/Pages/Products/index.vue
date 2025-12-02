<template>
    <Head title="Products" />
    
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Products Management</h2>
                    <p class="mt-1 text-sm text-gray-600">Manage your product catalog and inventory</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('categories.index')" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Categories
                    </Link>
                    <PrimaryButton @click="showCreateModal = true">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Product
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters and Search -->
                <div class="mb-6 bg-white shadow-sm rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Search products..."
                                class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            />
                        </div>
                        
                        <select v-model="categoryFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Categories</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                        
                        <select v-model="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="low_stock">Low Stock</option>
                            <option value="out_of_stock">Out of Stock</option>
                        </select>
                        
                        <button @click="clearFilters" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200">
                            Clear Filters
                        </button>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Product
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Category
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Pricing
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Stock
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="product in filteredProducts" :key="product.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0 bg-gray-200 rounded-lg flex items-center justify-center">
                                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ product.name }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    SKU: {{ product.sku }}
                                                </div>
                                                <div v-if="product.barcode" class="text-xs text-gray-500">
                                                    Barcode: {{ product.barcode }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="product.category" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ product.category.name }}
                                        </span>
                                        <span v-else class="text-xs text-gray-400 italic">Uncategorized</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $currency(product.selling_price) }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Cost: {{ $currency(product.cost_price) }}
                                        </div>
                                        <div class="text-xs" :class="$calculateMargin(product.cost_price, product.selling_price) >= 50 ? 'text-green-600' : 'text-yellow-600'">
                                            Margin: {{ $calculateMargin(product.cost_price, product.selling_price).toFixed(1) }}%
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ product.stock_quantity }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Low Stock: {{ product.low_stock_threshold }}
                                        </div>
                                        <div v-if="product.stock_quantity <= product.low_stock_threshold" 
                                             class="text-xs text-red-600 font-medium">
                                            Low Stock!
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="product.is_active" 
                                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                        <span v-else 
                                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Inactive
                                        </span>
                                        <div class="mt-1 text-xs text-gray-500">
                                            <span v-if="product.is_taxable" class="text-blue-600">Taxable</span>
                                            <span v-else class="text-gray-400">Non-taxable</span>
                                        </div>
                                    </td>
                                 <td class="px-6 py-4 whitespace-nowrap">
    <div class="flex space-x-2">
                                        <button @click="editProduct(product)" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200">    
                                            
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
                                            
                                            Edit
                                        </button>
                                        <button @click="adjustStock(product)"  class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded bg-green-50 text-green-700 hover:bg-green-100 border border-green-200">    
                                            
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>  Adjust Stock
                                        </button>
                                        <button @click="confirmDelete(product)"   class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded bg-red-50 text-red-700 hover:bg-red-100 border border-red-200"
        >
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>   Delete
                                        </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200" v-if="products.data && products.data.length > 0">
                        <Pagination :links="products.links" />
                    </div>

                    <!-- Empty State -->
                    <div v-if="products.data && products.data.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No products</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating your first product.</p>
                        <div class="mt-6">
                            <PrimaryButton @click="showCreateModal = true">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                New Product
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Product Modal -->
        <Modal :show="showCreateModal || showEditModal" @close="closeModal" maxWidth="4xl">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ editingProduct ? 'Edit Product' : 'Create New Product' }}
                </h3>
                
                <form @submit.prevent="editingProduct ? updateProduct() : createProduct()">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <div>
                                <InputLabel for="name" value="Product Name *" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    v-model="form.name"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div>
                                <InputLabel for="description" value="Description" />
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <div>
                                <InputLabel for="category_id" value="Category" />
                                <select
                                    id="category_id"
                                    v-model="form.category_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                >
                                    <option value="">Select Category</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.category_id" />
                            </div>

                      <div class="grid grid-cols-1 gap-4">
    <div>
        <div class="flex justify-between items-center mb-1">
            <InputLabel for="barcode" value="Barcode" />
            <button 
                type="button"
                @click="toggleScanner"
                class="text-xs text-blue-600 hover:text-blue-800 flex items-center"
            >
                <svg v-if="!showScanner" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1v-2a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <svg v-else class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                {{ showScanner ? 'Switch to Manual Entry' : 'Use Barcode Scanner' }}
            </button>
        </div>
        
        <!-- Manual Input -->
        <div v-if="!showScanner">
            <div class="flex">
                <TextInput
                    id="barcode"
                    type="text"
                    v-model="form.barcode"
                    class="mt-1 block w-full rounded-r-none"
                    placeholder="Enter barcode manually"
                    ref="barcodeInput"
                    @keyup.enter="generateBarcodeIfEmpty"
                />
                <button 
                    type="button"
                    @click="generateBarcode"
                    class="mt-1 inline-flex items-center px-4 py-2 bg-gray-200 border border-l-0 border-gray-300 rounded-r-md text-sm font-medium text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    title="Generate Barcode"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </button>
            </div>
            <InputError class="mt-2" :message="form.errors.barcode" />
            <p class="mt-1 text-xs text-gray-500">
                Press Enter to auto-generate or click generate button
            </p>
        </div>
        
        <!-- Scanner Interface -->
        <div v-else class="space-y-4">
            <!-- Scanner Video Feed Placeholder -->
            <div class="relative bg-gray-100 rounded-lg p-4 text-center" style="min-height: 150px;">
                <div v-if="!scannerActive" class="flex flex-col items-center justify-center h-full">
                    <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1v-2a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    <p class="text-sm text-gray-600 mb-3">Barcode Scanner Ready</p>
                    <button 
                        type="button"
                        @click="startScanner"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        Start Scanner
                    </button>
                </div>
                <div v-else class="flex flex-col items-center justify-center h-full">
                    <div class="relative w-full max-w-md mx-auto">
                        <!-- Scanner video will be inserted here -->
                        <div id="scanner-container" class="bg-black rounded-lg overflow-hidden"></div>
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-green-500 to-transparent animate-pulse"></div>
                    </div>
                    <p class="mt-3 text-sm text-green-600">Scanning... Point camera at barcode</p>
                    <button 
                        type="button"
                        @click="stopScanner"
                        class="mt-3 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                    >
                        Stop Scanner
                    </button>
                </div>
            </div>
            
            <!-- Recent Scans -->
            <div v-if="recentScans.length > 0">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Recent Scans</h4>
                <div class="space-y-2">
                    <button 
                        v-for="(scan, index) in recentScans.slice(0, 3)" 
                        :key="index"
                        @click="useScannedBarcode(scan)"
                        class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200"
                    >
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ scan }}</span>
                                <div class="text-xs text-gray-500">Click to use</div>
                            </div>
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </button>
                </div>
            </div>
            
            <!-- Manual Fallback in Scanner Mode -->
            <div class="pt-4 border-t border-gray-200">
                <InputLabel for="manual-barcode-scanner" value="Or Enter Manually" />
                <div class="flex mt-1">
                    <input
                        id="manual-barcode-scanner"
                        type="text"
                        v-model="manualBarcodeInput"
                        @keyup.enter="addManualBarcode"
                        class="block w-full rounded-l-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        placeholder="Type barcode and press Enter"
                        ref="scannerManualInput"
                    />
                    <button 
                        type="button"
                        @click="addManualBarcode"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-r-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        Add
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Auto-generated Barcode Preview -->
    <div v-if="form.barcode" class="bg-gray-50 p-3 rounded-lg">
        <div class="flex justify-between items-center">
            <div>
                <span class="text-sm font-medium text-gray-700">Barcode:</span>
                <span class="ml-2 font-mono text-sm text-blue-600">{{ form.barcode }}</span>
            </div>
            <button 
                type="button"
                @click="copyBarcode"
                class="text-xs text-blue-600 hover:text-blue-800 flex items-center"
            >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                Copy
            </button>
        </div>
        <!-- Barcode visual representation (simple version) -->
        <div class="mt-2 flex items-center justify-center">
            <div class="bg-white p-4 rounded border">
                <div class="font-mono text-xs tracking-widest text-center">{{ formatBarcodeVisual(form.barcode) }}</div>
                <div class="mt-1 text-center text-xs text-gray-500">Visual representation</div>
            </div>
        </div>
    </div>
</div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="unit" value="Unit" />
                                    <TextInput
                                        id="unit"
                                        type="text"
                                        v-model="form.unit"
                                        class="mt-1 block w-full"
                                        placeholder="e.g., pcs, kg, liter"
                                    />
                                    <InputError class="mt-2" :message="form.errors.unit" />
                                </div>

                                <div>
                                    <InputLabel for="weight" value="Weight (kg)" />
                                    <TextInput
                                        id="weight"
                                        type="number"
                                        step="0.01"
                                        v-model="form.weight"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError class="mt-2" :message="form.errors.weight" />
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="cost_price" value="Cost Price *" />
                                    <div class="relative mt-1 rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">₵</span>
                                        </div>
                                        <input
                                            id="cost_price"
                                            type="number"
                                            step="0.01"
                                            v-model="form.cost_price"
                                            class="block w-full pl-7 pr-12 border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                            required
                                            min="0"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.cost_price" />
                                </div>

                                <div>
                                    <InputLabel for="selling_price" value="Selling Price *" />
                                    <div class="relative mt-1 rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">₵</span>
                                        </div>
                                        <input
                                            id="selling_price"
                                            type="number"
                                            step="0.01"
                                            v-model="form.selling_price"
                                            class="block w-full pl-7 pr-12 border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                            required
                                            min="0"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.selling_price" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="stock_quantity" value="Stock Quantity *" />
                                    <TextInput
                                        id="stock_quantity"
                                        type="number"
                                        v-model="form.stock_quantity"
                                        class="mt-1 block w-full"
                                        required
                                        min="0"
                                    />
                                    <InputError class="mt-2" :message="form.errors.stock_quantity" />
                                </div>

                                <div>
                                    <InputLabel for="low_stock_threshold" value="Low Stock Threshold *" />
                                    <TextInput
                                        id="low_stock_threshold"
                                        type="number"
                                        v-model="form.low_stock_threshold"
                                        class="mt-1 block w-full"
                                        required
                                        min="0"
                                    />
                                    <InputError class="mt-2" :message="form.errors.low_stock_threshold" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="flex items-center">
                                        <input
                                            type="checkbox"
                                            v-model="form.is_taxable"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                        <span class="ml-2 text-sm text-gray-700">Taxable</span>
                                    </label>
                                </div>

                                <div>
                                    <label class="flex items-center">
                                        <input
                                            type="checkbox"
                                            v-model="form.is_active"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                        <span class="ml-2 text-sm text-gray-700">Active</span>
                                    </label>
                                </div>
                            </div>

                            <div v-if="form.cost_price && form.selling_price" class="bg-gray-50 p-4 rounded-lg">
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-600">Cost:</span>
                                        <span class="ml-2 font-medium">₵{{ $formatPrice(form.cost_price) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Selling:</span>
                                        <span class="ml-2 font-medium">₵{{ $formatPrice(form.selling_price) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Profit:</span>
                                        <span class="ml-2 font-medium">₵{{ $formatPrice(parseFloat(form.selling_price) - parseFloat(form.cost_price)) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Margin:</span>
                                        <span class="ml-2 font-medium" :class="$calculateMargin(form.cost_price, form.selling_price) >= 50 ? 'text-green-600' : 'text-yellow-600'">
                                            {{ $calculateMargin(form.cost_price, form.selling_price).toFixed(1) }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton @click="closeModal" type="button">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            {{ editingProduct ? 'Update' : 'Create' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Adjust Stock Modal -->
        <Modal :show="showAdjustStockModal" @close="showAdjustStockModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Adjust Stock - {{ productToAdjust?.name }}
                </h3>
                
                <div class="mb-4 bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Current Stock:</span>
                            <span class="ml-2 font-medium">{{ productToAdjust?.stock_quantity }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Low Stock Threshold:</span>
                            <span class="ml-2 font-medium">{{ productToAdjust?.low_stock_threshold }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Cost:</span>
                            <span class="ml-2 font-medium">{{ $currency(productToAdjust?.cost_price) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Selling:</span>
                            <span class="ml-2 font-medium">{{ $currency(productToAdjust?.selling_price) }}</span>
                        </div>
                    </div>
                </div>
                
                <form @submit.prevent="adjustStockSubmit">
                    <div class="space-y-4">
                        <div>
                            <InputLabel for="adjustment_type" value="Adjustment Type" />
                            <select
                                id="adjustment_type"
                                v-model="stockForm.adjustment_type"
                                class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="add">Add Stock</option>
                                <option value="remove">Remove Stock</option>
                                <option value="set">Set Stock</option>
                            </select>
                        </div>

                        <div>
                            <InputLabel for="quantity" :value="quantityLabel" />
                            <TextInput
                                id="quantity"
                                type="number"
                                v-model="stockForm.quantity"
                                class="mt-1 block w-full"
                                required
                                min="0"
                            />
                        </div>

                        <div>
                            <InputLabel for="reason" value="Reason (Optional)" />
                            <textarea
                                id="reason"
                                v-model="stockForm.reason"
                                rows="2"
                                class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                placeholder="e.g., Restock, Damage, Return, etc."
                            ></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton @click="showAdjustStockModal = false" type="button">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="stockForm.processing">
                            Update Stock
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false">
            <template #title>
                Delete Product
            </template>

            <template #content>
                Are you sure you want to delete <strong>{{ productToDelete?.name }}</strong>? This action cannot be undone.
            </template>

            <template #footer>
                <SecondaryButton @click="showDeleteModal = false">
                    Cancel
                </SecondaryButton>

                <DangerButton
                    class="ml-3"
                    @click="deleteProduct"
                    :disabled="form.processing"
                >
                    Delete Product
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Pagination from '@/Components/Pagination.vue';
import Quagga from '@ericblade/quagga2';

const props = defineProps({
    products: Object,
    categories: Array,
});

const search = ref('');
const categoryFilter = ref('');
const statusFilter = ref('');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showAdjustStockModal = ref(false);
const showDeleteModal = ref(false);
const editingProduct = ref(null);
const productToAdjust = ref(null);
const productToDelete = ref(null);

const showScanner = ref(false);
const scannerActive = ref(false);
const recentScans = ref([]);
const manualBarcodeInput = ref('');
const barcodeInput = ref(null);
const scannerManualInput = ref(null);

const form = useForm({
    name: '',
    description: '',
    category_id: '',
    cost_price: 0,
    selling_price: 0,
    stock_quantity: 0,
    low_stock_threshold: 10,
    barcode: '',
    unit: '',
    weight: null,
    is_taxable: true,
    is_active: true,
});

const stockForm = useForm({
    adjustment_type: 'add',
    quantity: 0,
    reason: '',
});

const filteredProducts = computed(() => {
    let filtered = props.products.data || [];

    // Apply search filter
    if (search.value) {
        const searchTerm = search.value.toLowerCase();
        filtered = filtered.filter(product =>
            product.name.toLowerCase().includes(searchTerm) ||
            product.sku.toLowerCase().includes(searchTerm) ||
            (product.barcode && product.barcode.toLowerCase().includes(searchTerm)) ||
            (product.description && product.description.toLowerCase().includes(searchTerm))
        );
    }

    // Apply category filter
    if (categoryFilter.value) {
        filtered = filtered.filter(product => 
            product.category_id == categoryFilter.value
        );
    }

    // Apply status filter
    if (statusFilter.value) {
        if (statusFilter.value === 'active') {
            filtered = filtered.filter(product => product.is_active);
        } else if (statusFilter.value === 'inactive') {
            filtered = filtered.filter(product => !product.is_active);
        } else if (statusFilter.value === 'low_stock') {
            filtered = filtered.filter(product => 
                product.stock_quantity <= product.low_stock_threshold
            );
        } else if (statusFilter.value === 'out_of_stock') {
            filtered = filtered.filter(product => product.stock_quantity === 0);
        }
    }

    return filtered;
});

const quantityLabel = computed(() => {
    switch (stockForm.adjustment_type) {
        case 'add': return 'Quantity to Add';
        case 'remove': return 'Quantity to Remove';
        case 'set': return 'New Stock Quantity';
        default: return 'Quantity';
    }
});

const clearFilters = () => {
    search.value = '';
    categoryFilter.value = '';
    statusFilter.value = '';
};

const editProduct = (product) => {
    editingProduct.value = product;
    form.name = product.name;
    form.description = product.description || '';
    form.category_id = product.category_id;
    form.cost_price = parseFloat(product.cost_price) || 0;
    form.selling_price = parseFloat(product.selling_price) || 0;
    form.stock_quantity = product.stock_quantity;
    form.low_stock_threshold = product.low_stock_threshold;
    form.barcode = product.barcode || '';
    form.unit = product.unit || '';
    form.weight = product.weight;
    form.is_taxable = product.is_taxable;
    form.is_active = product.is_active;
    showEditModal.value = true;
};

const adjustStock = (product) => {
    productToAdjust.value = product;
    stockForm.reset();
    stockForm.adjustment_type = 'add';
    stockForm.quantity = 0;
    showAdjustStockModal.value = true;
};

const adjustStockSubmit = () => {
    const payload = {
        ...stockForm,
        product_id: productToAdjust.value.id,
    };
    
    // In a real app, you'd have an API endpoint for stock adjustment
    // For now, we'll update via the product update endpoint
    let newQuantity = productToAdjust.value.stock_quantity;
    
    switch (stockForm.adjustment_type) {
        case 'add':
            newQuantity += parseInt(stockForm.quantity);
            break;
        case 'remove':
            newQuantity -= parseInt(stockForm.quantity);
            break;
        case 'set':
            newQuantity = parseInt(stockForm.quantity);
            break;
    }
    
    // Update the product
    form.put(route('products.update', productToAdjust.value.id), {
        data: {
            ...productToAdjust.value,
            stock_quantity: newQuantity,
        },
        preserveScroll: true,
        onSuccess: () => {
            showAdjustStockModal.value = false;
            productToAdjust.value = null;
            stockForm.reset();
        },
    });
};

const confirmDelete = (product) => {
    productToDelete.value = product;
    showDeleteModal.value = true;
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    form.reset();
    editingProduct.value = null;
};

const createProduct = () => {
    form.post(route('products.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

const updateProduct = () => {
    form.put(route('products.update', editingProduct.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

const deleteProduct = () => {
    router.delete(route('products.destroy', productToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            productToDelete.value = null;
        },
    });
};

// Add to your existing refs section

// Add these methods to your existing methods
const toggleScanner = () => {
    showScanner.value = !showScanner.value;
    if (showScanner.value) {
        // Focus manual input in scanner mode
        setTimeout(() => {
            scannerManualInput.value?.focus();
        }, 100);
    } else {
        // Focus barcode input in manual mode
        setTimeout(() => {
            barcodeInput.value?.focus();
        }, 100);
    }
};

const generateBarcode = () => {
    if (!form.barcode) {
        // Generate a unique barcode (EAN-13 format example)
        const prefix = '69'; // Country code for China (can change based on your needs)
        const random = Math.floor(1000000000 + Math.random() * 9000000000);
        const barcode = prefix + random.toString().substring(0, 10);
        
        // Calculate check digit for EAN-13
        let sum = 0;
        for (let i = 0; i < 12; i++) {
            const digit = parseInt(barcode[i]);
            sum += (i % 2 === 0) ? digit : digit * 3;
        }
        const checkDigit = (10 - (sum % 10)) % 10;
        
        form.barcode = barcode + checkDigit;
    }
};

const generateBarcodeIfEmpty = () => {
    if (!form.barcode) {
        generateBarcode();
    }
};


const startScanner = async () => {
    try {
        scannerActive.value = true;
        
        await Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.querySelector('#scanner-container'),
                constraints: {
                    facingMode: "environment" // Use back camera
                },
            },
            decoder: {
                readers: [
                    "ean_reader",
                    "ean_8_reader",
                    "code_128_reader",
                    "code_39_reader",
                    "upc_reader",
                    "upc_e_reader"
                ]
            }
        }, (err) => {
            if (err) {
                console.error('Error initializing scanner:', err);
                scannerActive.value = false;
                return;
            }
            
            Quagga.start();
            
            Quagga.onDetected((result) => {
                const code = result.codeResult.code;
                handleScannedBarcode(code);
                
                // Optional: Stop scanner after successful scan
                Quagga.stop();
                scannerActive.value = false;
            });
        });
        
    } catch (error) {
        console.error('Error starting scanner:', error);
        scannerActive.value = false;
    }
};

const stopScanner = () => {
    if (Quagga) {
        Quagga.stop();
    }
    scannerActive.value = false;
};
const handleScannedBarcode = (barcode) => {
    form.barcode = barcode;
    addToRecentScans(barcode);
    
    // Optional: Show success message
    const event = new CustomEvent('show-toast', {
        detail: {
            type: 'success',
            message: `Barcode scanned: ${barcode}`
        }
    });
    window.dispatchEvent(event);
};

const addToRecentScans = (barcode) => {
    // Remove if already exists
    recentScans.value = recentScans.value.filter(scan => scan !== barcode);
    // Add to beginning
    recentScans.value.unshift(barcode);
    // Keep only last 5 scans
    if (recentScans.value.length > 5) {
        recentScans.value.pop();
    }
};

const useScannedBarcode = (barcode) => {
    form.barcode = barcode;
    
    // Optional: Show message
    const event = new CustomEvent('show-toast', {
        detail: {
            type: 'info',
            message: `Barcode selected: ${barcode}`
        }
    });
    window.dispatchEvent(event);
};

const addManualBarcode = () => {
    if (manualBarcodeInput.value.trim()) {
        form.barcode = manualBarcodeInput.value.trim();
        addToRecentScans(manualBarcodeInput.value.trim());
        manualBarcodeInput.value = '';
        
        // Focus back on input
        setTimeout(() => {
            scannerManualInput.value?.focus();
        }, 100);
    }
};

const copyBarcode = async () => {
    try {
        await navigator.clipboard.writeText(form.barcode);
        
        // Show success message
        const event = new CustomEvent('show-toast', {
            detail: {
                type: 'success',
                message: 'Barcode copied to clipboard!'
            }
        });
        window.dispatchEvent(event);
    } catch (err) {
        console.error('Failed to copy barcode:', err);
    }
};

const formatBarcodeVisual = (barcode) => {
    if (!barcode) return '';
    // Simple visual representation with pipes for bars
    return barcode.split('').map(char => {
        if (char === '0') return '||';
        if (char === '1') return '|';
        if (char === '2') return '|||';
        if (char === '3') return '||||';
        if (char === '4') return '|||||';
        if (char === '5') return '||||||';
        if (char === '6') return '|||||||';
        if (char === '7') return '||||||||';
        if (char === '8') return '|||||||||';
        if (char === '9') return '||||||||||';
        return char;
    }).join('');
};
</script>