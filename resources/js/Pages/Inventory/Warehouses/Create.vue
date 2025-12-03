[file name]: resources/js/Pages/Inventory/Warehouses/Create.vue
[file content begin]
<template>
  <AppLayout title="Create Warehouse">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Create New Warehouse
        </h2>
        <SecondaryButton @click="router.visit(route('warehouses.index'))">
          Back to List
        </SecondaryButton>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="submit">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Code -->
                <div>
                  <InputLabel for="code" value="Warehouse Code" />
                  <TextInput
                    id="code"
                    v-model="form.code"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="Leave empty to auto-generate"
                  />
                  <InputError :message="form.errors.code" class="mt-2" />
                </div>

                <!-- Name -->
                <div>
                  <InputLabel for="name" value="Warehouse Name" required />
                  <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                  />
                  <InputError :message="form.errors.name" class="mt-2" />
                </div>

                <!-- Location -->
                <div>
                  <InputLabel for="location" value="Location" />
                  <TextInput
                    id="location"
                    v-model="form.location"
                    type="text"
                    class="mt-1 block w-full"
                  />
                  <InputError :message="form.errors.location" class="mt-2" />
                </div>

                <!-- Contact Person -->
                <div>
                  <InputLabel for="contact_person" value="Contact Person" />
                  <TextInput
                    id="contact_person"
                    v-model="form.contact_person"
                    type="text"
                    class="mt-1 block w-full"
                  />
                  <InputError :message="form.errors.contact_person" class="mt-2" />
                </div>

                <!-- Phone -->
                <div>
                  <InputLabel for="phone" value="Phone" />
                  <TextInput
                    id="phone"
                    v-model="form.phone"
                    type="tel"
                    class="mt-1 block w-full"
                  />
                  <InputError :message="form.errors.phone" class="mt-2" />
                </div>

                <!-- Email -->
                <div>
                  <InputLabel for="email" value="Email" />
                  <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                  />
                  <InputError :message="form.errors.email" class="mt-2" />
                </div>

                <!-- Address -->
                <div class="md:col-span-2">
                  <InputLabel for="address" value="Address" />
                  <textarea
                    id="address"
                    v-model="form.address"
                    rows="3"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                  />
                  <InputError :message="form.errors.address" class="mt-2" />
                </div>

                <!-- Status Checkboxes -->
                <div class="md:col-span-2 space-y-4">
                  <div class="flex items-center">
                    <input
                      id="is_active"
                      v-model="form.is_active"
                      type="checkbox"
                      class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                    />
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                      Active Warehouse
                    </label>
                  </div>

                  <div class="flex items-center">
                    <input
                      id="is_default"
                      v-model="form.is_default"
                      type="checkbox"
                      class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                    />
                    <label for="is_default" class="ml-2 block text-sm text-gray-900">
                      Set as Default Warehouse
                    </label>
                    <p class="ml-4 text-sm text-gray-500">
                      New inventory will be assigned to default warehouse if not specified
                    </p>
                  </div>
                </div>
              </div>

              <div class="flex items-center justify-end mt-8">
                <SecondaryButton
                  type="button"
                  @click="router.visit(route('warehouses.index'))"
                  class="mr-4"
                >
                  Cancel
                </SecondaryButton>
                <PrimaryButton
                  type="submit"
                  :disabled="form.processing"
                >
                  <span v-if="form.processing">Creating...</span>
                  <span v-else>Create Warehouse</span>
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

const form = useForm({
  code: '',
  name: '',
  location: '',
  contact_person: '',
  phone: '',
  email: '',
  address: '',
  is_active: true,
  is_default: false,
})

const submit = () => {
  form.post(route('warehouses.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
    },
  })
}
</script>
[file content end]