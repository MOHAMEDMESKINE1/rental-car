<script setup lang="ts">
// import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ArrowLeft, CheckCircle } from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps<{ rental: any }>()

const form = useForm({
  actual_dropoff_at:  new Date().toISOString().slice(0, 16),
  dropoff_mileage:    props.rental.pickup_mileage,
  dropoff_fuel_level: props.rental.pickup_fuel_level,
  notes:              '',
})

const plannedDropoff = new Date(props.rental.planned_dropoff_at)
const isLate = computed(() => new Date(form.actual_dropoff_at) > plannedDropoff)
const kmDriven = computed(() => Math.max(0, form.dropoff_mileage - props.rental.pickup_mileage))

const submit = () => {
  form.post(`/rentals/${props.rental.id}/return`)
}
</script>

<template>
  <!-- <AppLayout> -->
    <template #header>
      <div class="flex items-center gap-3">
        <Link :href="`/rentals/${rental.id}`" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
          <ArrowLeft class="h-4 w-4" />
        </Link>
        <div>
          <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Return Vehicle</h1>
          <p class="text-xs text-gray-400">{{ rental.rental_number }}</p>
        </div>
      </div>
    </template>

    <div class="p-4 lg:p-6">
      <div class="max-w-2xl space-y-6">
        <!-- Vehicle info summary -->
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
          <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Rental Summary</p>
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
              <p class="text-gray-500">Customer</p>
              <p class="font-medium text-gray-900 dark:text-white">{{ rental.customer?.user?.name }}</p>
            </div>
            <div>
              <p class="text-gray-500">Vehicle</p>
              <p class="font-medium text-gray-900 dark:text-white">{{ rental.vehicle?.make }} {{ rental.vehicle?.model }}</p>
            </div>
            <div>
              <p class="text-gray-500">Pickup Mileage</p>
              <p class="font-medium text-gray-900 dark:text-white">{{ rental.pickup_mileage?.toLocaleString() }} km</p>
            </div>
            <div>
              <p class="text-gray-500">Pickup Fuel</p>
              <p class="font-medium text-gray-900 dark:text-white">{{ rental.pickup_fuel_level }}%</p>
            </div>
            <div>
              <p class="text-gray-500">Planned Dropoff</p>
              <p class="font-medium" :class="isLate ? 'text-red-600' : 'text-gray-900 dark:text-white'">
                {{ new Date(rental.planned_dropoff_at).toLocaleDateString('fr-MA') }}
              </p>
            </div>
            <div v-if="isLate">
              <p class="text-red-500 text-xs font-semibold">⚠️ Late return — extra charges apply</p>
            </div>
          </div>
        </div>

        <!-- Return form -->
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 space-y-5">
          <p class="text-xs font-semibold text-gray-500 uppercase">Return Details</p>

          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Actual Return Date & Time *</label>
            <input
              v-model="form.actual_dropoff_at"
              type="datetime-local"
              class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="isLate ? 'border-red-300 dark:border-red-700' : ''"
            />
            <p v-if="isLate" class="mt-1 text-xs text-red-500">Late return — extra day charges will be applied.</p>
            <p v-if="form.errors.actual_dropoff_at" class="mt-1 text-xs text-red-500">{{ form.errors.actual_dropoff_at }}</p>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Return Mileage (km) *</label>
            <input
              v-model="form.dropoff_mileage"
              type="number"
              :min="rental.pickup_mileage"
              class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <p class="mt-1 text-xs text-gray-400">KM driven: {{ kmDriven.toLocaleString() }} km</p>
            <p v-if="form.errors.dropoff_mileage" class="mt-1 text-xs text-red-500">{{ form.errors.dropoff_mileage }}</p>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-500 mb-2">Return Fuel Level: {{ form.dropoff_fuel_level }}%</label>
            <input
              v-model="form.dropoff_fuel_level"
              type="range"
              min="0"
              max="100"
              step="5"
              class="w-full accent-blue-600"
            />
            <div class="flex justify-between text-xs text-gray-400 mt-1">
              <span>Empty</span>
              <span>Full</span>
            </div>
            <p v-if="form.dropoff_fuel_level < rental.pickup_fuel_level" class="mt-1 text-xs text-red-500">
              Fuel charges will apply (returned {{ rental.pickup_fuel_level - form.dropoff_fuel_level }}% less)
            </p>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Agent Notes</label>
            <textarea
              v-model="form.notes"
              rows="3"
              placeholder="Any observations about vehicle condition..."
              class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
            />
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
          <Link :href="`/rentals/${rental.id}`" class="px-4 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800">
            Cancel
          </Link>
          <button
            type="button"
            :disabled="form.processing"
            class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold disabled:opacity-60"
            @click="submit"
          >
            <CheckCircle class="h-4 w-4" />
            {{ form.processing ? 'Processing...' : 'Complete Return' }}
          </button>
        </div>
      </div>
    </div>
  <!-- </AppLayout> -->
</template>
