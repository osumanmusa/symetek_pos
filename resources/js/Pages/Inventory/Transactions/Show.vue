[file name]: resources/js/Pages/Inventory/Transactions/Show.vue
[file content begin]
<template>
  <AppLayout title="Transaction Details">
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
          <button
            @click="router.visit(route('inventory-transactions.index'))"
            class="text-gray-600 hover:text-gray-900"
            title="Back to Transactions"
          >
            <ArrowLeftIcon class="h-5 w-5" />
          </button>
          <div>
            <h2 class="text-2xl font-bold text-gray-800">
              Transaction Details
            </h2>
            <p class="mt-1 text-sm text-gray-600">
              View complete transaction information
            </p>
          </div>
        </div>
        <div class="flex flex-wrap gap-2">
          <SecondaryButton @click="printTransaction">
            <PrinterIcon class="h-4 w-4 mr-2" />
            Print
          </SecondaryButton>
          <PrimaryButton @click="createSimilarTransaction">
            <DocumentDuplicateIcon class="h-4 w-4 mr-2" />
            Duplicate
          </PrimaryButton>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Transaction Header Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
              <div>
                <div class="flex items-center space-x-3">
                  <div :class="getTransactionTypeIconClass(transaction.transaction_type)" class="flex-shrink-0">
                    <component :is="getTransactionTypeIcon(transaction.transaction_type)" class="h-6 w-6" />
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                      {{ transactionTypes[transaction.transaction_type] || transaction.transaction_type }}
                    </h3>
                    <p class="text-sm text-gray-500">
                      Transaction #{{ transaction.id }}
                    </p>
                  </div>
                </div>
              </div>
              <div class="mt-3 sm:mt-0">
                <span :class="getTransactionStatusClass(transaction)" class="px-3 py-1 rounded-full text-sm font-medium">
                  {{ getTransactionStatusText(transaction) }}
                </span>
              </div>
            </div>
          </div>
          
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <!-- Basic Information -->
              <div>
                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">
                  Basic Information
                </h4>
                <dl class="space-y-3">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Transaction Date</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ formatDateTime(transaction.transaction_date) }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Reference Number</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ transaction.reference_number || 'Not provided' }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Created By</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ transaction.user?.name || 'System' }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Created At</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ formatDateTime(transaction.created_at) }}
                    </dd>
                  </div>
                </dl>
              </div>

              <!-- Product Information -->
              <div>
                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">
                  Product Information
                </h4>
                <dl class="space-y-3">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Product</dt>
                    <dd class="mt-1">
                      <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-md flex items-center justify-center">
                          <CubeIcon class="h-6 w-6 text-gray-400" />
                        </div>
                        <div>
                          <div class="text-sm font-medium text-gray-900">
                            {{ transaction.product?.name || 'Unknown Product' }}
                          </div>
                          <div class="text-sm text-gray-500">
                            SKU: {{ transaction.product?.sku || 'N/A' }}
                          </div>
                        </div>
                      </div>
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Category</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ transaction.product?.category?.name || 'Uncategorized' }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Unit of Measure</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      {{ transaction.product?.unit || 'units' }}
                    </dd>
                  </div>
                </dl>
              </div>

              <!-- Warehouse & Quantity -->
              <div>
                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">
                  Location & Quantity
                </h4>
                <dl class="space-y-3">
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Warehouse</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                      <div class="flex items-center space-x-2">
                        <BuildingStorefrontIcon class="h-4 w-4 text-gray-400" />
                        <span>{{ transaction.warehouse?.name || 'Default Warehouse' }}</span>
                      </div>
                      <div class="text-xs text-gray-500 ml-6">
                        {{ transaction.warehouse?.code || '' }}
                      </div>
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500">Quantity</dt>
                    <dd class="mt-1">
                      <span :class="getQuantityBadgeClass(transaction)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                        {{ formatQuantity(transaction.quantity, transaction.transaction_type) }}
                        <span class="ml-2 text-xs">
                          {{ transaction.product?.unit || 'units' }}
                        </span>
                      </span>
                    </dd>
                  </div>
                  <div v-if="transaction.metadata">
                    <dt class="text-sm font-medium text-gray-500">Additional Data</dt>
                    <dd class="mt-1">
                      <button
                        @click="showMetadata = !showMetadata"
                        class="text-sm text-indigo-600 hover:text-indigo-900 flex items-center"
                      >
                        <InformationCircleIcon class="h-4 w-4 mr-1" />
                        {{ showMetadata ? 'Hide' : 'View' }} metadata
                      </button>
                    </dd>
                  </div>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Financial Details Card -->
        <div v-if="showCostFields" class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
          <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-white border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
              <CurrencyDollarIcon class="h-5 w-5 mr-2 text-blue-500" />
              Financial Details
            </h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <!-- Cost Breakdown -->
              <div>
                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">
                  Cost Breakdown
                </h4>
                <div class="space-y-4">
                  <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Unit Cost</span>
                    <span class="text-sm font-medium text-gray-900">
                      {{ formatCurrency(transaction.unit_cost) }}
                    </span>
                  </div>
                  <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Quantity</span>
                    <span class="text-sm font-medium text-gray-900">
                      {{ transaction.quantity }}
                    </span>
                  </div>
                  <div class="flex justify-between items-center py-2 border-b border-gray-200">
                    <span class="text-sm font-medium text-gray-700">Total Cost</span>
                    <span class="text-lg font-bold text-blue-600">
                      {{ formatCurrency(transaction.total_cost) }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Impact on Inventory -->
              <div>
                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">
                  Inventory Impact
                </h4>
                <div class="space-y-4">
                  <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                      <div :class="getImpactIconClass(transaction)" class="flex-shrink-0">
                        <component :is="getImpactIcon(transaction)" class="h-5 w-5" />
                      </div>
                      <div>
                        <div class="text-sm font-medium text-gray-900">
                          {{ getImpactText(transaction) }}
                        </div>
                        <div class="text-xs text-gray-500">
                          Stock level change
                        </div>
                      </div>
                    </div>
                    <div :class="getImpactValueClass(transaction)" class="text-sm font-bold">
                      {{ formatQuantity(transaction.quantity, transaction.transaction_type) }}
                    </div>
                  </div>
                  
                  <!-- Before/After if available -->
                  <div v-if="transaction.previous_quantity !== undefined" class="grid grid-cols-2 gap-4">
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                      <div class="text-xs text-gray-500">Before</div>
                      <div class="text-sm font-medium text-gray-900">
                        {{ transaction.previous_quantity }}
                      </div>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                      <div class="text-xs text-gray-500">After</div>
                      <div class="text-sm font-medium text-gray-900">
                        {{ transaction.new_quantity }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes & Metadata -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Notes Card -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <ChatBubbleLeftRightIcon class="h-5 w-5 mr-2 text-gray-500" />
                Transaction Notes
              </h3>
            </div>
            <div class="p-6">
              <div v-if="transaction.notes" class="prose prose-sm max-w-none">
                <p class="text-gray-700 whitespace-pre-wrap">{{ transaction.notes }}</p>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                <ChatBubbleLeftRightIcon class="mx-auto h-12 w-12 text-gray-300" />
                <p class="mt-2 text-sm">No notes provided for this transaction</p>
              </div>
            </div>
          </div>

          <!-- Metadata Card -->
          <div v-if="transaction.metadata" class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <InformationCircleIcon class="h-5 w-5 mr-2 text-gray-500" />
                Additional Information
              </h3>
            </div>
            <div class="p-6">
              <div v-if="showMetadata && transaction.metadata" class="space-y-3">
                <div v-for="(value, key) in transaction.metadata" :key="key" class="flex justify-between items-start py-2 border-b border-gray-100 last:border-0">
                  <span class="text-sm font-medium text-gray-600 capitalize">{{ key.replace(/_/g, ' ') }}</span>
                  <span class="text-sm text-gray-900 text-right max-w-xs break-words">
                    {{ typeof value === 'object' ? JSON.stringify(value) : value }}
                  </span>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                <InformationCircleIcon class="mx-auto h-12 w-12 text-gray-300" />
                <p class="mt-2 text-sm">Metadata available</p>
                <button
                  @click="showMetadata = true"
                  class="mt-4 text-sm text-indigo-600 hover:text-indigo-900"
                >
                  Click to view metadata
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Action History -->
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
              <ClockIcon class="h-5 w-5 mr-2 text-gray-500" />
              Transaction Timeline
            </h3>
          </div>
          <div class="p-6">
            <div class="space-y-6">
              <!-- Created -->
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center h-8 w-8 rounded-full bg-green-100">
                    <PlusIcon class="h-4 w-4 text-green-600" />
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">Transaction Created</div>
                  <div class="text-sm text-gray-500">
                    {{ formatDateTime(transaction.created_at) }}
                  </div>
                  <div class="mt-1 text-sm text-gray-700">
                    Transaction was recorded in the system
                  </div>
                </div>
              </div>

              <!-- Updated -->
              <div v-if="transaction.updated_at !== transaction.created_at" class="flex items-start">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100">
                    <PencilIcon class="h-4 w-4 text-blue-600" />
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">Last Updated</div>
                  <div class="text-sm text-gray-500">
                    {{ formatDateTime(transaction.updated_at) }}
                  </div>
                  <div class="mt-1 text-sm text-gray-700">
                    Transaction information was last modified
                  </div>
                </div>
              </div>

              <!-- Inventory Impact -->
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <div :class="getImpactIconClass(transaction, true)" class="flex items-center justify-center h-8 w-8 rounded-full">
                    <component :is="getImpactIcon(transaction)" class="h-4 w-4" />
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">Inventory Updated</div>
                  <div class="text-sm text-gray-500">
                    {{ formatDateTime(transaction.transaction_date) }}
                  </div>
                  <div class="mt-1 text-sm text-gray-700">
                    Stock levels were {{ getImpactText(transaction).toLowerCase() }} by {{ Math.abs(transaction.quantity) }} {{ transaction.product?.unit || 'units' }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-6 flex flex-wrap justify-end gap-3">
          <SecondaryButton @click="router.visit(route('products.inventory', transaction.product_id))" v-if="transaction.product_id">
            <CubeIcon class="h-4 w-4 mr-2" />
            View Product Inventory
          </SecondaryButton>
          <SecondaryButton @click="router.visit(route('inventory-transactions.index'))">
            <ArrowLeftIcon class="h-4 w-4 mr-2" />
            Back to Transactions
          </SecondaryButton>
        </div>
      </div>
    </div>

    <!-- Print Styles (hidden for screen) -->
    <div ref="printContent" class="hidden print:block">
      <div class="p-8">
        <div class="text-center mb-8">
          <h1 class="text-2xl font-bold text-gray-900">Inventory Transaction</h1>
          <p class="text-gray-600">Transaction #{{ transaction.id }}</p>
        </div>
        
        <div class="mb-8">
          <table class="w-full border-collapse">
            <tr>
              <td class="border p-2 font-semibold">Date:</td>
              <td class="border p-2">{{ formatDateTime(transaction.transaction_date) }}</td>
              <td class="border p-2 font-semibold">Type:</td>
              <td class="border p-2">{{ transactionTypes[transaction.transaction_type] }}</td>
            </tr>
            <tr>
              <td class="border p-2 font-semibold">Product:</td>
              <td class="border p-2">{{ transaction.product?.name }}</td>
              <td class="border p-2 font-semibold">SKU:</td>
              <td class="border p-2">{{ transaction.product?.sku }}</td>
            </tr>
            <tr>
              <td class="border p-2 font-semibold">Quantity:</td>
              <td class="border p-2">{{ transaction.quantity }} {{ transaction.product?.unit }}</td>
              <td class="border p-2 font-semibold">Warehouse:</td>
              <td class="border p-2">{{ transaction.warehouse?.name }}</td>
            </tr>
            <tr v-if="transaction.unit_cost">
              <td class="border p-2 font-semibold">Unit Cost:</td>
              <td class="border p-2">{{ formatCurrency(transaction.unit_cost) }}</td>
              <td class="border p-2 font-semibold">Total Cost:</td>
              <td class="border p-2">{{ formatCurrency(transaction.total_cost) }}</td>
            </tr>
          </table>
        </div>
        
        <div v-if="transaction.notes" class="mb-8">
          <h3 class="text-lg font-bold mb-2">Notes:</h3>
          <p class="text-gray-700">{{ transaction.notes }}</p>
        </div>
        
        <div class="text-sm text-gray-500 mt-12">
          Printed on {{ new Date().toLocaleDateString() }} at {{ new Date().toLocaleTimeString() }}
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import { 
  ArrowLeftIcon,
  PrinterIcon,
  DocumentDuplicateIcon,
  CubeIcon,
  BuildingStorefrontIcon,
  CurrencyDollarIcon,
  InformationCircleIcon,
  ChatBubbleLeftRightIcon,
  ClockIcon,
  PlusIcon,
  PencilIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  ShoppingCartIcon,
  TruckIcon,
  AdjustmentsHorizontalIcon,
  ExclamationTriangleIcon,
  ArrowRightIcon,
  WrenchScrewdriverIcon,
  FireIcon
} from '@heroicons/vue/24/outline'
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  transaction: Object,
  transactionTypes: Object,
})

const showMetadata = ref(false)
const printContent = ref(null)

// Computed properties
const showCostFields = computed(() => {
  return ['purchase', 'return', 'production'].includes(props.transaction.transaction_type)
})

// Formatting functions
const formatCurrency = (amount) => {
  if (!amount) return '₵0.00'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'GHS',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount)
}

