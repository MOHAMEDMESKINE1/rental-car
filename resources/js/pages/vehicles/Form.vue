<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'
import { ArrowLeft, Upload, X } from 'lucide-vue-next'
import { ref } from 'vue'

const props = defineProps<{
  vehicle?: any
  categories: any[]
  branches: any[]
}>()

const isEdit = !!props.vehicle

const form = useForm({
  category_id:         props.vehicle?.category_id ?? '',
  branch_id:           props.vehicle?.branch_id ?? '',
  make:                props.vehicle?.make ?? '',
  model:               props.vehicle?.model ?? '',
  year:                props.vehicle?.year ?? new Date().getFullYear(),
  color:               props.vehicle?.color ?? '',
  plate_number:        props.vehicle?.plate_number ?? '',
  vin:                 props.vehicle?.vin ?? '',
  transmission:        props.vehicle?.transmission ?? 'automatic',
  fuel_type:           props.vehicle?.fuel_type ?? 'gasoline',
  seat_count:          props.vehicle?.seat_count ?? 5,
  mileage:             props.vehicle?.mileage ?? 0,
  fuel_level:          props.vehicle?.fuel_level ?? 100,
  next_service_date:   props.vehicle?.next_service_date ?? '',
  insurance_expiry:    props.vehicle?.insurance_expiry ?? '',
  registration_expiry: props.vehicle?.registration_expiry ?? '',
  notes:               props.vehicle?.notes ?? '',
  is_active:           props.vehicle?.is_active ?? true,
  thumbnail:           null as File | null,
  photos:              [] as File[],
})

const thumbnailPreview = ref<string | null>(props.vehicle?.thumbnail_url ?? null)
const photosPreviews = ref<string[]>([])

const onThumbnail = (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (file) {
    form.thumbnail = file
    thumbnailPreview.value = URL.createObjectURL(file)
  }
}

const onPhotos = (e: Event) => {
  const files = Array.from((e.target as HTMLInputElement).files ?? [])
  form.photos = files
  photosPreviews.value = files.map((f) => URL.createObjectURL(f))
}

const submit = () => {
  const url = isEdit ? `/vehicles/${props.vehicle.id}` : '/vehicles'
  const method = isEdit ? 'post' : 'post'
  form
    .transform((data) => ({ ...data, _method: isEdit ? 'PUT' : 'POST' }))
    .post(url)
}
</script>

