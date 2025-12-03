<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
      <h2 class="text-xl font-bold mb-4">Add New Customer</h2>
      
      <!-- Use Inertia form -->
      <form @submit.prevent="submit">
        <div class="space-y-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
            <input 
              v-model="form.name" 
              type="text" 
              class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              required
              :disabled="form.processing"
            >
            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
              {{ form.errors.name }}
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
            <input 
              v-model="form.phone" 
              type="text" 
              class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              required
              :disabled="form.processing"
            >
            <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">
              {{ form.errors.phone }}
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input 
              v-model="form.email" 
              type="email" 
              class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              :disabled="form.processing"
            >
            <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">
              {{ form.errors.email }}
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <textarea 
              v-model="form.address" 
              class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              rows="2"
              :disabled="form.processing"
            ></textarea>
          </div>
        </div>
        
        <div class="flex justify-end gap-2 mt-6">
          <button 
            type="button" 
            @click="$emit('close')"
            class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50"
            :disabled="form.processing"
          >
            Cancel
          </button>
          <button 
            type="submit" 
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
            :disabled="form.processing"
          >
            <span v-if="form.processing">Saving...</span>
            <span v-else>Save Customer</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const emit = defineEmits(['close', 'saved']);

// Use Inertia form helper
const form = useForm({
  name: '',
  phone: '',
  email: '',
  address: '',
  city: '',
  state: '',
  country: '',
});

const submit = () => {
  form.post(route('customers.store'), {
    preserveScroll: true,
    onSuccess: (page) => {
      // Check if response contains customer data
      if (page.props.customer) {
        emit('saved', page.props.customer);
      } else {
        // Try to get customer from flash data or response
        const customer = page.props.flash.customer || 
                        (page.props.success && page.props.success.customer);
        if (customer) {
          emit('saved', customer);
        }
      }
      
      // Reset form
      form.reset();
    },
    onError: (errors) => {
      console.error('Customer form errors:', errors);
    },
  });
};
</script>