<template>
    <Head :title="`Print Purchase Order - ${purchaseOrder.po_number}`" />
    
    <div class="min-h-screen bg-gray-50 print:bg-white">
        <!-- Print Controls (Hidden when printing) -->
        <div class="print:hidden bg-white shadow-sm border-b border-gray-200 p-4">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-lg font-semibold text-gray-900">Print Purchase Order: {{ purchaseOrder.po_number }}</h1>
                    <p class="text-sm text-gray-600">Print or save as PDF</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        @click="goBack"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Order
                    </button>
                    <button
                        @click="printDocument"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print
                    </button>
                    <button
                        @click="saveAsPDF"
                        :disabled="savingPdf"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50"
                    >
                        <svg v-if="!savingPdf" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <svg v-else class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        {{ savingPdf ? 'Generating...' : 'Save as PDF' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Purchase Order Document -->
        <div class="max-w-4xl mx-auto bg-white p-8 print:p-0 shadow-lg print:shadow-none">
            <!-- Company Header -->
            <div class="border-b border-gray-300 pb-6 mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ company.name }}</h1>
                        <div class="mt-2 text-sm text-gray-600 space-y-1">
                            <div>{{ company.address }}</div>
                            <div>Phone: {{ company.phone }} | Email: {{ company.email }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <h2 class="text-2xl font-bold text-blue-600">PURCHASE ORDER</h2>
                        <div class="mt-2 text-sm text-gray-700">
                            <div>PO Number: <span class="font-bold">{{ purchaseOrder.po_number }}</span></div>
                            <div>Date: {{ formatDate(purchaseOrder.order_date) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- From & To Sections -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <!-- From: Company -->
                <div>
                    <h3 class="text-sm font-semibold uppercase text-gray-500 mb-2">From</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="font-bold text-gray-900">{{ company.name }}</div>
                        <div class="text-sm text-gray-700 mt-1">{{ company.address }}</div>
                        <div class="text-sm text-gray-700">Phone: {{ company.phone }}</div>
                        <div class="text-sm text-gray-700">Email: {{ company.email }}</div>
                    </div>
                </div>

                <!-- To: Supplier -->
                <div>
                    <h3 class="text-sm font-semibold uppercase text-gray-500 mb-2">To</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="font-bold text-gray-900">{{ purchaseOrder.supplier?.name }}</div>
                        <div v-if="purchaseOrder.supplier?.contact_person" class="text-sm text-gray-700 mt-1">
                            Contact: {{ purchaseOrder.supplier.contact_person }}
                        </div>
                        <div class="text-sm text-gray-700">{{ purchaseOrder.supplier?.address }}</div>
                        <div class="text-sm text-gray-700">Phone: {{ purchaseOrder.supplier?.phone || 'N/A' }}</div>
                        <div class="text-sm text-gray-700">Email: {{ purchaseOrder.supplier?.email || 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-sm font-semibold text-blue-700">Order Date</div>
                        <div class="text-lg font-bold text-blue-900">{{ formatDate(purchaseOrder.order_date) }}</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-sm font-semibold text-purple-700">Expected Delivery</div>
                        <div class="text-lg font-bold text-purple-900">
                            {{ purchaseOrder.expected_delivery_date ? formatDate(purchaseOrder.expected_delivery_date) : 'Not specified' }}
                        </div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-sm font-semibold text-green-700">Status</div>
                        <div class="text-lg font-bold text-green-900">{{ formatStatus(purchaseOrder.status) }}</div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Order Items</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300">#</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300">Product Description</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300">Unit Price</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(item, index) in purchaseOrder.items" :key="item.id">
                                <td class="px-4 py-3 text-sm text-gray-900 border-r border-gray-300">{{ index + 1 }}</td>
                                <td class="px-4 py-3 border-r border-gray-300">
                                    <div class="font-medium text-gray-900">{{ item.product?.name }}</div>
                                    <div class="text-xs text-gray-600">SKU: {{ item.product?.sku || 'N/A' }}</div>
                                    <div class="text-xs text-gray-600">Category: {{ item.product?.category?.name || 'Uncategorized' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 border-r border-gray-300">
                                    {{ item.quantity }} {{ item.product?.unit || 'pcs' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 border-r border-gray-300">
                                    {{ $currency(item.unit_cost) }}
                                </td>
                                <td class="px-4 py-3 text-sm font-bold text-gray-900">
                                    {{ $currency(item.total_cost) }}
                                </td>
                            </tr>
                            
                            <!-- Empty rows for printing -->
                            <tr v-for="n in Math.max(0, 10 - purchaseOrder.items.length)" :key="'empty-' + n">
                                <td class="px-4 py-3 border-r border-gray-300">&nbsp;</td>
                                <td class="px-4 py-3 border-r border-gray-300">&nbsp;</td>
                                <td class="px-4 py-3 border-r border-gray-300">&nbsp;</td>
                                <td class="px-4 py-3 border-r border-gray-300">&nbsp;</td>
                                <td class="px-4 py-3">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Totals Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <!-- Terms & Notes -->
                <div>
                    <div class="mb-6">
                        <h4 class="text-sm font-bold text-gray-900 mb-2">Shipping Address</h4>
                        <div class="text-sm text-gray-700 bg-gray-50 p-3 rounded border border-gray-300">
                            {{ purchaseOrder.shipping_address || purchaseOrder.supplier?.address || 'Same as supplier address' }}
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 mb-2">Notes</h4>
                        <div class="text-sm text-gray-700 bg-gray-50 p-3 rounded border border-gray-300 min-h-20">
                            {{ purchaseOrder.notes || 'No special instructions.' }}
                        </div>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div>
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-300">
                        <h4 class="text-lg font-bold text-gray-900 mb-4">Financial Summary</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-700">Subtotal:</span>
                                <span class="text-sm font-medium">{{ $currency(purchaseOrder.subtotal) }}</span>
                            </div>
                            <div v-if="purchaseOrder.shipping_cost > 0" class="flex justify-between">
                                <span class="text-sm text-gray-700">Shipping Cost:</span>
                                <span class="text-sm font-medium">{{ $currency(purchaseOrder.shipping_cost) }}</span>
                            </div>
                            <div v-if="purchaseOrder.tax_amount > 0" class="flex justify-between">
                                <span class="text-sm text-gray-700">Tax Amount:</span>
                                <span class="text-sm font-medium">{{ $currency(purchaseOrder.tax_amount) }}</span>
                            </div>
                            <div v-if="purchaseOrder.discount_amount > 0" class="flex justify-between">
                                <span class="text-sm text-gray-700">Discount:</span>
                                <span class="text-sm font-medium text-red-600">-{{ $currency(purchaseOrder.discount_amount) }}</span>
                            </div>
                            <div class="border-t border-gray-300 pt-2 mt-2">
                                <div class="flex justify-between font-bold">
                                    <span class="text-gray-900">TOTAL AMOUNT:</span>
                                    <span class="text-lg">{{ $currency(purchaseOrder.total_amount) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-gray-300">
                            <h5 class="text-sm font-bold text-gray-900 mb-2">Payment Information</h5>
                            <div class="text-sm text-gray-700">
                                <div>Payment Status: <span :class="getPaymentStatusClass(purchaseOrder.payment_status)" class="font-bold">
                                    {{ formatPaymentStatus(purchaseOrder.payment_status) }}
                                </span></div>
                                <div v-if="purchaseOrder.payment_method">Method: {{ formatPaymentMethod(purchaseOrder.payment_method) }}</div>
                                <div>Amount Paid: {{ $currency(purchaseOrder.amount_paid) }}</div>
                                <div>Balance Due: <span class="font-bold">{{ $currency(purchaseOrder.balance_due) }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-300 pt-8 mt-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="font-bold text-gray-900 mb-2">Prepared By</div>
                        <div class="h-16 border-b border-gray-300 mb-2"></div>
                        <div class="text-sm text-gray-700">{{ purchaseOrder.user?.name }}</div>
                        <div class="text-xs text-gray-500">Date: {{ formatDate(purchaseOrder.created_at) }}</div>
                    </div>
                    
                    <div class="text-center">
                        <div class="font-bold text-gray-900 mb-2">Authorized Signature</div>
                        <div class="h-16 border-b border-gray-300 mb-2"></div>
                        <div class="text-sm text-gray-700">Authorized Personnel</div>
                        <div class="text-xs text-gray-500">{{ company.name }}</div>
                    </div>
                    
                    <div class="text-center">
                        <div class="font-bold text-gray-900 mb-2">Supplier Acknowledgement</div>
                        <div class="h-16 border-b border-gray-300 mb-2"></div>
                        <div class="text-sm text-gray-700">Supplier Representative</div>
                        <div class="text-xs text-gray-500">{{ purchaseOrder.supplier?.name }}</div>
                    </div>
                </div>
                
                <div class="mt-8 text-center text-xs text-gray-500">
                    <p>This is a computer-generated purchase order. No signature is required.</p>
                    <p>PO Number: {{ purchaseOrder.po_number }} | Generated on: {{ formatDateTime(new Date()) }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
// Add at the top of your script section
import html2pdf from 'html2pdf.js';
const props = defineProps({
    purchaseOrder: Object,
    company: {
        type: Object,
        default: () => ({
            name: 'Your Company Name',
            address: '123 Business Street, Accra, Ghana',
            phone: '+233 123 456 789',
            email: 'info@company.com',
            logo: null
        })
    }
});

const savingPdf = ref(false);

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatDateTime = (date) => {
    return date.toLocaleDateString('en-GH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatStatus = (status) => {
    const statusMap = {
        'draft': 'Draft',
        'pending': 'Pending Approval',
        'approved': 'Approved',
        'ordered': 'Ordered',
        'partially_received': 'Partially Received',
        'received': 'Received',
        'cancelled': 'Cancelled',
    };
    return statusMap[status] || status;
};

const formatPaymentStatus = (status) => {
    const statusMap = {
        'pending': 'Pending',
        'partial': 'Partial Payment',
        'paid': 'Paid',
        'overdue': 'Overdue',
    };
    return statusMap[status] || status;
};

const formatPaymentMethod = (method) => {
    const methodMap = {
        'cash': 'Cash',
        'bank_transfer': 'Bank Transfer',
        'cheque': 'Cheque',
        'mobile_money': 'Mobile Money',
        'card': 'Credit/Debit Card',
    };
    return methodMap[method] || method;
};

const getPaymentStatusClass = (status) => {
    const classes = {
        'pending': 'text-yellow-600',
        'partial': 'text-blue-600',
        'paid': 'text-green-600',
        'overdue': 'text-red-600',
    };
    return classes[status] || 'text-gray-600';
};

const goBack = () => {
    router.get(route('purchase-orders.show', props.purchaseOrder.id));
};

const printDocument = () => {
    window.print();
};

const saveAsPDF = () => {
    savingPdf.value = true;
    
    // Using html2pdf library (you need to install it)
    if (typeof html2pdf !== 'undefined') {
        const element = document.querySelector('.bg-white.p-8');
        const opt = {
            margin: [10, 10, 10, 10],
            filename: `Purchase_Order_${props.purchaseOrder.po_number}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { 
                scale: 2,
                useCORS: true,
                letterRendering: true
            },
            jsPDF: { 
                unit: 'mm', 
                format: 'a4', 
                orientation: 'portrait' 
            }
        };
        
        html2pdf().from(element).set(opt).save().then(() => {
            savingPdf.value = false;
        });
    } else {
        // Fallback to print if html2pdf not available
        alert('PDF generation requires html2pdf library. Please install it or use the print function.');
        savingPdf.value = false;
    }
};

// Auto-print if coming from PDF route
if (window.location.search.includes('pdf=true')) {
    setTimeout(() => {
        window.print();
    }, 1000);
}
</script>

<style scoped>
/* Print Styles */
@media print {
    @page {
        margin: 0.5in;
    }
    
    body {
        background: white !important;
        color: black !important;
    }
    
    .print\:hidden {
        display: none !important;
    }
    
    .bg-white {
        background: white !important;
        box-shadow: none !important;
    }
    
    .border-gray-300 {
        border-color: #d1d5db !important;
    }
    
    .text-gray-900 {
        color: #111827 !important;
    }
    
    .text-gray-700 {
        color: #374151 !important;
    }
    
    .text-gray-600 {
        color: #4b5563 !important;
    }
    
    .bg-gray-50 {
        background-color: #f9fafb !important;
    }
    
    .bg-blue-50 {
        background-color: #eff6ff !important;
    }
    
    .bg-purple-50 {
        background-color: #faf5ff !important;
    }
    
    .bg-green-50 {
        background-color: #f0fdf4 !important;
    }
}

/* Ensure proper page breaks for printing */
.page-break {
    page-break-before: always;
}

.avoid-break {
    page-break-inside: avoid;
}
</style>