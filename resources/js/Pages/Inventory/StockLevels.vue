
<template>
  <AppLayout title="Stock Levels">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Stock Levels
        </h2>
        <div class="flex space-x-2">
          <SecondaryButton @click="exportReport" :disabled="exporting">
            <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
            {{ exporting ? 'Exporting...' : 'Export' }}
          </SecondaryButton>
          <PrimaryButton @click="showFilters = !showFilters">
            <FunnelIcon class="h-4 w-4 mr-2" />
            Filters
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
              <!-- Search -->
              <div>
                <InputLabel for="search" value="Search Products" />
                <TextInput
                  id="search"
                  v-model="filters.search"
                  type="text"
                  class="mt-1 block w-full"
                  placeholder="Name, SKU, Barcode..."
                />
              </div>

              <!-- Category -->
              <div>
                <InputLabel for="category_id" value="Category" />
                <select
                  id="category_id"
                  v-model="filters.category_id"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                >
                  <option value="">All Categories</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                  </option>
                </select>
              </div>

              <!-- Warehouse -->
              <div>
                <InputLabel for="warehouse_id" value="Warehouse" />
                <select
                  id="warehouse_id"
                  v-model="filters.warehouse_id"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                >
                  <option value="">All Warehouses</option>
                  <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                    {{ warehouse.name }}
                  </option>
                </select>
              </div>

              <!-- Stock Status -->
              <div>
                <InputLabel for="stock_status" value="Stock Status" />
                <select
                  id="stock_status"
                  v-model="filters.stock_status"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                >
                  <option value="">All</option>
                  <option value="in_stock">In Stock</option>
                  <option value="low_stock">Low Stock</option>
                  <option value="out_of_stock">Out of Stock</option>
                </select>
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

        <!-- Stock Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <CubeIcon class="h-6 w-6 text-gray-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Total Products
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ summary.total_products }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <CheckCircleIcon class="h-6 w-6 text-green-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      In Stock
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ summary.in_stock }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <ExclamationTriangleIcon class="h-6 w-6 text-yellow-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Low Stock
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ summary.low_stock }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <XCircleIcon class="h-6 w-6 text-red-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Out of Stock
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ summary.out_of_stock }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Stock Levels Table -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Product
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Category
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Warehouse
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      On Hand
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Committed
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Available
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Average Cost
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Total Value
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Status
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="level in stockLevels.data" :key="level.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div>
                          <div class="text-sm font-medium text-gray-900">
                            {{ level.product.name }}
                          </div>
                          <div class="text-sm text-gray-500">
                            {{ level.product.sku }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ level.product.category?.name || '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ level.warehouse.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatNumber(level.quantity_on_hand) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatNumber(level.quantity_committed) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <span :class="getStockLevelClass(level.quantity_available)">
                        {{ formatNumber(level.quantity_available) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatCurrency(level.average_cost) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatCurrency(level.total_value) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="getStockStatusClass(level)">
                        {{ level.stock_status_text }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
              
              <!-- Empty State -->
              <div
                v-if="stockLevels.data.length === 0"
                class="text-center py-12"
              >
                <CubeIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No stock data</h3>
                <p class="mt-1 text-sm text-gray-500">
                  No inventory levels found matching your criteria.
                </p>
              </div>
            </div>

            <!-- Pagination -->
            <Pagination
              v-if="stockLevels.data.length > 0"
              class="mt-6"
              :links="stockLevels.links"
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
  FunnelIcon,
  ArrowDownTrayIcon,
  CubeIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  stockLevels: Object,
  categories: Array,
  warehouses: Array,
  summary: Object,
  filters: Object,
})

const showFilters = ref(false)
const exporting = ref(false)

const filters = ref({
  search: props.filters.search || '',
  category_id: props.filters.category_id || '',
  warehouse_id: props.filters.warehouse_id || '',
  stock_status: props.filters.stock_status || '',
})

const getStockLevelClass = (available) => {
  if (available <= 0) return 'text-red-600'
  if (available <= 10) return 'text-yellow-600'
  return 'text-green-600'
}

const getStockStatusClass = (level) => {
  const classes = {
    'out_of_stock': 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800',
    'low_stock': 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800',
    'in_stock': 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800',
  }
  return classes[level.stock_status] || 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800'
}

const formatNumber = (num) => {
  return parseFloat(num).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'GHS',
  }).format(amount)
}

const applyFilters = () => {
  router.get(route('inventory.levels.index'), filters.value, {
    preserveState: true,
    replace: true,
  })
}

const resetFilters = () => {
  filters.value = {
    search: '',
    category_id: '',
    warehouse_id: '',
    stock_status: '',
  }
  applyFilters()
}

const exportReport = async () => {
  exporting.value = true
  try {
    const response = await axios.get(route('inventory.levels.index'), {
      params: {
        ...filters.value,
        export: 'csv',
      },
      responseType: 'blob'
    })
    
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `stock-report-${new Date().toISOString().split('T')[0]}.csv`)
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (error) {
    console.error('Export failed:', error)
  } finally {
    exporting.value = false
  }
}
</script>