const formatDateTime = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatQuantity = (quantity, type) => {
  const isIncrement = ['purchase', 'return', 'transfer_in', 'production'].includes(type)
  const isAdjustment = type === 'adjustment'
  
  if (isAdjustment) {
    return quantity > 0 ? `+${quantity}` : quantity.toString()
  }
  
  return isIncrement ? `+${quantity}` : `-${quantity}`
}

// Styling functions
const getTransactionTypeIcon = (type) => {
  const icons = {
    'purchase': ShoppingCartIcon,
    'sale': CurrencyDollarIcon,
    'adjustment': AdjustmentsHorizontalIcon,
    'return': ArrowLeftIcon,
    'damage': ExclamationTriangleIcon,
    'transfer_in': ArrowRightIcon,
    'transfer_out': ArrowRightIcon,
    'production': WrenchScrewdriverIcon,
    'consumption': FireIcon,
  }
  return icons[type] || InformationCircleIcon
}

const getTransactionTypeIconClass = (type) => {
  const classes = {
    'purchase': 'p-2 rounded-lg bg-blue-100 text-blue-600',
    'sale': 'p-2 rounded-lg bg-green-100 text-green-600',
    'adjustment': 'p-2 rounded-lg bg-yellow-100 text-yellow-600',
    'return': 'p-2 rounded-lg bg-purple-100 text-purple-600',
    'damage': 'p-2 rounded-lg bg-red-100 text-red-600',
    'transfer_in': 'p-2 rounded-lg bg-indigo-100 text-indigo-600',
    'transfer_out': 'p-2 rounded-lg bg-indigo-100 text-indigo-600',
    'production': 'p-2 rounded-lg bg-teal-100 text-teal-600',
    'consumption': 'p-2 rounded-lg bg-orange-100 text-orange-600',
  }
  return classes[type] || 'p-2 rounded-lg bg-gray-100 text-gray-600'
}

