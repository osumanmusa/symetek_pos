<template>
  <AppLayout title="Product Inventory">
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
          <button
            @click="router.visit(route('products.index'))"
            class="text-gray-600 hover:text-gray-900"
            title="Back to Products"
          >
            <ArrowLeftIcon class="h-5 w-5" />
          </button>
          <div>
            <h2 class="text-2xl font-bold text-gray-800">
              Inventory: {{ product.name }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">
              Stock levels and transaction history
            </p>
          </div>
        </div>
        <div class="flex flex-wrap gap-2">
          <SecondaryButton @click="showStockTransfer = true">
            <ArrowRightIcon class="h-4 w-4 mr-2" />
            Transfer Stock
          </SecondaryButton>
          <PrimaryButton @click="showAdjustment = true">
            <AdjustmentsHorizontalIcon class="h-4 w-4 mr-2" />
            Adjust Stock
          </PrimaryButton>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Product Summary Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <!-- Product Info -->
              <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 h-16 w-16 bg-gray-100 rounded-lg flex items-center justify-center">
                  <CubeIcon class="h-8 w-8 text-gray-400" />
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">{{ product.name }}</h3>
                  <div class="text-sm text-gray-500 space-y-1">
                    <div>SKU: {{ product.sku || 'N/A' }}</div>
                    <div>Category: {{ product.category?.name || 'Uncategorized' }}</div>
                    <div>Unit: {{ product.unit || 'units' }}</div>
                  </div>
                </div>
              </div>

              <!-- Stock Summary -->
              <div class="border-l border-gray-200 pl-6">
                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">
                  Stock Summary
                </h4>
                <div class="space-y-2">
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Total Quantity:</span>
                    <span class="text-sm font-medium text-gray-900">
                      {{ formatNumber(totalQuantity) }} {{ product.unit || 'units' }}
                    </span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Available:</span>
                    <span :class="getStockLevelClass(totalAvailable)" class="text-sm font-medium">
                      {{ formatNumber(totalAvailable) }} {{ product.unit || 'units' }}
                    </span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Committed:</span>
                    <span class="text-sm text-gray-900">
                      {{ formatNumber(totalCommitted) }} {{ product.unit || 'units' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Status & Actions -->
              <div class="border-l border-gray-200 pl-6">
                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">
                  Status & Actions
                </h4>
                <div class="space-y-3">
                  <div>
                    <span :class="getStockStatusBadge" class="px-2 py-1 rounded-full text-xs font-medium">
                      {{ stockStatusText }}
                    </span>
                  </div>
                  <div class="text-sm text-gray-600">
                    Low Stock Alert: {{ product.low_stock_threshold || 10 }} {{ product.unit || 'units' }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Warehouses Stock Levels -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Stock by Warehouse</h3>
          </div>
          <div class="p-6">
            <div v-if="product.inventory_levels?.length > 0" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Warehouse
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      On Hand
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Committed
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Available
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Average Cost
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Total Value
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Status
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="level in product.inventory_levels" :key="level.id" class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">
                        {{ level.warehouse?.name || 'Unknown' }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ level.warehouse?.code || '' }}
                      </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                      {{ formatNumber(level.quantity_on_hand) }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                      {{ formatNumber(level.quantity_committed) }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                      <span :class="getStockLevelClass(level.quantity_available)" class="text-sm font-medium">
                        {{ formatNumber(level.quantity_available) }}
                      </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                      {{ formatCurrency(level.average_cost) }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                      {{ formatCurrency(level.total_value) }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                      <span :class="getLevelStatusBadgeClass(level)" class="px-2 py-1 rounded-full text-xs font-medium">
                        {{ level.stock_status_text }}
                      </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                      <div class="flex items-center space-x-2">
                        <button
                          @click="adjustStock(level)"
                          class="text-blue-600 hover:text-blue-900"
                          title="Adjust Stock"
                        >
                          <AdjustmentsHorizontalIcon class="h-4 w-4" />
                        </button>
                        <button
                          @click="transferStock(level)"
                          class="text-green-600 hover:text-green-900"
                          title="Transfer Stock"
                        >
                          <ArrowRightIcon class="h-4 w-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="text-center py-8">
              <BuildingStorefrontIcon class="mx-auto h-12 w-12 text-gray-400" />
              <h3 class="mt-2 text-sm font-medium text-gray-900">No stock recorded</h3>
              <p class="mt-1 text-sm text-gray-500">
                This product has no inventory in any warehouse.
              </p>
            </div>
          </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
            <a
              :href="route('inventory.transactions.index', { product_id: product.id })"
              class="text-sm text-indigo-600 hover:text-indigo-900"
            >
              View all transactions
            </a>
          </div>
          <div class="p-6">
            <div v-if="product.inventory_transactions?.length > 0" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Date
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Type
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Quantity
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Warehouse
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Reference
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      User
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="transaction in product.inventory_transactions" :key="transaction.id">
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                      {{ formatDate(transaction.transaction_date) }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                      <span :class="getTransactionTypeBadgeClass(transaction.transaction_type)" class="px-2 py-1 rounded-full text-xs font-medium">
                        {{ transactionTypes[transaction.transaction_type] || transaction.transaction_type }}
                      </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                      <span :class="getQuantityClass(transaction)">
                        {{ formatQuantity(transaction.quantity, transaction.transaction_type) }}
                      </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                      {{ transaction.warehouse?.name || '-' }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                      {{ transaction.reference_number || '-' }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                      {{ transaction.user?.name || 'System' }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="text-center py-8">
              <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-400" />
              <h3 class="mt-2 text-sm font-medium text-gray-900">No transactions</h3>
              <p class="mt-1 text-sm text-gray-500">
                No inventory transactions recorded for this product.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stock Adjustment Modal -->
    <Modal :show="showAdjustment" @close="showAdjustment = false">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">Adjust Stock Level</h3>
          <button @click="showAdjustment = false" class="text-gray-400 hover:text-gray-500">
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Warehouse
            </label>
            <select
              v-model="adjustmentForm.warehouse_id"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="">Select Warehouse</option>
              <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                {{ warehouse.name }} ({{ warehouse.code }})
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Adjustment Type
            </label>
            <select
              v-model="adjustmentForm.adjustment_type"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="add">Add Stock</option>
              <option value="remove">Remove Stock</option>
              <option value="set">Set Exact Quantity</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Quantity
            </label>
            <input
              v-model="adjustmentForm.quantity"
              type="number"
              min="0.01"
              step="0.01"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Reason
            </label>
            <textarea
              v-model="adjustmentForm.reason"
              rows="3"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              placeholder="Reason for adjustment..."
            />
          </div>
          <div class="flex justify-end space-x-3 pt-4">
            <SecondaryButton @click="showAdjustment = false">
              Cancel
            </SecondaryButton>
            <PrimaryButton @click="submitAdjustment" :disabled="adjustmentProcessing">
              Apply Adjustment
            </PrimaryButton>
          </div>
        </div>
      </div>
    </Modal>

    <!-- Stock Transfer Modal -->
    <Modal :show="showStockTransfer" @close="showStockTransfer = false">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">Transfer Stock</h3>
          <button @click="showStockTransfer = false" class="text-gray-400 hover:text-gray-500">
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                From Warehouse
              </label>
              <select
                v-model="transferForm.from_warehouse_id"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              >
                <option value="">Select Source</option>
                <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                  {{ warehouse.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                To Warehouse
              </label>
              <select
                v-model="transferForm.to_warehouse_id"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              >
                <option value="">Select Destination</option>
                <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                  {{ warehouse.name }}
                </option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Quantity to Transfer
            </label>
            <input
              v-model="transferForm.quantity"
              type="number"
              min="0.01"
              step="0.01"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              :placeholder="`Available: ${getAvailableStock(transferForm.from_warehouse_id)}`"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Notes
            </label>
            <textarea
              v-model="transferForm.notes"
              rows="2"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              placeholder="Transfer notes..."
            />
          </div>
          <div class="flex justify-end space-x-3 pt-4">
            <SecondaryButton @click="showStockTransfer = false">
              Cancel
            </SecondaryButton>
            <PrimaryButton @click="submitTransfer" :disabled="transferProcessing">
              Transfer Stock
            </PrimaryButton>
          </div>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import Modal from '@/Components/Modal.vue'
import { 
  ArrowLeftIcon,
  CubeIcon,
  BuildingStorefrontIcon,
  DocumentTextIcon,
  AdjustmentsHorizontalIcon,
  ArrowRightIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'
import { ref, computed, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  product: Object,
  warehouses: Array,
})

// State
const showAdjustment = ref(false)
const showStockTransfer = ref(false)
const adjustmentProcessing = ref(false)
const transferProcessing = ref(false)

// Forms
const adjustmentForm = reactive({
  warehouse_id: '',
  adjustment_type: 'add',
  quantity: 0,
  reason: '',
})

const transferForm = reactive({
  from_warehouse_id: '',
  to_warehouse_id: '',
  quantity: 0,
  notes: '',
})

// Transaction types (you might want to pass this from controller)
const transactionTypes = {
  purchase: 'Purchase',
  sale: 'Sale',
  adjustment: 'Adjustment',
  return: 'Return',
  damage: 'Damage/Loss',
  transfer_in: 'Transfer In',
  transfer_out: 'Transfer Out',
  production: 'Production',
  consumption: 'Consumption',
}

// Computed properties
const totalQuantity = computed(() => {
  return props.product.inventory_levels?.reduce((sum, level) => sum + level.quantity_on_hand, 0) || 0
})

const totalCommitted = computed(() => {
  return props.product.inventory_levels?.reduce((sum, level) => sum + level.quantity_committed, 0) || 0
})

const totalAvailable = computed(() => {
  return totalQuantity.value - totalCommitted.value
})

const stockStatusText = computed(() => {
  if (totalAvailable.value <= 0) return 'Out of Stock'
  if (totalAvailable.value <= props.product.low_stock_threshold) return 'Low Stock'
  return 'In Stock'
})

const getStockStatusBadge = computed(() => {
  if (totalAvailable.value <= 0) {
    return 'bg-red-100 text-red-800'
  }
  if (totalAvailable.value <= props.product.low_stock_threshold) {
    return 'bg-yellow-100 text-yellow-800'
  }
  return 'bg-green-100 text-green-800'
})

// Helper functions
const formatNumber = (num) => {
  return parseFloat(num || 0).toFixed(2)
}

const formatCurrency = (amount) => {
  if (!amount) return '₵0.00'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'GHS',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
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

const getStockLevelClass = (available) => {
  if (available <= 0) return 'text-red-600'
  if (available <= props.product.low_stock_threshold) return 'text-yellow-600'
  return 'text-green-600'
}

const getLevelStatusBadgeClass = (level) => {
  if (level.quantity_available <= 0) {
    return 'bg-red-100 text-red-800'
  }
  if (level.quantity_available <= props.product.low_stock_threshold) {
    return 'bg-yellow-100 text-yellow-800'
  }
  return 'bg-green-100 text-green-800'
}

const getTransactionTypeBadgeClass = (type) => {
  const classes = {
    'purchase': 'bg-blue-100 text-blue-800',
    'sale': 'bg-green-100 text-green-800',
    'adjustment': 'bg-yellow-100 text-yellow-800',
    'return': 'bg-purple-100 text-purple-800',
    'damage': 'bg-red-100 text-red-800',
    'transfer_in': 'bg-indigo-100 text-indigo-800',
    'transfer_out': 'bg-indigo-100 text-indigo-800',
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getQuantityClass = (transaction) => {
  const isIncrement = ['purchase', 'return', 'transfer_in', 'production'].includes(transaction.transaction_type)
  const isAdjustment = transaction.transaction_type === 'adjustment'
  
  if (isAdjustment) {
    return transaction.quantity > 0 ? 'text-green-600' : 'text-red-600'
  }
  
  return isIncrement ? 'text-green-600' : 'text-red-600'
}

const getAvailableStock = (warehouseId) => {
  if (!warehouseId) return 0
  const level = props.product.inventory_levels?.find(l => l.warehouse_id == warehouseId)
  return level ? level.quantity_available : 0
}

// Actions
const adjustStock = (level) => {
  adjustmentForm.warehouse_id = level.warehouse_id
  adjustmentForm.quantity = level.quantity_available
  showAdjustment.value = true
}

const transferStock = (level) => {
  transferForm.from_warehouse_id = level.warehouse_id
  transferForm.quantity = Math.min(level.quantity_available, 1)
  showStockTransfer.value = true
}



const submitAdjustment = async () => {
  adjustmentProcessing.value = true
  try {
    // Ensure all fields are sent, even if empty
    const adjustmentData = {
      product_id: props.product.id,
      warehouse_id: adjustmentForm.warehouse_id,
      adjustment_type: adjustmentForm.adjustment_type,
      quantity: parseFloat(adjustmentForm.quantity) || 0,
      reason: adjustmentForm.reason || '',
      notes: adjustmentForm.notes || '', // Always send notes, even if empty
      reference_number: `ADJ-${Date.now()}`,
    }
    
    const response = await axios.post(route('inventory.adjustment.store'), adjustmentData)
    
    if (response.data.success) {
      showAdjustment.value = false
      // Reset form
      Object.assign(adjustmentForm, {
        warehouse_id: '',
        adjustment_type: 'add',
        quantity: 0,
        reason: '',
        notes: '',
      })
      // Reload page data
      router.reload()
    } else {
      alert('Adjustment failed: ' + response.data.message)
    }
  } catch (error) {
    console.error('Adjustment failed:', error)
    alert('Error: ' + (error.response?.data?.message || error.message))
  } finally {
    adjustmentProcessing.value = false
  }
}

const submitTransfer = async () => {
  transferProcessing.value = true
  try {
    const response = await axios.post(route('inventory.transfer.store'), {
      product_id: props.product.id,
      ...transferForm,
    })
    
    if (response.data.success) {
      showStockTransfer.value = false
      // Reset form
      Object.assign(transferForm, {
        from_warehouse_id: '',
        to_warehouse_id: '',
        quantity: 0,
        notes: '',
      })
      // Reload page data
      router.reload()
    } else {
      alert('Transfer failed: ' + response.data.message)
    }
  } catch (error) {
    console.error('Transfer failed:', error)
    alert('Error: ' + (error.response?.data?.message || error.message))
  } finally {
    transferProcessing.value = false
  }
}
</script>
