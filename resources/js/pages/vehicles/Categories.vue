<script setup lang="ts">
// import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { Car, Pencil, Plus, Trash2, X } from 'lucide-vue-next'
import { ref } from 'vue'

const props = defineProps<{ categories: any[] }>()

const showModal = ref(false)
const editingCat = ref<any>(null)

const form = useForm({
  name:               '',
  description:        '',
  seat_count:         5,
  luggage_count:      2,
  transmission:       'automatic',
  base_price_per_day: '',
  extra_km_price:     '0',
  free_km_per_day:    300,
  is_active:          true,
})

const openCreate = () => {
  editingCat.value = null
  form.reset()
  showModal.value = true
}

const openEdit = (cat: any) => {
  editingCat.value = cat
  Object.assign(form, {
    name: cat.name, description: cat.description, seat_count: cat.seat_count,
    luggage_count: cat.luggage_count, transmission: cat.transmission,
    base_price_per_day: cat.base_price_per_day, extra_km_price: cat.extra_km_price,
    free_km_per_day: cat.free_km_per_day, is_active: cat.is_active,
  })
  showModal.value = true
}

const submit = () => {
  if (editingCat.value) {
    form.put(`/vehicle-categories/${editingCat.value.id}`, { onSuccess: () => { showModal.value = false } })
  } else {
    form.post('/vehicle-categories', { onSuccess: () => { showModal.value = false; form.reset() } })
  }
}

const destroy = (id: string) => {
  if (confirm('Delete this category?')) useForm({}).delete(`/vehicle-categories/${id}`)
}
</script>

<template>
  <!-- <AppLayout> -->
    <template #header>
      <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Vehicle Categories</h1>
    </template>

    <div class="p-4 lg:p-6 space-y-5">
      <div class="flex justify-end">
        <button class="flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 text-sm font-semibold" @click="openCreate">
          <Plus class="h-4 w-4" /> New Category
        </button>
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="cat in categories"
          :key="cat.id"
          class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5"
        >
          <div class="flex items-start justify-between mb-3">
            <div class="h-10 w-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
              <Car class="h-5 w-5 text-blue-600" />
            </div>
            <div class="flex gap-1">
              <button class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20" @click="openEdit(cat)">
                <Pencil class="h-4 w-4" />
              </button>
              <button class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20" @click="destroy(cat.id)">
                <Trash2 class="h-4 w-4" />
              </button>
            </div>
          </div>
          <h3 class="font-semibold text-gray-900 dark:text-white">{{ cat.name }}</h3>
          <p class="text-xs text-gray-400 mt-1">{{ cat.description }}</p>
          <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg px-2 py-1.5">
              <p class="text-gray-400">Seats</p>
              <p class="font-semibold text-gray-900 dark:text-white">{{ cat.seat_count }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg px-2 py-1.5">
              <p class="text-gray-400">Luggage</p>
              <p class="font-semibold text-gray-900 dark:text-white">{{ cat.luggage_count }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg px-2 py-1.5">
              <p class="text-gray-400">Free KM/day</p>
              <p class="font-semibold text-gray-900 dark:text-white">{{ cat.free_km_per_day }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg px-2 py-1.5">
              <p class="text-gray-400">Vehicles</p>
              <p class="font-semibold text-gray-900 dark:text-white">{{ cat.vehicles_count ?? 0 }}</p>
            </div>
          </div>
          <div class="mt-3 flex items-center justify-between">
            <p class="text-lg font-bold text-blue-600">{{ Number(cat.base_price_per_day).toLocaleString() }} MAD<span class="text-xs font-normal text-gray-400">/day</span></p>
            <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', cat.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600']">
              {{ cat.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-gray-900 shadow-2xl">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ editingCat ? 'Edit Category' : 'New Category' }}</h2>
            <button class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800" @click="showModal = false"><X class="h-4 w-4" /></button>
          </div>
          <div class="p-6 grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="block text-xs font-medium text-gray-500 mb-1">Name *</label>
              <input v-model="form.name" type="text" class="input-field" placeholder="e.g. Economy, SUV, Luxury" />
              <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
            </div>
            <div class="col-span-2">
              <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
              <textarea v-model="form.description" rows="2" class="input-field resize-none" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Seats *</label>
              <input v-model="form.seat_count" type="number" min="2" max="12" class="input-field" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Luggage *</label>
              <input v-model="form.luggage_count" type="number" min="0" class="input-field" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Transmission</label>
              <select v-model="form.transmission" class="input-field">
                <option value="automatic">Automatic</option>
                <option value="manual">Manual</option>
                <option value="both">Both</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Base Price/day (MAD) *</label>
              <input v-model="form.base_price_per_day" type="number" min="0" class="input-field" />
              <p v-if="form.errors.base_price_per_day" class="mt-1 text-xs text-red-500">{{ form.errors.base_price_per_day }}</p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Extra KM Price (MAD)</label>
              <input v-model="form.extra_km_price" type="number" min="0" class="input-field" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Free KM/day</label>
              <input v-model="form.free_km_per_day" type="number" min="0" class="input-field" />
            </div>
            <div class="col-span-2">
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600" />
                <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
              </label>
            </div>
          </div>
          <div class="flex justify-end gap-3 px-6 pb-5">
            <button class="px-4 py-2 rounded-xl text-sm text-gray-600 hover:bg-gray-100" @click="showModal = false">Cancel</button>
            <button class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-semibold" :disabled="form.processing" @click="submit">
              {{ form.processing ? 'Saving...' : 'Save Category' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  <!-- </AppLayout> -->
</template>

<style scoped>
.input-field { @apply w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 px-3 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500; }
</style>
