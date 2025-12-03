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
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                  <InputError :message="errors.transaction_type" class="mt-2" />
                </div>

                <!-- Product Selection -->
                <div>
                  <InputLabel for="product_id" value="Product" required />
                  <select
                    id="product_id"
                    v-model="form.product_id"
                    @change="getProductStock"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    required
                  >
                    <option value="">Select Product</option>
                    <option v-for="product in products" :key="product.id" :value="product.id">
                      {{ product.name }} ({{ product.sku }})
                    </option>
                  </select>
                  <InputError :message="errors.product_id" class="mt-2" />
                </div>

                <!-- Warehouse Selection -->
                <div>
                  <InputLabel for="warehouse_id" value="Warehouse" />
                  <div class="flex items-center space-x-2">
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
                    <a
                      v-if="form.product_id"
                      :href="route('products.inventory', form.product_id)"
                      target="_blank"
                      class="text-sm text-indigo-600 hover:text-indigo-900"
                    >
                      View Stock
                    </a>
                  </div>
                  <InputError :message="errors.warehouse_id" class="mt-2" />
                </div>

                <!-- Quantity -->
                <div>
                  <InputLabel for="quantity" value="Quantity" required />
                  <div class="flex items-center space-x-2">
                    <TextInput
                      id="quantity"
                      v-model="form.quantity"
                      type="number"
                      step="0.01"
                      min="0.01"
                      class="mt-1 block w-full"
                      required
                      @input="calculateTotal"
                    />
                    <span v-if="selectedProduct" class="text-sm text-gray-500">
                      {{ selectedProduct.unit || 'units' }}
                    </span>
                  </div>
                  <div v-if="stockInfo" class="mt-1 text-sm">
                    <span class="text-gray-600">Available: </span>
                    <span :class="{
                      'text-green-600 font-semibold': stockInfo.available_stock >= form.quantity,
                      'text-red-600 font-semibold': stockInfo.available_stock < form.quantity
                    }">
                      {{ stockInfo.available_stock }}
                    </span>
                  </div>
                  <InputError :message="errors.quantity" class="mt-2" />
                </div>

                <!-- Unit Cost -->
                <div v-if="showCostFields">
                  <InputLabel for="unit_cost" value="Unit Cost" />
                  <div class="relative mt-1 rounded-md shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                      <span class="text-gray-500 sm:text-sm">₵</span>
                    </div>
                    <TextInput
                      id="unit_cost"
                      v-model="form.unit_cost"
                      type="number"
                      step="0.01"
                      min="0"
                      class="block w-full pl-7"
                      @input="calculateTotal"
                    />
                  </div>
                  <InputError :message="errors.unit_cost" class="mt-2" />
                </div>

                <!-- Total Cost -->
                <div v-if="showCostFields">
                  <InputLabel for="total_cost" value="Total Cost" />
                  <div class="relative mt-1 rounded-md shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                      <span class="text-gray-500 sm:text-sm">₵</span>
                    </div>
                    <TextInput
                      id="total_cost"
                      v-model="form.total_cost"
                      type="number"
                      step="0.01"
                      min="0"
                      class="block w-full pl-7 bg-gray-50"
                      readonly
                    />
                  </div>
                  <InputError :message="errors.total_cost" class="mt-2" />
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
                  <InputError :message="errors.reference_number" class="mt-2" />
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                  <InputLabel for="notes" value="Notes" />
                  <textarea
                    id="notes"
                    v-model="form.notes"
                    rows="3"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    :placeholder="notesPlaceholder"
                  />
                  <InputError :message="errors.notes" class="mt-2" />
                </div>

                <!-- Summary Card -->
                <div class="md:col-span-2">
                  <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Transaction Summary</h3>
                    <div class="space-y-2">
                      <div class="flex justify-between">
                        <span class="text-gray-600">Type:</span>
                        <span class="font-medium">
                          {{ transactionTypes[form.transaction_type] || 'Not selected' }}
                        </span>
                      </div>
                      <div v-if="selectedProduct" class="flex justify-between">
                        <span class="text-gray-600">Product:</span>
                        <span class="font-medium">{{ selectedProduct.name }}</span>
                      </div>
                      <div v-if="form.quantity" class="flex justify-between">
                        <span class="text-gray-600">Quantity:</span>
                        <span class="font-medium">{{ form.quantity }} {{ selectedProduct?.unit || 'units' }}</span>
                      </div>
                      <div v-if="form.unit_cost" class="flex justify-between">
                        <span class="text-gray-600">Unit Cost:</span>
                        <span class="font-medium">₵{{ parseFloat(form.unit_cost).toFixed(2) }}</span>
                      </div>
                      <div v-if="form.total_cost" class="flex justify-between">
                        <span class="text-gray-600">Total Cost:</span>
                        <span class="font-medium">₵{{ parseFloat(form.total_cost).toFixed(2) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex items-center justify-end mt-8">
                <SecondaryButton
                  type="button"
                  @click="router.visit(route('inventory.transactions.index'))"
                  class="mr-4"
                >
                  Cancel
                </SecondaryButton>
                <PrimaryButton
                  type="submit"
                  :disabled="form.processing"
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
import axios from 'axios'

const props = defineProps({
  products: Array,
  warehouses: Array,
  transactionTypes: Object,
  errors: Object,
})

const form = useForm({
  transaction_type: '',
  product_id: '',
  quantity: 1,
  unit_cost: null,
  total_cost: null,
  warehouse_id: '',
  notes: '',
  reference_number: '',
})

const stockInfo = ref(null)
const selectedProduct = computed(() => 
  props.products.find(p => p.id == form.product_id)
)

const showCostFields = computed(() => {
  return ['purchase', 'return', 'production'].includes(form.transaction_type)
})

const notesPlaceholder = computed(() => {
  const type = form.transaction_type
  if (type === 'adjustment') return 'Reason for adjustment...'
  if (type === 'damage') return 'Details of damage...'
  if (type === 'transfer') return 'Transfer details...'
  return 'Additional notes...'
})

const getProductStock = async () => {
  if (!form.product_id || !form.warehouse_id) return
  
  try {
    const response = await axios.get(route('products.stock', form.product_id), {
      params: { warehouse_id: form.warehouse_id }
    })
    stockInfo.value = response.data
  } catch (error) {
    console.error('Error fetching stock:', error)
    stockInfo.value = null
  }
}

const calculateTotal = () => {
  if (form.quantity && form.unit_cost) {
    form.total_cost = parseFloat(form.quantity) * parseFloat(form.unit_cost)
  } else {
    form.total_cost = null
  }
}

const onTransactionTypeChange = () => {
  // Reset cost fields when type changes
  if (!showCostFields.value) {
    form.unit_cost = null
    form.total_cost = null
  }
}

const submit = () => {
  form.post(route('inventory.transactions.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
    },
  })
}

watch(() => form.warehouse_id, getProductStock)
watch(() => form.product_id, getProductStock)
</script>
[file content end]