<template>
  <AppLayout>
    <template #header>
      <div class="flex items-center gap-3">
        <Link href="/vehicles" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
          <ArrowLeft class="h-4 w-4" />
        </Link>
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ isEdit ? 'Edit Vehicle' : 'Add Vehicle' }}
        </h1>
      </div>
    </template>

    <div class="p-4 lg:p-6">
      <form @submit.prevent="submit" class="max-w-4xl space-y-6">

        <!-- Images -->
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Photos</h2>
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Thumbnail -->
            <div>
              <p class="text-xs font-medium text-gray-500 mb-2">Main Photo</p>
              <label class="flex flex-col items-center justify-center h-40 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 cursor-pointer hover:border-blue-400 transition-colors overflow-hidden relative">
                <img v-if="thumbnailPreview" :src="thumbnailPreview" class="absolute inset-0 h-full w-full object-cover" />
                <div v-else class="flex flex-col items-center gap-2 text-gray-400">
                  <Upload class="h-8 w-8" />
                  <span class="text-xs">Click to upload</span>
                </div>
                <input type="file" accept="image/*" class="hidden" @change="onThumbnail" />
              </label>
              <p v-if="form.errors.thumbnail" class="mt-1 text-xs text-red-500">{{ form.errors.thumbnail }}</p>
            </div>
            <!-- Additional photos -->
            <div>
              <p class="text-xs font-medium text-gray-500 mb-2">Additional Photos</p>
              <label class="flex flex-col items-center justify-center h-40 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 cursor-pointer hover:border-blue-400 transition-colors">
                <div class="flex flex-col items-center gap-2 text-gray-400">
                  <Upload class="h-8 w-8" />
                  <span class="text-xs">Multiple files</span>
                </div>
                <input type="file" accept="image/*" multiple class="hidden" @change="onPhotos" />
              </label>
              <div v-if="photosPreviews.length" class="mt-2 flex gap-2 flex-wrap">
                <img v-for="(url, i) in photosPreviews" :key="i" :src="url" class="h-12 w-16 rounded-lg object-cover" />
              </div>
            </div>
          </div>
        </div>

        <!-- Basic info -->
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Vehicle Details</h2>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

            <div class="sm:col-span-2 grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Category *</label>
                <select v-model="form.category_id" class="input-field">
                  <option value="">Select category</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
                <p v-if="form.errors.category_id" class="mt-1 text-xs text-red-500">{{ form.errors.category_id }}</p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Branch *</label>
                <select v-model="form.branch_id" class="input-field">
                  <option value="">Select branch</option>
                  <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>
                <p v-if="form.errors.branch_id" class="mt-1 text-xs text-red-500">{{ form.errors.branch_id }}</p>
              </div>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Make *</label>
              <input v-model="form.make" type="text" placeholder="Toyota" class="input-field" />
              <p v-if="form.errors.make" class="mt-1 text-xs text-red-500">{{ form.errors.make }}</p>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Model *</label>
              <input v-model="form.model" type="text" placeholder="Corolla" class="input-field" />
              <p v-if="form.errors.model" class="mt-1 text-xs text-red-500">{{ form.errors.model }}</p>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Year *</label>
              <input v-model="form.year" type="number" class="input-field" />
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Color *</label>
              <input v-model="form.color" type="text" placeholder="White" class="input-field" />
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Plate Number *</label>
              <input v-model="form.plate_number" type="text" placeholder="12345-A-1" class="input-field uppercase" />
              <p v-if="form.errors.plate_number" class="mt-1 text-xs text-red-500">{{ form.errors.plate_number }}</p>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">VIN</label>
              <input v-model="form.vin" type="text" class="input-field" />
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Transmission *</label>
              <select v-model="form.transmission" class="input-field">
                <option value="automatic">Automatic</option>
                <option value="manual">Manual</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Fuel Type *</label>
              <select v-model="form.fuel_type" class="input-field">
                <option value="gasoline">Gasoline</option>
                <option value="diesel">Diesel</option>
                <option value="electric">Electric</option>
                <option value="hybrid">Hybrid</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Seat Count *</label>
              <input v-model="form.seat_count" type="number" min="2" max="12" class="input-field" />
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Current Mileage (km) *</label>
              <input v-model="form.mileage" type="number" min="0" class="input-field" />
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Fuel Level (%) *</label>
              <input v-model="form.fuel_level" type="number" min="0" max="100" class="input-field" />
            </div>
          </div>
        </div>

        <!-- Dates -->
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Documents & Dates</h2>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Next Service Date</label>
              <input v-model="form.next_service_date" type="date" class="input-field" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Insurance Expiry</label>
              <input v-model="form.insurance_expiry" type="date" class="input-field" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Registration Expiry</label>
              <input v-model="form.registration_expiry" type="date" class="input-field" />
            </div>
          </div>
          <div class="mt-4">
            <label class="block text-xs font-medium text-gray-500 mb-1">Notes</label>
            <textarea v-model="form.notes" rows="3" class="input-field resize-none" placeholder="Optional notes..." />
          </div>
          <label class="mt-4 flex items-center gap-2 cursor-pointer">
            <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600" />
            <span class="text-sm text-gray-700 dark:text-gray-300">Active (available for rental)</span>
          </label>
        </div>

        <!-- Submit -->
        <div class="flex items-center gap-3 justify-end">
          <Link href="/vehicles" class="px-4 py-2.5 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors disabled:opacity-60"
          >
            {{ form.processing ? 'Saving...' : (isEdit ? 'Update Vehicle' : 'Create Vehicle') }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<style scoped>
.input-field {
  @apply w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 px-3 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500;
}
</style>