const getTransactionStatusClass = (transaction) => {
  return 'bg-green-100 text-green-800' // All transactions are considered "completed"
}

const getTransactionStatusText = (transaction) => {
  return 'Completed'
}

const getQuantityBadgeClass = (transaction) => {
  const isIncrement = ['purchase', 'return', 'transfer_in', 'production'].includes(transaction.transaction_type)
  const isAdjustment = transaction.transaction_type === 'adjustment'
  
  if (isAdjustment) {
    return transaction.quantity > 0 
      ? 'bg-green-100 text-green-800' 
      : 'bg-red-100 text-red-800'
  }
  
  return isIncrement 
    ? 'bg-green-100 text-green-800' 
    : 'bg-red-100 text-red-800'
}

const getImpactIcon = (transaction) => {
  const isIncrement = ['purchase', 'return', 'transfer_in', 'production'].includes(transaction.transaction_type)
  const isAdjustment = transaction.transaction_type === 'adjustment'
  
  if (isAdjustment) {
    return transaction.quantity > 0 ? ArrowUpIcon : ArrowDownIcon
  }
  
  return isIncrement ? ArrowUpIcon : ArrowDownIcon
}

const getImpactIconClass = (transaction, isBg = false) => {
  const isIncrement = ['purchase', 'return', 'transfer_in', 'production'].includes(transaction.transaction_type)
  const isAdjustment = transaction.transaction_type === 'adjustment'
  
  if (isAdjustment) {
    return transaction.quantity > 0 
      ? isBg ? 'bg-green-100 text-green-600' : 'text-green-600'
      : isBg ? 'bg-red-100 text-red-600' : 'text-red-600'
  }
  
  return isIncrement 
    ? isBg ? 'bg-green-100 text-green-600' : 'text-green-600'
    : isBg ? 'bg-red-100 text-red-600' : 'text-red-600'
}

