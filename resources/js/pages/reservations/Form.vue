<script setup lang="ts">
// import AppLayout from '@/Layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue';
import { Link, useForm, router } from '@inertiajs/vue3'
import { ArrowLeft, Calculator, Car, Check, ChevronDown, Search } from 'lucide-vue-next'
import { computed, ref, watch } from 'vue'

const props = defineProps<{
  customers: any[]
  branches: any[]
  insurancePlans: any[]
  extraServices: any[]
  availableVehicles: any[]
  query: { pickup_date?: string; dropoff_date?: string; branch_id?: string; category_id?: string }
}>()

const form = useForm({
  customer_id:        '',
  vehicle_id:         '',
  pickup_branch_id:   props.query.branch_id ?? '',
  dropoff_branch_id:  props.query.branch_id ?? '',
  insurance_plan_id:  '',
  pickup_date:        props.query.pickup_date ?? '',
  dropoff_date:       props.query.dropoff_date ?? '',
  extra_service_ids:  [] as string[],
  promo_code:         '',
  notes:              '',
})

const pricing = ref<any>(null)
const calculating = ref(false)
const vehicles = ref<any[]>(props.availableVehicles ?? [])

// Search availability
const searchAvailability = () => {
  if (!form.pickup_date || !form.dropoff_date || !form.pickup_branch_id) return
  router.get(
    '/reservations/create',
    { pickup_date: form.pickup_date, dropoff_date: form.dropoff_date, branch_id: form.pickup_branch_id },
    { preserveState: true, replace: true, only: ['availableVehicles'] },
  )
}

watch([() => form.pickup_date, () => form.dropoff_date, () => form.pickup_branch_id], searchAvailability)

// Live price calculation
const calculatePrice = async () => {
  if (!form.vehicle_id || !form.pickup_date || !form.dropoff_date) return
  calculating.value = true
  try {
    const res = await fetch('/reservations/calculate-price', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({
        vehicle_id:        form.vehicle_id,
        pickup_date:       form.pickup_date,
        dropoff_date:      form.dropoff_date,
        insurance_plan_id: form.insurance_plan_id || undefined,
        extra_service_ids: form.extra_service_ids,
        promo_code:        form.promo_code || undefined,
      }),
    })
    pricing.value = await res.json()
  } finally {
    calculating.value = false
  }
}

watch([() => form.vehicle_id, () => form.insurance_plan_id, () => form.extra_service_ids, () => form.promo_code], calculatePrice)

const toggleExtra = (id: string) => {
  const idx = form.extra_service_ids.indexOf(id)
  if (idx === -1) form.extra_service_ids.push(id)
  else form.extra_service_ids.splice(idx, 1)
}

const selectedVehicle = computed(() => vehicles.value.find((v) => v.id === form.vehicle_id))

const days = computed(() => {
  if (!form.pickup_date || !form.dropoff_date) return 0
  return Math.max(1, Math.ceil((new Date(form.dropoff_date).getTime() - new Date(form.pickup_date).getTime()) / 86400000))
})

const submit = () => form.post('/reservations')

const fmt = (v: number) => `${Number(v).toLocaleString()} MAD`
</script>

