<template>
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900">Receipt</h2>
                <div class="flex space-x-3">
                    <button
                        @click="printReceipt"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print Receipt
                    </button>
                    <Link
                        :href="route('sales.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-white hover:bg-gray-700"
                    >
                        Back to Sales
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto">
                <!-- Receipt Content -->
                <div id="receipt-content" class="bg-white shadow-lg rounded-lg p-8">
                    <!-- Company Header -->
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-900">{{ company.name }}</h1>
                        <p class="text-gray-600">{{ company.address }}</p>
                        <p class="text-gray-600">Tel: {{ company.phone }} | Email: {{ company.email }}</p>
                        <div class="mt-2 border-t pt-2">
                            <h2 class="text-xl font-semibold">SALES RECEIPT</h2>
                        </div>
                    </div>

                    <!-- Sale Info -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-600">Invoice #</p>
                            <p class="font-bold">{{ sale.invoice_number }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Date</p>
                            <p class="font-bold">{{ formatDate(sale.created_at) }}</p>
                            <p class="text-sm">{{ formatTime(sale.created_at) }}</p>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="mb-6">
                        <p class="text-sm text-gray-600">Customer</p>
                        <p class="font-bold">
                            {{ sale.customer ? sale.customer.name : 'Walk-in Customer' }}
                        </p>
                        <p v-if="sale.customer?.phone" class="text-gray-600">{{ sale.customer.phone }}</p>
                        <p v-if="sale.customer?.email" class="text-gray-600">{{ sale.customer.email }}</p>
                    </div>

                    <!-- Items Table -->
                    <div class="mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Item
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Qty
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Price
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in sale.items" :key="item.id">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ item.product?.name || 'Unknown Product' }}</div>
                                        <div class="text-sm text-gray-500">{{ item.product?.sku || '' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-900">{{ item.quantity }} {{ item.product?.unit || 'pcs' }}</td>
                                    <td class="px-4 py-3 text-gray-900">${{ formatCurrency(item.unit_price) }}</td>
                                    <td class="px-4 py-3 font-bold text-gray-900">${{ formatCurrency(item.total_price) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="border-t pt-4">
                        <div class="max-w-xs ml-auto">
                            <div class="flex justify-between py-1">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-medium">${{ formatCurrency(sale.subtotal) }}</span>
                            </div>
                            <div v-if="sale.tax_amount > 0" class="flex justify-between py-1">
                                <span class="text-gray-600">Tax (16%):</span>
                                <span class="font-medium">${{ formatCurrency(sale.tax_amount) }}</span>
                            </div>
                            <div v-if="sale.discount_amount > 0" class="flex justify-between py-1">
                                <span class="text-gray-600">Discount:</span>
                                <span class="font-medium text-green-600">-${{ formatCurrency(sale.discount_amount) }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-t mt-2">
                                <span class="text-lg font-bold">Total:</span>
                                <span class="text-lg font-bold">${{ formatCurrency(sale.total_amount) }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-gray-600">Amount Paid:</span>
                                <span class="font-medium">${{ formatCurrency(sale.amount_paid) }}</span>
                            </div>
                            <div v-if="sale.change_amount > 0" class="flex justify-between py-1">
                                <span class="text-gray-600">Change:</span>
                                <span class="font-medium text-green-600">${{ formatCurrency(sale.change_amount) }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-gray-600">Payment Method:</span>
                                <span class="font-medium">{{ getPaymentMethodLabel(sale.payment_method) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-8 pt-8 border-t text-center text-gray-500 text-sm">
                        <p>Thank you for your business!</p>
                        <p class="mt-1">This receipt is computer generated and does not require a signature.</p>
                        <p class="mt-2">Invoice ID: {{ sale.invoice_number }}</p>
                        <p>Cashier: {{ sale.user?.name || 'System' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    sale: Object,
    company: Object,
})

const formatDate = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const formatTime = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatCurrency = (value) => {
    if (value === null || value === undefined) return '0.00'
    const num = parseFloat(value)
    return isNaN(num) ? '0.00' : num.toFixed(2)
}

const getPaymentMethodLabel = (method) => {
    const methods = {
        'cash': 'Cash',
        'card': 'Credit/Debit Card',
        'mobile': 'Mobile Money',
        'transfer': 'Bank Transfer',
    }
    return methods[method] || method.charAt(0).toUpperCase() + method.slice(1)
}

const printReceipt = () => {
    const content = document.getElementById('receipt-content').innerHTML
    const originalContent = document.body.innerHTML
    
    document.body.innerHTML = content
    window.print()
    document.body.innerHTML = originalContent
    window.location.reload()
}
</script>

<style scoped>
@media print {
    body * {
        visibility: hidden;
    }
    #receipt-content, #receipt-content * {
        visibility: visible;
    }
    #receipt-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        box-shadow: none;
    }
}
</style>