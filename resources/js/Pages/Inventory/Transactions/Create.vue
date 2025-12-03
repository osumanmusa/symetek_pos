[file name]: resources/js/Pages/Inventory/Transactions/Create.vue
[file content begin]
<template>
  <AppLayout title="Create Inventory Transaction">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Create Inventory Transaction
        </h2>
        <SecondaryButton @click="router.visit(route('inventory.transactions.index'))">
          Back to Transactions
        </SecondaryButton>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="submit">
              <!-- Transaction Header -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Transaction Type -->
                <div>
                  <InputLabel for="transaction_type" value="Transaction Type" required />
                  <select
                    id="transaction_type"
                    v-model="form.transaction_type"
                    @change="onTransactionTypeChange"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    required
                  >
                    <option value="">Select Type</option>
                    <option v-for="(label, value) in transactionTypes" :key="value" :value="value">
                      {{ label }}
                    </option>
                  </select>
                  <InputError :message="form.errors.transaction_type" class="mt-2" />
                </div>

                <!-- Warehouse Selection -->
                <div>
                  <InputLabel for="warehouse_id" value="Warehouse" />
                  <select
                    id="warehouse_id"
                    v-model="form.warehouse_id"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                  >
                    <option value="">Default Warehouse</option>
                    <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                      {{ warehouse.name }} ({{ warehouse.code }})
                    </option>
                  </select>
                  <InputError :message="form.errors.warehouse_id" class="mt-2" />
                </div>

                <!-- Reference Number -->
                <div>
                  <InputLabel for="reference_number" value="Reference Number" />
                  <TextInput
                    id="reference_number"
                    v-model="form.reference_number"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="PO, Invoice, Transfer number..."
                  />
                  <InputError :message="form.errors.reference_number" class="mt-2" />
                </div>
              </div>

              <!-- Add Product Form -->
              <div class="bg-gray-50 p-4 rounded-lg mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Add Products</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                  <!-- Product Search/Select -->
                  <div>
                    <InputLabel for="new_product_id" value="Product" />
                    <select
                      id="new_product_id"
                      v-model="newProduct.product_id"
                      class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    >
                      <option value="">Select Product</option>
                      <option v-for="product in products" :key="product.id" :value="product.id">
                        {{ product.name }} ({{ product.sku }})
                      </option>
                    </select>
                  </div>

                  <!-- Quantity -->
                  <div>
                    <InputLabel for="new_quantity" value="Quantity" />
                    <TextInput
                      id="new_quantity"
                      v-model="newProduct.quantity"
                      type="number"
                      step="0.01"
                      min="0.01"
                      class="mt-1 block w-full"
                      placeholder="Qty"
                    />
                  </div>

                  <!-- Unit Cost -->
                  <div v-if="showCostFields">
                    <InputLabel for="new_unit_cost" value="Unit Cost" />
                    <div class="relative mt-1 rounded-md shadow-sm">
                      <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <span class="text-gray-500 sm:text-sm">₵</span>
                      </div>
                      <TextInput
                        id="new_unit_cost"
                        v-model="newProduct.unit_cost"
                        type="number"
                        step="0.01"
                        min="0"
                        class="block w-full pl-7"
                        placeholder="0.00"
                      />
                    </div>
                  </div>

                  <!-- Notes -->
                  <div>
                    <InputLabel for="new_notes" value="Notes" />
                    <TextInput
                      id="new_notes"
                      v-model="newProduct.notes"
                      type="text"
                      class="mt-1 block w-full"
                      placeholder="Item notes"
                    />
                  </div>

                  <!-- Add Button -->
                  <div class="flex items-end">
                    <PrimaryButton
                      type="button"
                      @click="addProduct"
                      :disabled="!canAddProduct"
                      class="w-full"
                    >
                      <PlusIcon class="h-4 w-4 mr-2" />
                      Add Product
                    </PrimaryButton>
                  </div>
                </div>
              </div>

              <!-- Products Table -->
              <div class="mb-8" v-if="form.items.length > 0">
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                      <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Product
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Quantity
                        </th>
                        <th v-if="showCostFields" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Unit Cost
                        </th>
                        <th v-if="showCostFields" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Total Cost
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Notes
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Actions
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="(item, index) in form.items" :key="index">
                        <td class="px-4 py-3 whitespace-nowrap">
                          <div class="text-sm font-medium text-gray-900">
                            {{ getProductName(item.product_id) }}
                          </div>
                          <div class="text-sm text-gray-500">
                            {{ getProductSKU(item.product_id) }}
                          </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                          {{ item.quantity }} {{ getProductUnit(item.product_id) }}
                        </td>
                        <td v-if="showCostFields" class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                          ₵{{ formatNumber(item.unit_cost) }}
                        </td>
                        <td v-if="showCostFields" class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                          ₵{{ formatNumber(item.quantity * item.unit_cost) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                          {{ item.notes || '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                          <button
                            type="button"
                            @click="removeProduct(index)"
                            class="text-red-600 hover:text-red-900"
                            title="Remove"
                          >
                            <TrashIcon class="h-5 w-5" />
                          </button>
                        </td>
                      </tr>
                    </tbody>
                    <!-- Totals Row -->
                    <tfoot v-if="showCostFields && form.items.length > 0" class="bg-gray-50">
                      <tr>
                        <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-900">
                          Subtotal:
                        </td>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900">
                          ₵{{ formatNumber(subtotal) }}
                        </td>
                        <td colspan="2"></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>

              <!-- Empty State for Products -->
              <div v-if="form.items.length === 0" class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg mb-8">
                <CubeIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No products added</h3>
                <p class="mt-1 text-sm text-gray-500">
                  Add products to this transaction using the form above.
                </p>
              </div>

              <!-- Transaction Summary -->
              <div class="bg-gray-50 p-6 rounded-lg mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Transaction Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="space-y-4">
                    <div class="flex justify-between">
                      <span class="text-gray-600">Transaction Type:</span>
                      <span class="font-medium">
                        {{ transactionTypes[form.transaction_type] || 'Not selected' }}
                      </span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Warehouse:</span>
                      <span class="font-medium">
                        {{ getWarehouseName(form.warehouse_id) || 'Default' }}
                      </span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Reference:</span>
                      <span class="font-medium">
                        {{ form.reference_number || 'Not provided' }}
                      </span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Number of Products:</span>
                      <span class="font-medium">
                        {{ form.items.length }}
                      </span>
                    </div>
                  </div>
                  <div class="space-y-4" v-if="showCostFields">
                    <div class="flex justify-between">
                      <span class="text-gray-600">Total Quantity:</span>
                      <span class="font-medium">
                        {{ totalQuantity }}
                      </span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Subtotal:</span>
                      <span class="font-medium">₵{{ formatNumber(subtotal) }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-4">
                      <span class="text-lg font-bold text-gray-900">Total Cost:</span>
                      <span class="text-lg font-bold text-indigo-600">₵{{ formatNumber(subtotal) }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Notes -->
              <div class="mb-8">
                <InputLabel for="notes" value="Transaction Notes" />
                <textarea
                  id="notes"
                  v-model="form.notes"
                  rows="3"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                  :placeholder="notesPlaceholder"
                />
                <InputError :message="form.errors.notes" class="mt-2" />
              </div>

              <!-- Action Buttons -->
              <div class="flex items-center justify-end space-x-4">
                <SecondaryButton
                  type="button"
                  @click="router.visit(route('inventory-transactions.index'))"
                >
                  Cancel
                </SecondaryButton>
                <PrimaryButton
                  type="submit"
                  :disabled="form.processing || !canSubmit"
                >
                  <span v-if="form.processing">Processing...</span>
                  <span v-else>Record Transaction</span>
                </PrimaryButton>
              </div>
            </form>
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
import InputError from '@/Components/InputError.vue'
import { useForm, router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import { 
  PlusIcon, 
  TrashIcon, 
  CubeIcon 
} from '@heroicons/vue/24/outline'

const props = defineProps({
  products: Array,
  warehouses: Array,
  transactionTypes: Object,
})

// Initialize form with items array for multiple products
const form = useForm({
  transaction_type: '',
  warehouse_id: '',
  reference_number: '',
  notes: '',
  items: [], // Array of {product_id, quantity, unit_cost, notes}
})

// New product being added
const newProduct = ref({
  product_id: '',
  quantity: 1,
  unit_cost: null,
  notes: ''
})

// Computed properties
const showCostFields = computed(() => {
  return ['purchase', 'return', 'production'].includes(form.transaction_type)
})

const notesPlaceholder = computed(() => {
  const type = form.transaction_type
  if (type === 'adjustment') return 'Reason for adjustment...'
  if (type === 'damage') return 'Details of damage...'
  if (type === 'transfer') return 'Transfer details...'
  return 'Additional notes about this transaction...'
})

const canAddProduct = computed(() => {
  return newProduct.value.product_id && newProduct.value.quantity > 0
})

const canSubmit = computed(() => {
  return form.transaction_type && form.items.length > 0
})

const totalQuantity = computed(() => {
  return form.items.reduce((sum, item) => sum + parseFloat(item.quantity), 0)
})

const subtotal = computed(() => {
  if (!showCostFields.value) return 0
  return form.items.reduce((sum, item) => {
    const cost = item.unit_cost || 0
    return sum + (parseFloat(item.quantity) * parseFloat(cost))
  }, 0)
})

// Helper functions
const getProductName = (productId) => {
  const product = props.products.find(p => p.id == productId)
  return product?.name || 'Unknown Product'
}

const getProductSKU = (productId) => {
  const product = props.products.find(p => p.id == productId)
  return product?.sku || ''
}

const getProductUnit = (productId) => {
  const product = props.products.find(p => p.id == productId)
  return product?.unit || 'units'
}

const getWarehouseName = (warehouseId) => {
  const warehouse = props.warehouses.find(w => w.id == warehouseId)
  return warehouse?.name
}

const formatNumber = (num) => {
  return parseFloat(num || 0).toFixed(2)
}

// Methods
const addProduct = () => {
  if (!canAddProduct.value) return

  // Check if product already exists in items
  const existingIndex = form.items.findIndex(item => 
    item.product_id == newProduct.value.product_id
  )

  if (existingIndex > -1) {
    // Update existing item
    form.items[existingIndex].quantity = parseFloat(form.items[existingIndex].quantity) + 
                                         parseFloat(newProduct.value.quantity)
    if (newProduct.value.unit_cost) {
      form.items[existingIndex].unit_cost = newProduct.value.unit_cost
    }
    if (newProduct.value.notes) {
      form.items[existingIndex].notes = newProduct.value.notes
    }
  } else {
    // Add new item
    form.items.push({
      product_id: newProduct.value.product_id,
      quantity: parseFloat(newProduct.value.quantity),
      unit_cost: newProduct.value.unit_cost ? parseFloat(newProduct.value.unit_cost) : null,
      notes: newProduct.value.notes || ''
    })
  }

  // Reset new product form
  newProduct.value = {
    product_id: '',
    quantity: 1,
    unit_cost: null,
    notes: ''
  }
}

const removeProduct = (index) => {
  form.items.splice(index, 1)
}

const onTransactionTypeChange = () => {
  // Clear unit costs if not showing cost fields
  if (!showCostFields.value) {
    form.items.forEach(item => {
      item.unit_cost = null
    })
    newProduct.value.unit_cost = null
  }
}

const submit = () => {
  // Prepare data for submission
  const submissionData = {
    ...form.data(),
    user_id: window.auth?.user?.id // Make sure to send user ID
  }

  form.post(route('inventory-transactions.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      newProduct.value = {
        product_id: '',
        quantity: 1,
        unit_cost: null,
        notes: ''
      }
    },
  })
}
</script>
[file content end]