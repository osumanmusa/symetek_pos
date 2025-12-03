<template>
  <AppLayout title="Inventory Transactions">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Inventory Transactions
        </h2>
        <div class="flex space-x-2">
          <SecondaryButton @click="showFilters = !showFilters">
            <FilterIcon class="h-4 w-4 mr-2" />
            Filters
          </SecondaryButton>
          <PrimaryButton @click="createTransaction">
            <PlusIcon class="h-4 w-4 mr-2" />
            New Transaction
          </PrimaryButton>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Filters Panel -->
        <div
          v-show="showFilters"
          class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6"
        >
          <div class="p-6 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <!-- Product Search -->
              <div>
                <InputLabel for="product_search" value="Product" />
                <TextInput
                  id="product_search"
                  v-model="filters.product_search"
                  type="text"
                  class="mt-1 block w-full"
                  placeholder="Product name or SKU"
                />
              </div>

              <!-- Transaction Type -->
              <div>
                <InputLabel for="transaction_type" value="Type" />
                <select
                  id="transaction_type"
                  v-model="filters.transaction_type"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                >
                  <option value="">All Types</option>
                  <option v-for="(label, value) in transactionTypes" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>
              </div>

              <!-- Date Range -->
              <div>
                <InputLabel for="start_date" value="Start Date" />
                <TextInput
                  id="start_date"
                  v-model="filters.start_date"
                  type="date"
                  class="mt-1 block w-full"
                />
              </div>

              <div>
                <InputLabel for="end_date" value="End Date" />
                <TextInput
                  id="end_date"
                  v-model="filters.end_date"
                  type="date"
                  class="mt-1 block w-full"
                />
              </div>
            </div>

            <div class="flex justify-end mt-4">
              <SecondaryButton @click="resetFilters">
                Clear Filters
              </SecondaryButton>
              <PrimaryButton @click="applyFilters" class="ml-2">
                Apply Filters
              </PrimaryButton>
            </div>
          </div>
        </div>

        <!-- Transactions List -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Date
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Type
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Product
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Warehouse
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Quantity
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Unit Cost
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      User
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Reference
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="transaction in transactions.data" :key="transaction.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatDate(transaction.transaction_date) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        :class="getTransactionTypeClass(transaction.transaction_type)"
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                      >
                        {{ transactionTypes[transaction.transaction_type] || transaction.transaction_type }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <div class="flex items-center">
                        <div>
                          <div class="font-medium">{{ transaction.product?.name }}</div>
                          <div class="text-gray-500 text-xs">{{ transaction.product?.sku }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ transaction.warehouse?.name || '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <span :class="getQuantityClass(transaction)">
                        {{ formatQuantity(transaction.quantity, transaction.transaction_type) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ transaction.unit_cost ? formatCurrency(transaction.unit_cost) : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ transaction.user?.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ transaction.reference_number || '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <button
                        @click="viewTransaction(transaction)"
                        class="text-indigo-600 hover:text-indigo-900"
                        title="View Details"
                      >
                        <EyeIcon class="h-5 w-5" />
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
              
              <!-- Empty State -->
              <div
                v-if="transactions.data.length === 0"
                class="text-center py-12"
              >
                <DocumentIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No transactions</h3>
                <p class="mt-1 text-sm text-gray-500">
                  No inventory transactions found.
                </p>
              </div>
            </div>

            <!-- Pagination -->
            <Pagination
              v-if="transactions.data.length > 0"
              class="mt-6"
              :links="transactions.links"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import Pagination from '@/Components/Pagination.vue'
import { 
  PlusIcon,
  FilterIcon,
  EyeIcon,
  DocumentIcon 
} from '@heroicons/vue/24/outline'
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  transactions: Object,
  transactionTypes: Object,
  filters: Object,
})

const showFilters = ref(false)

const filters = ref({
  product_search: props.filters.search || '',
  transaction_type: props.filters.type || '',
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
})

const getTransactionTypeClass = (type) => {
  const classes = {
    'purchase': 'bg-blue-100 text-blue-800',
    'sale': 'bg-green-100 text-green-800',
    'adjustment': 'bg-yellow-100 text-yellow-800',
    'return': 'bg-purple-100 text-purple-800',
    'damage': 'bg-red-100 text-red-800',
    'transfer_in': 'bg-indigo-100 text-indigo-800',
    'transfer_out': 'bg-indigo-100 text-indigo-800',
    'production': 'bg-teal-100 text-teal-800',
    'consumption': 'bg-orange-100 text-orange-800',
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

const formatQuantity = (quantity, type) => {
  const isIncrement = ['purchase', 'return', 'transfer_in', 'production'].includes(type)
  const isAdjustment = type === 'adjustment'
  
  if (isAdjustment) {
    return quantity > 0 ? `+${quantity}` : quantity.toString()
  }
  
  return isIncrement ? `+${quantity}` : `-${quantity}`
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'GHS',
  }).format(amount)
}

const applyFilters = () => {
  router.get(route('inventory.transactions.index'), filters.value, {
    preserveState: true,
    replace: true,
  })
}

const resetFilters = () => {
  filters.value = {
    product_search: '',
    transaction_type: '',
    start_date: '',
    end_date: '',
  }
  applyFilters()
}

const createTransaction = () => {
  router.visit(route('inventory.transactions.create'))
}

const viewTransaction = (transaction) => {
  router.visit(route('inventory.transactions.show', transaction.id))
}
</script>
