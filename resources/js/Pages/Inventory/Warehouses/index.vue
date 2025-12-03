<template>
  <AppLayout title="Warehouses">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Warehouses
        </h2>
        <PrimaryButton @click="createWarehouse">
          <PlusIcon class="h-4 w-4 mr-2" />
          Add Warehouse
        </PrimaryButton>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Filters -->
            <div class="mb-6">
              <div class="flex flex-wrap gap-4">
                <div class="w-full md:w-48">
                  <InputLabel for="search" value="Search" />
                  <TextInput
                    id="search"
                    v-model="filters.search"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="Name or code..."
                    @keyup.enter="getWarehouses"
                  />
                </div>
                <div class="w-full md:w-32">
                  <InputLabel for="status" value="Status" />
                  <select
                    id="status"
                    v-model="filters.status"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    @change="getWarehouses"
                  >
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
                <div class="flex items-end">
                  <SecondaryButton @click="resetFilters">
                    Reset
                  </SecondaryButton>
                </div>
              </div>
            </div>

            <!-- Warehouse List -->
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Code
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Name
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Location
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Products
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Default
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="warehouse in warehouses.data" :key="warehouse.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ warehouse.code }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ warehouse.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ warehouse.location || '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ warehouse.product_count || 0 }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        :class="[
                          warehouse.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800',
                          'px-2 inline-flex text-xs leading-5 font-semibold rounded-full'
                        ]"
                      >
                        {{ warehouse.is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <CheckIcon v-if="warehouse.is_default" class="h-5 w-5 text-green-500" />
                      <span v-else class="text-gray-400">-</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex space-x-2">
                        <button
                          @click="editWarehouse(warehouse)"
                          class="text-indigo-600 hover:text-indigo-900"
                          title="Edit"
                        >
                          <PencilIcon class="h-5 w-5" />
                        </button>
                        <button
                          v-if="!warehouse.is_default"
                          @click="deleteWarehouse(warehouse)"
                          class="text-red-600 hover:text-red-900"
                          title="Delete"
                        >
                          <TrashIcon class="h-5 w-5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              
              <!-- Empty State -->
              <div
                v-if="warehouses.data.length === 0"
                class="text-center py-12"
              >
                <BuildingOfficeIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No warehouses</h3>
                <p class="mt-1 text-sm text-gray-500">
                  Get started by creating a new warehouse.
                </p>
                <div class="mt-6">
                  <PrimaryButton @click="createWarehouse">
                    <PlusIcon class="h-4 w-4 mr-2" />
                    New Warehouse
                  </PrimaryButton>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <Pagination
              v-if="warehouses.data.length > 0"
              class="mt-6"
              :links="warehouses.links"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false">
      <template #title>
        Delete Warehouse
      </template>
      <template #content>
        Are you sure you want to delete warehouse "{{ warehouseToDelete?.name }}"? This action cannot be undone.
      </template>
      <template #footer>
        <SecondaryButton @click="showDeleteModal = false">
          Cancel
        </SecondaryButton>
        <DangerButton
          class="ml-3"
          :class="{ 'opacity-25': deleteProcessing }"
          :disabled="deleteProcessing"
          @click="confirmDelete"
        >
          Delete Warehouse
        </DangerButton>
      </template>
    </ConfirmationModal>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import Pagination from '@/Components/Pagination.vue'
import { 
  PlusIcon,
  PencilIcon,
  TrashIcon,
  CheckIcon,
  BuildingOfficeIcon
} from '@heroicons/vue/24/outline'
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import debounce from 'lodash/debounce'

const props = defineProps({
  warehouses: Object,
  filters: Object,
})

const filters = ref({
  search: props.filters.search || '',
  status: props.filters.status || '',
})

const showDeleteModal = ref(false)
const warehouseToDelete = ref(null)
const deleteProcessing = ref(false)

const getWarehouses = debounce(() => {
  router.get(route('warehouses.index'), filters.value, {
    preserveState: true,
    replace: true,
  })
}, 300)

const resetFilters = () => {
  filters.value = { search: '', status: '' }
  getWarehouses()
}

const createWarehouse = () => {
  router.visit(route('warehouses.create'))
}

const editWarehouse = (warehouse) => {
  router.visit(route('warehouses.edit', warehouse.id))
}

const deleteWarehouse = (warehouse) => {
  warehouseToDelete.value = warehouse
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  deleteProcessing.value = true
  try {
    await router.delete(route('warehouses.destroy', warehouseToDelete.value.id))
    showDeleteModal.value = false
    warehouseToDelete.value = null
  } catch (error) {
    console.error('Delete failed:', error)
  } finally {
    deleteProcessing.value = false
  }
}

watch(filters, getWarehouses, { deep: true })
</script>