<template>
  <!-- <AppLayout> -->
    <!-- <template #header>
      <div class="flex items-center gap-3">
        <Link href="/reservations" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
          <ArrowLeft class="h-4 w-4" />
        </Link>
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">New Reservation</h1>
      </div>
    </template> -->
     <Heading
      variant="small"
      title="Maintenance"
      description="View and schedule maintenance for your vehicles"
    />


    <div class="p-4 lg:p-6">
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Left: Form -->
        <div class="lg:col-span-2 space-y-5">

          <!-- Step 1: Dates & Branch -->
          <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <span class="h-6 w-6 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center">1</span>
              Dates & Branches
            </h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Pickup Date *</label>
                <input v-model="form.pickup_date" type="datetime-local" class="input-field" />
                <p v-if="form.errors.pickup_date" class="mt-1 text-xs text-red-500">{{ form.errors.pickup_date }}</p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Dropoff Date *</label>
                <input v-model="form.dropoff_date" type="datetime-local" class="input-field" />
                <p v-if="form.errors.dropoff_date" class="mt-1 text-xs text-red-500">{{ form.errors.dropoff_date }}</p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Pickup Branch *</label>
                <select v-model="form.pickup_branch_id" class="input-field">
                  <option value="">Select branch</option>
                  <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }} — {{ b.city }}</option>
                </select>
                <p v-if="form.errors.pickup_branch_id" class="mt-1 text-xs text-red-500">{{ form.errors.pickup_branch_id }}</p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Dropoff Branch *</label>
                <select v-model="form.dropoff_branch_id" class="input-field">
                  <option value="">Select branch</option>
                  <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }} — {{ b.city }}</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Step 2: Vehicle Selection -->
          <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <span class="h-6 w-6 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center">2</span>
              Choose Vehicle
              <span v-if="vehicles.length" class="text-xs font-normal text-gray-400">({{ vehicles.length }} available)</span>
            </h2>

            <div v-if="!form.pickup_date || !form.dropoff_date" class="py-8 text-center text-sm text-gray-400">
              Select dates above to see available vehicles.
            </div>

            <div v-else-if="!vehicles.length" class="py-8 text-center text-sm text-gray-400">
              No vehicles available for selected dates and branch.
            </div>

            <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <button
                v-for="v in vehicles"
                :key="v.id"
                type="button"
                :class="[
                  'text-left p-4 rounded-xl border-2 transition-all',
                  form.vehicle_id === v.id
                    ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600',
                ]"
                @click="form.vehicle_id = v.id"
              >
                <div class="flex items-start justify-between">
                  <div>
                    <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ v.year }} {{ v.make }} {{ v.model }}</p>
                    <p class="text-xs text-gray-400 mt-0.5 capitalize">{{ v.color }} · {{ v.transmission }} · {{ v.seat_count }} seats</p>
                  </div>
                  <div v-if="form.vehicle_id === v.id" class="h-5 w-5 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0">
                    <Check class="h-3 w-3 text-white" />
                  </div>
                </div>
                <p class="mt-2 text-sm font-bold text-blue-600">{{ fmt(v.category?.base_price_per_day) }}<span class="text-xs font-normal text-gray-400"> / day</span></p>
              </button>
            </div>
            <p v-if="form.errors.vehicle_id" class="mt-2 text-xs text-red-500">{{ form.errors.vehicle_id }}</p>
          </div>

          <!-- Step 3: Customer -->
          <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <span class="h-6 w-6 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center">3</span>
              Customer
            </h2>
            <select v-model="form.customer_id" class="input-field">
              <option value="">Select customer...</option>
              <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.user?.name }} — {{ c.license_number }}</option>
            </select>
            <p v-if="form.errors.customer_id" class="mt-1 text-xs text-red-500">{{ form.errors.customer_id }}</p>
          </div>

          <!-- Step 4: Insurance & Extras -->
          <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <span class="h-6 w-6 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center">4</span>
              Insurance & Add-ons
            </h2>

            <!-- Insurance -->
            <p class="text-xs font-medium text-gray-500 mb-2">Insurance Plan</p>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 mb-5">
              <button
                type="button"
                :class="['p-3 rounded-xl border-2 text-left transition-all', !form.insurance_plan_id ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700']"
                @click="form.insurance_plan_id = ''"
              >
                <p class="text-sm font-medium text-gray-900 dark:text-white">No Insurance</p>
                <p class="text-xs text-gray-400">Travel at your own risk</p>
              </button>
              <button
                v-for="plan in insurancePlans"
                :key="plan.id"
                type="button"
                :class="['p-3 rounded-xl border-2 text-left transition-all', form.insurance_plan_id === plan.id ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700']"
                @click="form.insurance_plan_id = plan.id"
              >
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ plan.name }}</p>
                <p class="text-xs text-gray-400">{{ fmt(plan.price_per_day) }}/day</p>
              </button>
            </div>

            <!-- Extras -->
            <p class="text-xs font-medium text-gray-500 mb-2">Extra Services</p>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="s in extraServices"
                :key="s.id"
                type="button"
                :class="[
                  'flex items-center gap-2 px-3 py-2 rounded-xl border text-xs font-medium transition-all',
                  form.extra_service_ids.includes(s.id)
                    ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400'
                    : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-gray-300',
                ]"
                @click="toggleExtra(s.id)"
              >
                <Check v-if="form.extra_service_ids.includes(s.id)" class="h-3 w-3" />
                {{ s.name }} — {{ fmt(s.price) }}{{ s.type === 'per_day' ? '/day' : ' flat' }}
              </button>
            </div>

            <!-- Promo code -->
            <div class="mt-5">
              <label class="block text-xs font-medium text-gray-500 mb-1">Promo Code</label>
              <input v-model="form.promo_code" type="text" placeholder="Enter code..." class="input-field max-w-xs uppercase" />
            </div>

            <!-- Notes -->
            <div class="mt-4">
              <label class="block text-xs font-medium text-gray-500 mb-1">Notes</label>
              <textarea v-model="form.notes" rows="2" class="input-field resize-none" placeholder="Optional notes..." />
            </div>
          </div>
        </div>

        <!-- Right: Pricing summary -->
        <div class="lg:col-span-1">
          <div class="sticky top-6 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
              <Calculator class="h-4 w-4 text-blue-600" /> Price Summary
            </h2>

            <div v-if="!form.vehicle_id || !days" class="py-6 text-center text-xs text-gray-400">
              Select dates and a vehicle to see pricing.
            </div>

            <div v-else class="space-y-3">
              <!-- Vehicle -->
              <div v-if="selectedVehicle" class="rounded-xl bg-gray-50 dark:bg-gray-800 p-3">
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ selectedVehicle.make }} {{ selectedVehicle.model }}</p>
                <p class="text-xs text-gray-400">{{ days }} day{{ days > 1 ? 's' : '' }} × {{ fmt(selectedVehicle.category?.base_price_per_day) }}</p>
              </div>

              <div v-if="calculating" class="flex items-center gap-2 text-xs text-gray-400 py-2">
                <div class="h-3 w-3 border-2 border-blue-600 border-t-transparent rounded-full animate-spin" />
                Calculating...
              </div>

              <template v-if="pricing && !calculating">
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-500">Base ({{ pricing.days }}d)</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ fmt(pricing.base_amount) }}</span>
                  </div>
                  <div v-if="pricing.insurance_amount > 0" class="flex justify-between">
                    <span class="text-gray-500">Insurance</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ fmt(pricing.insurance_amount) }}</span>
                  </div>
                  <div v-if="pricing.extras_amount > 0" class="flex justify-between">
                    <span class="text-gray-500">Extras</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ fmt(pricing.extras_amount) }}</span>
                  </div>
                  <div v-if="pricing.discount_amount > 0" class="flex justify-between text-green-600">
                    <span>Promo discount</span>
                    <span>- {{ fmt(pricing.discount_amount) }}</span>
                  </div>
                  <div class="border-t border-gray-100 dark:border-gray-800 pt-2 flex justify-between font-bold text-gray-900 dark:text-white text-base">
                    <span>Total</span>
                    <span>{{ fmt(pricing.total_amount) }}</span>
                  </div>
                </div>

                <div v-if="pricing.promotion" class="flex items-center gap-2 rounded-xl bg-green-50 dark:bg-green-900/20 px-3 py-2 text-xs text-green-700 dark:text-green-400">
                  <Check class="h-3.5 w-3.5 flex-shrink-0" />
                  Promo "{{ pricing.promotion.code }}" applied — {{ pricing.promotion.type === 'percentage' ? `${pricing.promotion.value}% off` : `${fmt(pricing.promotion.value)} off` }}
                </div>
              </template>
            </div>

            <!-- Submit -->
            <button
              type="button"
              :disabled="form.processing || !form.vehicle_id || !form.customer_id"
              class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-semibold transition-colors"
              @click="submit"
            >
              {{ form.processing ? 'Creating...' : 'Confirm Reservation' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  <!-- </AppLayout> -->
</template>

<style scoped>
.input-field { @apply w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 px-3 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500; }
</style>
