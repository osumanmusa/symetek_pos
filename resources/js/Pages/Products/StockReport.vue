
<template>
  <AppLayout title="Stock Report">
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-2xl font-bold text-gray-800">
            Stock Report
          </h2>
          <p class="mt-1 text-sm text-gray-600">
            Comprehensive view of all product stock levels
          </p>
        </div>
        <div class="flex flex-wrap gap-2">
          <SecondaryButton @click="exportReport" :disabled="exporting">
            <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
            {{ exporting ? 'Exporting...' : 'Export CSV' }}
          </SecondaryButton>
          <PrimaryButton @click="showFilters = !showFilters">
            <FunnelIcon class="h-4 w-4 mr-2" />
            {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
          </PrimaryButton>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Filters Card -->
        <div v-show="showFilters" class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
          <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Filter Products</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <!-- Search -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Search Products
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                  </div>
                  <input
                    v-model="filters.search"
                    type="text"
                    placeholder="Name, SKU, Barcode..."
                    class="pl-10 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    @keyup.enter="applyFilters"
                  />
                </div>
              </div>

              <!-- Category -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Category
                </label>
                <select
                  v-model="filters.category_id"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  @change="applyFilters"
                >
                  <option value="">All Categories</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                  </option>
                </select>
              </div>

              <!-- Stock Status -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Stock Status
                </label>
                <select
                  v-model="filters.stock_status"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  @change="applyFilters"
                >
                  <option value="">All Status</option>
                  <option value="in_stock">In Stock</option>
                  <option value="low_stock">Low Stock</option>
                  <option value="out_of_stock">Out of Stock</option>
                </select>
              </div>

              <!-- Product Status -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Product Status
                </label>
                <select
                  v-model="filters.product_status"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  @change="applyFilters"
                >
                  <option value="">All Products</option>
                  <option value="active">Active Only</option>
                  <option value="inactive">Inactive Only</option>
                </select>
              </div>
            </div>

            <div class="flex justify-end mt-4 pt-4 border-t border-gray-200">
              <button
                type="button"
                @click="resetFilters"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Clear All Filters
              </button>
            </div>
          </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                <CubeIcon class="h-6 w-6 text-blue-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Products</p>
                <p class="text-2xl font-semibold text-gray-900">{{ summary.total_products || 0 }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                <CheckCircleIcon class="h-6 w-6 text-green-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">In Stock</p>
                <p class="text-2xl font-semibold text-gray-900">{{ summary.in_stock || 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ summary.in_stock_percentage || 0 }}% of total</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                <ExclamationTriangleIcon class="h-6 w-6 text-yellow-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Low Stock</p>
                <p class="text-2xl font-semibold text-gray-900">{{ summary.low_stock || 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Needs restocking</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                <XCircleIcon class="h-6 w-6 text-red-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Out of Stock</p>
                <p class="text-2xl font-semibold text-gray-900">{{ summary.out_of_stock || 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ summary.out_of_stock_percentage || 0 }}% of total</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Report Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="p-6">
            <!-- Table Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
              <div>
                <h3 class="text-lg font-medium text-gray-900">Product Stock Levels</h3>
                <p class="text-sm text-gray-600">
                  Showing {{ products.from || 0 }} to {{ products.to || 0 }} of {{ products.total || 0 }} products
                </p>
              </div>
              
              <div class="flex items-center space-x-2 mt-4 sm:mt-0">
                <select
                  v-model="perPage"
                  @change="updatePerPage"
                  class="block w-32 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option value="10">10 per page</option>
                  <option value="25">25 per page</option>
                  <option value="50">50 per page</option>
                  <option value="100">100 per page</option>
                </select>
              </div>
            </div>

            <!-- Products Table -->
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr class="bg-gray-50">
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Product
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Category
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      SKU
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Total Stock
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Available
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Low Stock Alert
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
                  <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                    <!-- Product -->
                    <td class="px-6 py-4">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-md flex items-center justify-center">
                          <CubeIcon class="h-6 w-6 text-gray-400" />
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">
                            {{ product.name }}
                          </div>
                          <div class="text-sm text-gray-500">
                            {{ product.barcode || 'No barcode' }}
                          </div>
                        </div>
                      </div>
                    </td>

                    <!-- Category -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        {{ product.category?.name || 'Uncategorized' }}
                      </div>
                    </td>

                    <!-- SKU -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">
                        {{ product.sku || 'N/A' }}
                      </div>
                    </td>

                    <!-- Total Stock -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        {{ formatNumber(product.total_quantity || 0) }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ product.unit || 'units' }}
                      </div>
                    </td>

                    <!-- Available -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium" :class="getStockLevelClass(product.total_available)">
                        {{ formatNumber(product.total_available || 0) }}
                      </div>
                      <div v-if="product.inventory_levels?.length > 0" class="text-xs text-gray-500">
                        Across {{ product.inventory_levels.length }} warehouse(s)
                      </div>
                    </td>

                    <!-- Low Stock Alert -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        {{ product.low_stock_threshold || 10 }}
                      </div>
                      <div class="text-xs text-gray-500">
                        Alert level
                      </div>
                    </td>

                    <!-- Status -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex flex-col space-y-2">
                        <span :class="getStockStatusBadgeClass(product)">
                          {{ getStockStatusText(product) }}
                        </span>
                        <span :class="getProductStatusBadgeClass(product)">
                          {{ product.is_active ? 'Active' : 'Inactive' }}
                        </span>
                      </div>
                    </td>

                    <!-- Actions -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex items-center space-x-2">
                        <button
                          @click="viewProduct(product)"
                          class="text-indigo-600 hover:text-indigo-900"
                          title="View Details"
                        >
                          <EyeIcon class="h-5 w-5" />
                        </button>
                        <button
                          @click="viewInventory(product)"
                          class="text-blue-600 hover:text-blue-900"
                          title="View Inventory"
                        >
                          <BuildingStorefrontIcon class="h-5 w-5" />
                        </button>
                        <button
                          v-if="product.is_low_stock"
                          @click="createReorder(product)"
                          class="text-green-600 hover:text-green-900"
                          title="Reorder"
                        >
                          <ShoppingCartIcon class="h-5 w-5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Empty State -->
              <div v-if="products.data.length === 0" class="text-center py-12">
                <CubeIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
                <p class="mt-1 text-sm text-gray-500">
                  {{ hasFilters ? 'Try changing your filters' : 'No products in inventory' }}
                </p>
                <div v-if="!hasFilters" class="mt-6">
                  <PrimaryButton @click="router.visit(route('products.create'))">
                    <PlusIcon class="h-4 w-4 mr-2" />
                    Add Product
                  </PrimaryButton>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="products.data.length > 0" class="flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6">
              <div class="flex flex-1 justify-between sm:hidden">
                <button
                  :disabled="!products.prev_page_url"
                  @click="goToPage(products.current_page - 1)"
                  class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Previous
                </button>
                <button
                  :disabled="!products.next_page_url"
                  @click="goToPage(products.current_page + 1)"
                  class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Next
                </button>
              </div>
              <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-gray-700">
                    Showing <span class="font-medium">{{ products.from }}</span> to 
                    <span class="font-medium">{{ products.to }}</span> of 
                    <span class="font-medium">{{ products.total }}</span> results
                  </p>
                </div>
                <div>
                  <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    <button
                      :disabled="!products.prev_page_url"
                      @click="goToPage(products.current_page - 1)"
                      class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <span class="sr-only">Previous</span>
                      <ChevronLeftIcon class="h-5 w-5" />
                    </button>
                    
                    <button
                      v-for="page in paginationRange"
                      :key="page"
                      @click="goToPage(page)"
                      :class="[
                        'relative inline-flex items-center px-4 py-2 text-sm font-semibold',
                        page === products.current_page
                          ? 'z-10 bg-indigo-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600'
                          : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0'
                      ]"
                    >
                      {{ page }}
                    </button>
                    
                    <button
                      :disabled="!products.next_page_url"
                      @click="goToPage(products.current_page + 1)"
                      class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <span class="sr-only">Next</span>
                      <ChevronRightIcon class="h-5 w-5" />
                    </button>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Low Stock Alert Section -->
        <div v-if="lowStockProducts.length > 0" class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
          <div class="flex items-center mb-4">
            <ExclamationTriangleIcon class="h-6 w-6 text-yellow-600 mr-2" />
            <h3 class="text-lg font-medium text-yellow-800">Low Stock Alerts</h3>
          </div>
          <p class="text-sm text-yellow-700 mb-4">
            {{ lowStockProducts.length }} product(s) are below their low stock threshold and need reordering.
          </p>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-yellow-200">
              <thead>
                <tr>
                  <th class="px-3 py-2 text-left text-xs font-medium text-yellow-800 uppercase">Product</th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-yellow-800 uppercase">Available</th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-yellow-800 uppercase">Threshold</th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-yellow-800 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-yellow-100">
                <tr v-for="product in lowStockProducts" :key="product.id">
                  <td class="px-3 py-2 text-sm text-yellow-900">{{ product.name }}</td>
                  <td class="px-3 py-2 text-sm font-medium text-yellow-900">
                    {{ product.total_available }} {{ product.unit }}
                  </td>
                  <td class="px-3 py-2 text-sm text-yellow-900">{{ product.low_stock_threshold }} {{ product.unit }}</td>
                  <td class="px-3 py-2 text-sm">
                    <button
                      @click="createReorder(product)"
                      class="text-green-600 hover:text-green-900 flex items-center"
                    >
                      <ShoppingCartIcon class="h-4 w-4 mr-1" />
                      Reorder
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
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
import { 
  ArrowDownTrayIcon,
  FunnelIcon,
  MagnifyingGlassIcon,
  CubeIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  XCircleIcon,
  EyeIcon,
  BuildingStorefrontIcon,
  ShoppingCartIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  PlusIcon
} from '@heroicons/vue/24/outline'
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import debounce from 'lodash/debounce'
import axios from 'axios'

const props = defineProps({
  products: Object,
  categories: Array,
  summary: Object,
  filters: Object,
})

const showFilters = ref(false)
const exporting = ref(false)
const perPage = ref(props.products.per_page || 25)

const filters = ref({
  search: props.filters.search || '',
  category_id: props.filters.category_id || '',
  stock_status: props.filters.stock_status || '',
  product_status: props.filters.product_status || '',
})

const hasFilters = computed(() => {
  return Object.values(filters.value).some(value => value !== '')
})

const lowStockProducts = computed(() => {
  return props.products.data.filter(product => product.is_low_stock)
})

const paginationRange = computed(() => {
  const current = props.products.current_page
  const last = props.products.last_page
  const delta = 2
  const range = []
  const rangeWithDots = []
  let l

  for (let i = 1; i <= last; i++) {
    if (i === 1 || i === last || (i >= current - delta && i <= current + delta)) {
      range.push(i)
    }
  }

  range.forEach((i) => {
    if (l) {
      if (i - l === 2) {
        rangeWithDots.push(l + 1)
      } else if (i - l !== 1) {
        rangeWithDots.push('...')
      }
    }
    rangeWithDots.push(i)
    l = i
  })

  return rangeWithDots
})

// Formatting functions
const formatNumber = (num) => {
  return parseFloat(num).toLocaleString('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'GHS',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount)
}

// Styling functions
const getStockLevelClass = (available) => {
  if (available <= 0) return 'text-red-600'
  if (available <= 10) return 'text-yellow-600'
  return 'text-green-600'
}

const getStockStatusText = (product) => {
  if (product.total_available <= 0) return 'Out of Stock'
  if (product.total_available <= product.low_stock_threshold) return 'Low Stock'
  return 'In Stock'
}

const getStockStatusBadgeClass = (product) => {
  if (product.total_available <= 0) {
    return 'inline-flex px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800'
  }
  if (product.total_available <= product.low_stock_threshold) {
    return 'inline-flex px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800'
  }
  return 'inline-flex px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800'
}

const getProductStatusBadgeClass = (product) => {
  return product.is_active 
    ? 'inline-flex px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800'
    : 'inline-flex px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800'
}

// Actions
const applyFilters = debounce(() => {
  router.get(route('products.stock-report'), {
    ...filters.value,
    per_page: perPage.value,
  }, {
    preserveState: true,
    replace: true,
  })
}, 300)

const resetFilters = () => {
  filters.value = {
    search: '',
    category_id: '',
    stock_status: '',
    product_status: '',
  }
  applyFilters()
}

const updatePerPage = () => {
  applyFilters()
}

const goToPage = (page) => {
  if (page < 1 || page > props.products.last_page) return
  
  router.get(route('products.stock-report'), {
    ...filters.value,
    per_page: perPage.value,
    page: page,
  }, {
    preserveState: true,
    replace: true,
  })
}

const viewProduct = (product) => {
  router.visit(route('products.show', product.id))
}

const viewInventory = (product) => {
  router.visit(route('products.inventory', product.id))
}

const createReorder = (product) => {
  // Navigate to purchase order creation with product pre-selected
  router.visit(route('purchase-orders.create'), {
    data: {
      product_id: product.id,
      quantity: product.low_stock_threshold * 2, // Suggest ordering 2x threshold
    }
  })
}

const exportReport = async () => {
  exporting.value = true
  try {
    const response = await axios.get(route('products.stock-report'), {
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

// Watch filters for changes
watch(filters, applyFilters, { deep: true })
</script>
