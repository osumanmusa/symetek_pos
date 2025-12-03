<template>
  <AppLayout title="Inventory Dashboard">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Inventory Dashboard
        </h2>
        <div class="flex space-x-2">
          <PrimaryButton @click="newTransaction">
            <PlusIcon class="h-4 w-4 mr-2" />
            New Transaction
          </PrimaryButton>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Total Products -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <CubeIcon class="h-6 w-6 text-blue-500" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Total Products
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ stats.total_products || 0 }}
                    </dd>
                  </dl>
                </div>
              </div>
              <div class="mt-4">
                <a
                  :href="route('products.index')"
                  class="text-sm font-medium text-blue-600 hover:text-blue-500"
                >
                  View all products
                </a>
              </div>
            </div>
          </div>

          <!-- Total Value -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <CurrencyDollarIcon class="h-6 w-6 text-green-500" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Total Inventory Value
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ formatCurrency(stats.total_inventory_value || 0) }}
                    </dd>
                  </dl>
                </div>
              </div>
              <div class="mt-4">
                <a
                  :href="route('inventory.levels.index')"
                  class="text-sm font-medium text-blue-600 hover:text-blue-500"
                >
                  View stock report
                </a>
              </div>
            </div>
          </div>

          <!-- Low Stock -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <ExclamationTriangleIcon class="h-6 w-6 text-yellow-500" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Low Stock Items
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ stats.low_stock_items || 0 }}
                    </dd>
                  </dl>
                </div>
              </div>
              <div class="mt-4">
                <a
                  :href="route('inventory.levels.index', { stock_status: 'low_stock' })"
                  class="text-sm font-medium text-blue-600 hover:text-blue-500"
                >
                  View low stock
                </a>
              </div>
            </div>
          </div>

          <!-- Out of Stock -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <XCircleIcon class="h-6 w-6 text-red-500" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Out of Stock
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ stats.out_of_stock_items || 0 }}
                    </dd>
                  </dl>
                </div>
              </div>
              <div class="mt-4">
                <a
                  :href="route('inventory.levels.index', { stock_status: 'out_of_stock' })"
                  class="text-sm font-medium text-blue-600 hover:text-blue-500"
                >
                  View out of stock
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions Grid -->
        <div class="mb-8">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Stock Levels -->
            <a
              :href="route('inventory.levels.index')"
              class="bg-white overflow-hidden shadow rounded-lg p-6 hover:shadow-md transition-shadow duration-200"
            >
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                  <ChartBarIcon class="h-6 w-6 text-blue-600" />
                </div>
                <div class="ml-4">
                  <h4 class="text-sm font-medium text-gray-900">Stock Levels</h4>
                  <p class="text-sm text-gray-500">View current stock across warehouses</p>
                </div>
              </div>
            </a>

            <!-- Transactions -->
            <a
              :href="route('inventory.transactions.index')"
              class="bg-white overflow-hidden shadow rounded-lg p-6 hover:shadow-md transition-shadow duration-200"
            >
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                  <DocumentTextIcon class="h-6 w-6 text-green-600" />
                </div>
                <div class="ml-4">
                  <h4 class="text-sm font-medium text-gray-900">Transactions</h4>
                  <p class="text-sm text-gray-500">View all inventory transactions</p>
                </div>
              </div>
            </a>

            <!-- Warehouses -->
            <a
              :href="route('warehouses.index')"
              class="bg-white overflow-hidden shadow rounded-lg p-6 hover:shadow-md transition-shadow duration-200"
            >
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                  <BuildingStorefrontIcon class="h-6 w-6 text-purple-600" />
                </div>
                <div class="ml-4">
                  <h4 class="text-sm font-medium text-gray-900">Warehouses</h4>
                  <p class="text-sm text-gray-500">Manage your warehouses</p>
                </div>
              </div>
            </a>

            <!-- Stock Transfer -->
            <a
              :href="route('inventory.transactions.create')"
              class="bg-white overflow-hidden shadow rounded-lg p-6 hover:shadow-md transition-shadow duration-200"
            >
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                  <ArrowRightLeftIcon class="h-6 w-6 text-yellow-600" />
                </div>
                <div class="ml-4">
                  <h4 class="text-sm font-medium text-gray-900">New Transaction</h4>
                  <p class="text-sm text-gray-500">Record stock movement</p>
                </div>
              </div>
            </a>
          </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
          <div class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
              <a
                :href="route('inventory.transactions.index')"
                class="text-sm text-blue-600 hover:text-blue-500"
              >
                View all
              </a>
            </div>
            <div class="overflow-x-auto">
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
                      Product
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Quantity
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Warehouse
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="transaction in recentTransactions" :key="transaction.id">
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                      {{ formatDate(transaction.transaction_date) }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                      <span
                        :class="getTransactionTypeClass(transaction.transaction_type)"
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                      >
                        {{ transactionTypes[transaction.transaction_type] || transaction.transaction_type }}
                      </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                      {{ transaction.product?.name }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                      <span :class="getQuantityClass(transaction)">
                        {{ formatQuantity(transaction.quantity, transaction.transaction_type) }}
                      </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                      {{ transaction.warehouse?.name || '-' }}
                    </td>
                  </tr>
                </tbody>
              </table>
              <div
                v-if="recentTransactions.length === 0"
                class="text-center py-8 text-gray-500"
              >
                No recent transactions
              </div>
            </div>
          </div>
        </div>

        <!-- Low Stock Items -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-medium text-gray-900">Low Stock Items</h3>
              <a
                :href="route('inventory.levels.index', { stock_status: 'low_stock' })"
                class="text-sm text-blue-600 hover:text-blue-500"
              >
                View all
              </a>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Product
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Warehouse
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      On Hand
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Available
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Threshold
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Status
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="level in lowStockItems" :key="level.id">
                    <td class="px-4 py-3 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">
                        {{ level.product?.name }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ level.product?.sku }}
                      </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                      {{ level.warehouse?.name }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                      {{ formatNumber(level.quantity_on_hand) }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-yellow-600">
                      {{ formatNumber(level.quantity_available) }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                      {{ level.product?.low_stock_threshold || 10 }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                      <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        Low Stock
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div
                v-if="lowStockItems.length === 0"
                class="text-center py-8 text-gray-500"
              >
                No low stock items
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import {
  PlusIcon,
  CubeIcon,
  CurrencyDollarIcon,
  ExclamationTriangleIcon,
  XCircleIcon,
  ChartBarIcon,
  DocumentTextIcon,
  BuildingStorefrontIcon,
  
} from '@heroicons/vue/24/outline'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  stats: Object,
  recentTransactions: Array,
  lowStockItems: Array,
  transactionTypes: Object,
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'GHS',
  }).format(amount)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatNumber = (num) => {
  return parseFloat(num).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
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

const getTransactionTypeClass = (type) => {
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

const newTransaction = () => {
  router.visit(route('inventory.transactions.create'))
}
</script>