const getImpactText = (transaction) => {
  const isIncrement = ['purchase', 'return', 'transfer_in', 'production'].includes(transaction.transaction_type)
  const isAdjustment = transaction.transaction_type === 'adjustment'
  
  if (isAdjustment) {
    return transaction.quantity > 0 ? 'Increased' : 'Decreased'
  }
  
  return isIncrement ? 'Increased' : 'Decreased'
}

const getImpactValueClass = (transaction) => {
  const isIncrement = ['purchase', 'return', 'transfer_in', 'production'].includes(transaction.transaction_type)
  const isAdjustment = transaction.transaction_type === 'adjustment'
  
  if (isAdjustment) {
    return transaction.quantity > 0 ? 'text-green-600' : 'text-red-600'
  }
  
  return isIncrement ? 'text-green-600' : 'text-red-600'
}

// Actions
const printTransaction = () => {
  const printWindow = window.open('', '_blank')
  printWindow.document.write(`
    <html>
      <head>
        <title>Transaction #${props.transaction.id}</title>
        <style>
          body { font-family: Arial, sans-serif; margin: 20px; }
          table { width: 100%; border-collapse: collapse; margin: 20px 0; }
          td, th { border: 1px solid #ddd; padding: 8px; }
          .header { text-align: center; margin-bottom: 30px; }
          .footer { margin-top: 50px; font-size: 12px; color: #666; }
        </style>
      </head>
      <body>
        ${printContent.value.innerHTML}
      </body>
    </html>
  `)
  printWindow.document.close()
  printWindow.focus()
  printWindow.print()
  printWindow.close()
}

const createSimilarTransaction = () => {
  // Navigate to create page with prefilled data
  router.visit(route('inventory-transactions.create'), {
    data: {
      transaction_type: props.transaction.transaction_type,
      product_id: props.transaction.product_id,
      warehouse_id: props.transaction.warehouse_id,
      reference_number: props.transaction.reference_number,
    }
  })
}
</script>
[file content end]