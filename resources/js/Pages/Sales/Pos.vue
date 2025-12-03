<template>
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Point of Sale (POS)</h2>
                    <p class="mt-1 text-sm text-gray-600">Quick sales, barcode scanning, and customer management</p>
                </div>
                <div class="flex space-x-3">
                    <Link 
                        :href="route('sales.index')" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Sales History
                    </Link>
                </div>
            </div>
        </template>

        <!-- Notification Toast -->
        <div v-if="showNotification" class="fixed top-4 right-4 z-50">
            <div :class="notificationClass" class="px-6 py-4 rounded-lg shadow-lg flex items-center">
                <span class="mr-3">{{ notificationMessage }}</span>
                <button @click="showNotification = false" class="ml-auto">×</button>
            </div>
        </div>

        <!-- Main POS Interface -->
        <div class="flex h-[calc(100vh-8rem)] bg-gray-100">
            <!-- Column 1: Products Grid (45%) -->
            <div class="w-[45%] flex flex-col border-r border-gray-200">
                <!-- Search & Controls -->
                <div class="p-4 bg-white border-b">
                    <div class="flex items-center space-x-4">
                        <div class="flex-1 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                v-model="searchTerm"
                                @keyup.enter="searchProduct"
                                @input="handleSearchInput"
                                placeholder="Scan barcode or search products..."
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                ref="searchInput"
                                autofocus
                            />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                    {{ filteredProducts.length }} products
                                </span>
                            </div>
                        </div>
                        
                        <!-- Quick Action Buttons -->
                        <div class="flex space-x-2">
                            <button 
                                @click="clearCart" 
                                :disabled="cartItems.length === 0"
                                :class="cartItems.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-red-600'"
                                class="px-4 py-2 bg-red-500 text-white rounded-lg font-medium transition-colors"
                            >
                                Clear Cart
                            </button>
                            <button 
                                @click="holdSale" 
                                :disabled="cartItems.length === 0"
                                :class="cartItems.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-yellow-600'"
                                class="px-4 py-2 bg-yellow-500 text-white rounded-lg font-medium transition-colors"
                            >
                                Hold Sale
                            </button>
                        </div>
                    </div>
                    
                    <!-- Quick Categories -->
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button
                            @click="selectedCategory = null"
                            :class="selectedCategory === null ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'"
                            class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors"
                        >
                            All Products
                        </button>
                        <button
                            v-for="category in uniqueCategories"
                            :key="category"
                            @click="selectedCategory = category"
                            :class="selectedCategory === category ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'"
                            class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors"
                        >
                            {{ category }}
                        </button>
                    </div>
                </div>
                
                <!-- Products Grid -->
                <div class="flex-1 overflow-y-auto p-4">
                    <div v-if="filteredProducts.length === 0" class="text-center py-12">
                        <div class="text-gray-400 mb-4">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
                        <p class="text-gray-500">Try a different search term or category</p>
                    </div>
                    
                    <div v-else class="grid grid-cols-3 gap-3">
                        <div
                            v-for="product in filteredProducts"
                            :key="product.id"
                            @click="addToCart(product)"
                            :class="[
                                'bg-white rounded-lg border cursor-pointer transition-all duration-200 hover:shadow-md',
                                product.available_stock <= 0 ? 'opacity-60' : ''
                            ]"
                        >
                            <div class="p-3">
                                <!-- Product Image -->
                                <div class="relative mb-2">
                                    <div v-if="product.image" class="h-24 bg-gray-100 rounded-lg overflow-hidden">
                                        <img :src="product.image" :alt="product.name" class="w-full h-full object-cover">
                                    </div>
                                    <div v-else class="h-24 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    
                                    <!-- Stock Badge -->
                                    <div class="absolute top-2 right-2">
                                        <span 
                                            :class="[
                                                'px-2 py-1 text-xs font-bold rounded-full',
                                                product.available_stock > 10 ? 'bg-green-100 text-green-800' :
                                                product.available_stock > 0 ? 'bg-yellow-100 text-yellow-800' :
                                                'bg-red-100 text-red-800'
                                            ]"
                                        >
                                            {{ product.available_stock }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Product Info -->
                                <h3 class="font-semibold text-gray-900 text-sm mb-1 line-clamp-2">{{ product.name }}</h3>
                                <div class="text-xs text-gray-500 mb-2 truncate">{{ product.sku }}</div>
                                
                                <!-- Price -->
                                <div class="flex justify-between items-center">
                                    <span class="text-base font-bold text-blue-600">
                                        {{ formatCurrency(product.selling_price) }}
                                    </span>
                                    <span v-if="product.is_taxable" class="text-xs text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">
                                        VAT
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Column 2: Cart Items (30%) -->
            <div class="w-[30%] flex flex-col border-r border-gray-200 bg-white">
                <!-- Cart Header -->
                <div class="p-4 border-b">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold text-gray-900">
                            Order Items ({{ cartItems.length }})
                        </h2>
                        <div class="flex items-center space-x-2">
                            <button 
                                @click="clearCart" 
                                :disabled="cartItems.length === 0"
                                :class="cartItems.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-red-600'"
                                class="px-3 py-1.5 bg-red-500 text-white text-sm rounded-lg font-medium transition-colors"
                            >
                                Clear All
                            </button>
                            <button 
                                @click="holdSale" 
                                :disabled="cartItems.length === 0"
                                :class="cartItems.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-yellow-600'"
                                class="px-3 py-1.5 bg-yellow-500 text-white text-sm rounded-lg font-medium transition-colors"
                            >
                                Hold Order
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto">
                    <div v-if="cartItems.length === 0" class="h-full flex flex-col items-center justify-center text-center p-8">
                        <div class="text-gray-300 mb-4">
                            <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Cart is empty</h3>
                        <p class="text-gray-500 text-sm">Click products to add them to your order</p>
                    </div>
                    
                    <div v-else class="divide-y divide-gray-100">
                        <div 
                            v-for="(item, index) in cartItems" 
                            :key="index"
                            class="p-4 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-start space-x-3">
                                <!-- Item Image -->
                                <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                    <img 
                                        v-if="item.product.image" 
                                        :src="item.product.image" 
                                        :alt="item.product.name" 
                                        class="w-full h-full object-cover"
                                    >
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                
                                <!-- Item Details -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h4 class="font-semibold text-gray-900 text-sm mb-1">{{ item.product.name }}</h4>
                                            <p class="text-xs text-gray-500">{{ item.product.sku }}</p>
                                        </div>
                                        <button 
                                            @click="removeFromCart(index)"
                                            class="text-gray-400 hover:text-red-500 transition-colors flex-shrink-0"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <!-- Quantity Controls -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <button 
                                                @click="updateQuantity(index, -1)"
                                                class="w-7 h-7 flex items-center justify-center border border-gray-300 rounded bg-white hover:bg-gray-50 transition-colors"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                </svg>
                                            </button>
                                            
                                            <div class="relative">
                                                <input
                                                    type="number"
                                                    v-model="item.quantity"
                                                    @change="validateItemQuantity(index)"
                                                    class="w-14 text-center py-1 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                                    min="1"
                                                />
                                            </div>
                                            
                                            <button 
                                                @click="updateQuantity(index, 1)"
                                                class="w-7 h-7 flex items-center justify-center border border-gray-300 rounded bg-white hover:bg-gray-50 transition-colors"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                            </button>
                                        </div>
                                        
                                        <!-- Item Price -->
                                        <div class="text-right">
                                            <div class="text-sm text-gray-500 mb-0.5">
                                                {{ formatCurrency(item.price) }} each
                                            </div>
                                            <div class="text-base font-bold text-gray-900">
                                                {{ formatCurrency(item.price * item.quantity) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Cart Summary -->
                <div class="border-t border-gray-200 p-4 bg-gray-50">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium">{{ formatCurrency(subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax ({{ taxRate }}%)</span>
                            <span class="font-medium">{{ formatCurrency(taxAmount) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">Discount</span>
                            <div class="flex items-center space-x-2">
                                <input
                                    type="number"
                                    v-model="discountAmount"
                                    @change="validateDiscount"
                                    class="w-20 px-2 py-1 border border-gray-300 rounded text-right text-sm"
                                    min="0"
                                    step="0.01"
                                />
                                <span class="font-medium">{{ formatCurrency(discountAmount) }}</span>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-gray-300">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Order Total</span>
                                <span class="text-blue-600">{{ formatCurrency(totalAmount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Column 3: Payment & Customer (25%) -->
            <div class="w-[25%] flex flex-col bg-white">
                <!-- Customer Selection -->
                <div class="p-4 border-b">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-medium text-gray-900">Customer Details</h3>
                        <button 
                            @click="showCustomerModal = true"
                            class="text-sm text-blue-600 hover:text-blue-800 flex items-center"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            New
                        </button>
                    </div>
                    
                    <div class="space-y-3">
                        <select
                            v-model="selectedCustomerId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
                        >
                            <option :value="null">Walk-in Customer</option>
                            <option
                                v-for="customer in customers"
                                :key="customer.id"
                                :value="customer.id"
                            >
                                {{ customer.name }} ({{ customer.phone }})
                            </option>
                        </select>
                        
                        <div v-if="selectedCustomer" class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
                            <div class="font-medium mb-1">{{ selectedCustomer.name }}</div>
                            <div v-if="selectedCustomer.phone" class="mb-1">Phone: {{ selectedCustomer.phone }}</div>
                            <div v-if="selectedCustomer.email" class="mb-1">Email: {{ selectedCustomer.email }}</div>
                            <div class="text-xs text-gray-500 mt-2">
                                Total Spent: {{ formatCurrency(selectedCustomer.total_spent || 0) }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div class="p-4 border-b">
                    <h3 class="font-medium text-gray-900 mb-3">Payment Method</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            v-for="(label, value) in paymentMethods"
                            :key="value"
                            @click="paymentMethod = value"
                            :class="[
                                'py-2.5 rounded-lg border transition-colors text-sm',
                                paymentMethod === value 
                                    ? 'bg-blue-600 text-white border-blue-600' 
                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                            ]"
                        >
                            {{ label }}
                        </button>
                    </div>
                </div>
                
                <!-- Amount Paid & Change -->
                <div class="p-4 border-b">
                    <h3 class="font-medium text-gray-900 mb-3">Payment</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Amount Paid
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">{{ currencySymbol }}</span>
                                </div>
                                <input
                                    type="number"
                                    v-model="amountPaid"
                                    @input="calculateChange"
                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                />
                            </div>
                            
                            <!-- Quick Amount Buttons -->
                            <div class="grid grid-cols-3 gap-2 mt-2">
                                <button 
                                    @click="amountPaid = Math.round(totalAmount)"
                                    class="px-2 py-1.5 text-xs border border-gray-300 rounded hover:bg-gray-50"
                                >
                                    Exact
                                </button>
                                <button 
                                    @click="amountPaid = Math.ceil(totalAmount)"
                                    class="px-2 py-1.5 text-xs border border-gray-300 rounded hover:bg-gray-50"
                                >
                                    Round Up
                                </button>
                                <button 
                                    @click="amountPaid = totalAmount * 2"
                                    class="px-2 py-1.5 text-xs border border-gray-300 rounded hover:bg-gray-50"
                                >
                                    Double
                                </button>
                            </div>
                        </div>
                        
                        <!-- Change Display -->
                        <div v-if="changeAmount > 0" class="p-3 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-green-800 text-sm">Change Due</span>
                                <span class="text-xl font-bold text-green-800">
                                    {{ formatCurrency(changeAmount) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Notes -->
                <div class="p-4 border-b flex-1">
                    <h3 class="font-medium text-gray-900 mb-2">Order Notes</h3>
                    <textarea
                        v-model="orderNotes"
                        class="w-full h-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
                        placeholder="Add any special instructions or notes..."
                    ></textarea>
                </div>
                
                <!-- Complete Sale Button -->
                <div class="p-4 border-t border-gray-200 bg-gray-50">
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-900">Total</span>
                            <span class="text-2xl font-bold text-green-600">
                                {{ formatCurrency(totalAmount) }}
                            </span>
                        </div>
                        
                        <button
                            @click="processSale"
                            :disabled="cartItems.length === 0 || isProcessing"
                            :class="[
                                'w-full py-3 rounded-lg font-bold transition-colors',
                                cartItems.length === 0 || isProcessing
                                    ? 'bg-gray-400 text-gray-200 cursor-not-allowed'
                                    : 'bg-green-600 text-white hover:bg-green-700'
                            ]"
                        >
                            <span v-if="isProcessing">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                            <span v-else>
                                COMPLETE SALE
                            </span>
                        </button>
                        
                        <div class="text-center text-xs text-gray-500">
                            Press <kbd class="px-2 py-1 bg-gray-100 rounded border">Enter</kbd> to complete sale
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Modal -->
        <CustomerModal
            v-if="showCustomerModal"
            @close="showCustomerModal = false"
            @saved="handleCustomerSaved"
        />
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { router, usePage, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CustomerModal from '@/Components/CustomerModal.vue';

// Props
const props = defineProps({
    customers: Array,
    paymentMethods: Object,
    defaultWarehouse: Object,
    taxRate: Number,
    products: Array,
});

// Get page props for currency
const page = usePage();

// Currency configuration
const currencyConfig = computed(() => {
    return page.props.currency || {
        symbol: 'GH₵',
        position: 'before',
        decimals: 2
    };
});

const currencySymbol = computed(() => currencyConfig.value.symbol);

// Currency formatting function
const formatCurrency = (amount) => {
    const amountNumber = parseFloat(amount) || 0;
    const formattedAmount = amountNumber.toFixed(currencyConfig.value.decimals);
    
    if (currencyConfig.value.position === 'after') {
        return `${formattedAmount} ${currencySymbol.value}`;
    } else {
        return `${currencySymbol.value}${formattedAmount}`;
    }
};

// Refs
const searchTerm = ref('');
const cartItems = ref([]);
const selectedCustomerId = ref(null);
const paymentMethod = ref('cash');
const discountAmount = ref(0);
const amountPaid = ref(0);
const changeAmount = ref(0);
const showCustomerModal = ref(false);
const isProcessing = ref(false);
const searchInput = ref(null);
const selectedCategory = ref(null);
const showNotification = ref(false);
const notificationMessage = ref('');
const notificationType = ref('success');
const orderNotes = ref('');

// Use Inertia form for sale submission
const saleForm = useForm({
    customer_id: null,
    items: [],
    subtotal: 0,
    tax_amount: 0,
    discount_amount: 0,
    total_amount: 0,
    amount_paid: 0,
    change_amount: 0,
    payment_method: 'cash',
    notes: '',
    warehouse_id: null,
});

// Computed Properties
const subtotal = computed(() => {
    return cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const taxAmount = computed(() => {
    const taxableItems = cartItems.value.filter(item => item.product.is_taxable);
    const taxableTotal = taxableItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    return taxableTotal * (props.taxRate / 100);
});

const totalAmount = computed(() => {
    return subtotal.value + taxAmount.value - discountAmount.value;
});

const uniqueCategories = computed(() => {
    const categories = props.products
        .map(product => product.category?.name)
        .filter(name => name);
    return [...new Set(categories)];
});

const filteredProducts = computed(() => {
    let filtered = props.products;
    
    // Filter by search term
    if (searchTerm.value) {
        const term = searchTerm.value.toLowerCase();
        filtered = filtered.filter(product => 
            product.name.toLowerCase().includes(term) ||
            product.sku.toLowerCase().includes(term) ||
            product.barcode?.toLowerCase().includes(term)
        );
    }
    
    // Filter by category
    if (selectedCategory.value) {
        filtered = filtered.filter(product => 
            product.category?.name === selectedCategory.value
        );
    }
    
    return filtered;
});

const selectedCustomer = computed(() => {
    return props.customers.find(c => c.id === selectedCustomerId.value) || null;
});

// Methods
const showNotificationMessage = (message, type = 'success') => {
    notificationMessage.value = message;
    notificationType.value = type;
    showNotification.value = true;
    
    setTimeout(() => {
        showNotification.value = false;
    }, 3000);
};

const notificationClass = computed(() => {
    return {
        'success': 'bg-green-100 text-green-800 border border-green-200',
        'error': 'bg-red-100 text-red-800 border border-red-200',
        'warning': 'bg-yellow-100 text-yellow-800 border border-yellow-200',
        'info': 'bg-blue-100 text-blue-800 border border-blue-200'
    }[notificationType.value];
});

const searchProduct = async () => {
    if (!searchTerm.value.trim()) return;
    
    // Simple local search
    const product = props.products.find(p => 
        p.barcode === searchTerm.value || 
        p.sku === searchTerm.value
    );
    
    if (product) {
        addToCart(product);
        searchTerm.value = '';
        focusSearch();
    } else {
        showNotificationMessage('Product not found', 'warning');
    }
};

const handleSearchInput = () => {
    if (searchTerm.value) {
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(() => {
            searchTerm.value = '';
        }, 2000);
    }
};

const addToCart = (product) => {
    if (product.available_stock <= 0) {
        showNotificationMessage(`${product.name} is out of stock!`, 'warning');
        return;
    }
    
    const existingIndex = cartItems.value.findIndex(
        item => item.product.id === product.id
    );
    
    if (existingIndex >= 0) {
        const newQuantity = cartItems.value[existingIndex].quantity + 1;
        const totalInCart = cartItems.value[existingIndex].quantity;
        const availableForThisAddition = product.available_stock - totalInCart;
        
        if (availableForThisAddition <= 0) {
            showNotificationMessage(`Only ${product.available_stock} ${product.name} available!`, 'warning');
            return;
        }
        
        cartItems.value[existingIndex].quantity = newQuantity;
        showNotificationMessage(`Increased ${product.name} quantity to ${newQuantity}`, 'success');
    } else {
        cartItems.value.push({
            product: product,
            quantity: 1,
            price: product.selling_price,
            originalPrice: product.selling_price
        });
        showNotificationMessage(`Added ${product.name} to cart`, 'success');
    }
    
    searchTerm.value = '';
    focusSearch();
};

const removeFromCart = (index) => {
    const productName = cartItems.value[index].product.name;
    cartItems.value.splice(index, 1);
    showNotificationMessage(`Removed ${productName} from cart`, 'info');
};

const updateQuantity = (index, delta) => {
    const newQuantity = cartItems.value[index].quantity + delta;
    const product = cartItems.value[index].product;
    
    if (newQuantity < 1) {
        removeFromCart(index);
        return;
    }
    
    if (newQuantity > product.available_stock) {
        showNotificationMessage(`Only ${product.available_stock} items available!`, 'warning');
        return;
    }
    
    cartItems.value[index].quantity = newQuantity;
};

const validateItemQuantity = (index) => {
    if (cartItems.value[index].quantity < 1) {
        cartItems.value[index].quantity = 1;
    }
    
    const product = cartItems.value[index].product;
    if (cartItems.value[index].quantity > product.available_stock) {
        showNotificationMessage(`Reduced to available stock: ${product.available_stock}`, 'warning');
        cartItems.value[index].quantity = product.available_stock;
    }
};

const validateDiscount = () => {
    if (discountAmount.value < 0) {
        discountAmount.value = 0;
    }
    if (discountAmount.value > subtotal.value) {
        discountAmount.value = subtotal.value;
        showNotificationMessage(`Discount cannot exceed subtotal of ${formatCurrency(subtotal.value)}`, 'warning');
    }
};

const calculateChange = () => {
    if (amountPaid.value < 0) {
        amountPaid.value = 0;
    }
    changeAmount.value = Math.max(0, amountPaid.value - totalAmount.value);
};

const clearCart = () => {
    console.log('Clear cart triggered');
    console.log('Current cart items:', cartItems.value);
    
    if (cartItems.value.length === 0) {
        showNotificationMessage('Cart is already empty', 'info');
        return;
    }
    
    // Force clear without confirmation for debugging
    cartItems.value = [];
    discountAmount.value = 0;
    amountPaid.value = 0;
    changeAmount.value = 0;
    orderNotes.value = '';
    
    // Show success message
    showNotificationMessage('Cart cleared successfully', 'success');
    
    // Log to console
    console.log('Cart cleared. New cart items:', cartItems.value);
    
    focusSearch();
};

const holdSale = () => {
    if (cartItems.value.length === 0) {
        showNotificationMessage('No items in cart to hold', 'warning');
        return;
    }
    
    const holdData = {
        items: cartItems.value.map(item => ({
            product_id: item.product.id,
            quantity: item.quantity,
            price: item.price,
            product_name: item.product.name
        })),
        customer_id: selectedCustomerId.value,
        timestamp: new Date().toISOString(),
        total: totalAmount.value,
        item_count: cartItems.value.length,
        notes: orderNotes.value
    };
    
    localStorage.setItem('heldSale', JSON.stringify(holdData));
    showNotificationMessage(`Order held with ${cartItems.value.length} items`, 'success');
};

const processSale = () => {
    if (cartItems.value.length === 0) {
        showNotificationMessage('Add items to cart first', 'warning');
        return;
    }
    
    // Validate stock
    for (const item of cartItems.value) {
        if (item.quantity > item.product.available_stock) {
            showNotificationMessage(`Insufficient stock for ${item.product.name}. Available: ${item.product.available_stock}`, 'error');
            return;
        }
    }
    
    if (amountPaid.value < totalAmount.value) {
        const paidFormatted = formatCurrency(amountPaid.value);
        const totalFormatted = formatCurrency(totalAmount.value);
        if (!confirm(`Amount paid (${paidFormatted}) is less than total (${totalFormatted}). Continue?`)) {
            return;
        }
    }
    
    isProcessing.value = true;
    
    // Prepare form data
    saleForm.customer_id = selectedCustomerId.value;
    saleForm.items = cartItems.value.map(item => ({
        product_id: item.product.id,
        quantity: item.quantity,
        price: item.price
    }));
    saleForm.subtotal = subtotal.value;
    saleForm.tax_amount = taxAmount.value;
    saleForm.discount_amount = discountAmount.value;
    saleForm.total_amount = totalAmount.value;
    saleForm.amount_paid = amountPaid.value;
    saleForm.change_amount = changeAmount.value;
    saleForm.payment_method = paymentMethod.value;
    saleForm.warehouse_id = props.defaultWarehouse?.id;
    saleForm.notes = orderNotes.value;
    
    // Submit using Inertia form
    saleForm.post(route('sales.process'), {
        preserveScroll: true,
        onSuccess: (page) => {
            showNotificationMessage('Sale completed successfully!', 'success');
            
            // Clear cart
            cartItems.value = [];
            discountAmount.value = 0;
            amountPaid.value = 0;
            changeAmount.value = 0;
            selectedCustomerId.value = null;
            orderNotes.value = '';
            
            // If there's a receipt URL, open it
            if (page.props.redirect_url) {
                window.open(page.props.redirect_url, '_blank');
            }
        },
        onError: (errors) => {
            console.error('Sale errors:', errors);
            showNotificationMessage('Error processing sale. Please try again.', 'error');
        },
        onFinish: () => {
            isProcessing.value = false;
        },
    });
};

const handleCustomerSaved = (customer) => {
    selectedCustomerId.value = customer.id;
    showCustomerModal.value = false;
    showNotificationMessage(`Customer ${customer.name} added successfully`, 'success');
};

const filterByCategory = (category) => {
    selectedCategory.value = category;
    focusSearch();
};

const clearSearch = () => {
    searchTerm.value = '';
    selectedCategory.value = null;
    focusSearch();
};

const focusSearch = () => {
    nextTick(() => {
        searchInput.value?.focus();
    });
};

// Keyboard shortcuts
const setupKeyboardShortcuts = () => {
    const handleKeyDown = (e) => {
        // Ctrl/Cmd + K to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            focusSearch();
        }
        
        // Enter to complete sale (only when not in input fields)
        if (e.key === 'Enter' && !e.ctrlKey && !e.metaKey && cartItems.value.length > 0) {
            const activeElement = document.activeElement;
            if (!activeElement || (activeElement.tagName !== 'INPUT' && activeElement.tagName !== 'TEXTAREA' && activeElement.tagName !== 'SELECT')) {
                processSale();
            }
        }
        
        // Escape to clear search
        if (e.key === 'Escape') {
            clearSearch();
        }
        
        // F1 to hold sale
        if (e.key === 'F1') {
            e.preventDefault();
            holdSale();
        }
        
        // F2 to clear cart
        if (e.key === 'F2') {
            e.preventDefault();
            clearCart();
        }
    };
    
    window.addEventListener('keydown', handleKeyDown);
    
    // Cleanup
    return () => window.removeEventListener('keydown', handleKeyDown);
};

// Lifecycle
onMounted(() => {
    focusSearch();
    
    // Load held sale if exists
    const heldSale = localStorage.getItem('heldSale');
    if (heldSale) {
        try {
            const data = JSON.parse(heldSale);
            if (confirm(`Load held order from ${new Date(data.timestamp).toLocaleTimeString()}? ${data.item_count} items, Total: ${formatCurrency(data.total)}`)) {
                selectedCustomerId.value = data.customer_id;
                orderNotes.value = data.notes || '';
                showNotificationMessage(`Loaded held order with ${data.item_count} items`, 'success');
                localStorage.removeItem('heldSale');
            }
        } catch (e) {
            console.error('Error loading held sale:', e);
            localStorage.removeItem('heldSale');
        }
    }
    
    // Setup keyboard shortcuts
    const cleanup = setupKeyboardShortcuts();
    
    // Cleanup on unmount
    return cleanup;
});
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>