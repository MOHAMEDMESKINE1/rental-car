<script setup lang="ts">
// import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import { ArrowLeft, Car, CheckCircle, CreditCard, FileText, MapPin, User, Wrench } from 'lucide-vue-next'
import { ref } from 'vue'

const props = defineProps<{ rental: any }>()

const activeTab = ref<'details' | 'payments' | 'inspections' | 'damages'>('details')

const paymentForm = useForm({
  amount: props.rental.total_amount,
  method: 'cash',
  notes: '',
})

const submitPayment = () => {
  paymentForm.post(`/rentals/${props.rental.id}/payments`, { preserveScroll: true })
}

const formatDate = (d: string) =>
  d ? new Date(d).toLocaleDateString('fr-MA', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'

const formatCurrency = (v: number) => `${Number(v).toLocaleString()} MAD`

const statusClass: Record<string, string> = {
  active:    'bg-blue-100 text-blue-700',
  overdue:   'bg-red-100 text-red-700',
  completed: 'bg-green-100 text-green-700',
  cancelled: 'bg-gray-100 text-gray-600',
}
</script>

<template>
  <!-- <AppLayout> -->
    <template #header>
      <div class="flex items-center gap-3">
        <Link href="/rentals" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
          <ArrowLeft class="h-4 w-4" />
        </Link>
        <div>
          <h1 class="text-lg font-semibold text-gray-900 dark:text-white">{{ rental.rental_number }}</h1>
          <p class="text-xs text-gray-400">Rental Details</p>
        </div>
        <span :class="['ml-2 text-xs font-medium px-2.5 py-0.5 rounded-full', statusClass[rental.status]]">
          {{ rental.status }}
        </span>
        <div class="ml-auto flex gap-2">
          <Link
            v-if="rental.status === 'active'"
            :href="`/rentals/${rental.id}/return`"
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold"
          >
            <CheckCircle class="h-4 w-4" /> Return Vehicle
          </Link>
        </div>
      </div>
    </template>

    <div class="p-4 lg:p-6 space-y-6">
      <!-- Top cards -->
      <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <!-- Customer -->
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
          <div class="flex items-center gap-2 mb-3">
            <User class="h-4 w-4 text-gray-400" />
            <p class="text-xs font-semibold text-gray-500 uppercase">Customer</p>
          </div>
          <p class="font-semibold text-gray-900 dark:text-white">{{ rental.customer?.user?.name }}</p>
          <p class="text-sm text-gray-500">{{ rental.customer?.user?.email }}</p>
          <p class="text-sm text-gray-500">{{ rental.customer?.phone }}</p>
          <p class="text-xs text-gray-400 mt-1">License: {{ rental.customer?.license_number }}</p>
          <Link :href="`/customers/${rental.customer_id}`" class="mt-2 inline-block text-xs text-blue-600 hover:underline">View profile →</Link>
        </div>

        <!-- Vehicle -->
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
          <div class="flex items-center gap-2 mb-3">
            <Car class="h-4 w-4 text-gray-400" />
            <p class="text-xs font-semibold text-gray-500 uppercase">Vehicle</p>
          </div>
          <p class="font-semibold text-gray-900 dark:text-white">{{ rental.vehicle?.year }} {{ rental.vehicle?.make }} {{ rental.vehicle?.model }}</p>
          <p class="text-sm text-gray-500">{{ rental.vehicle?.plate_number }}</p>
          <p class="text-sm text-gray-500 capitalize">{{ rental.vehicle?.color }} · {{ rental.vehicle?.transmission }}</p>
          <p class="text-xs text-gray-400 mt-1">Pickup: {{ rental.pickup_mileage?.toLocaleString() }} km | Fuel: {{ rental.pickup_fuel_level }}%</p>
          <Link :href="`/vehicles/${rental.vehicle_id}`" class="mt-2 inline-block text-xs text-blue-600 hover:underline">View vehicle →</Link>
        </div>

        <!-- Dates & Branches -->
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
          <div class="flex items-center gap-2 mb-3">
            <MapPin class="h-4 w-4 text-gray-400" />
            <p class="text-xs font-semibold text-gray-500 uppercase">Dates & Branches</p>
          </div>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500">Pickup</span>
              <span class="text-gray-900 dark:text-white font-medium">{{ formatDate(rental.planned_pickup_at) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Dropoff</span>
              <span class="text-gray-900 dark:text-white font-medium">{{ formatDate(rental.planned_dropoff_at) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">From</span>
              <span class="text-gray-900 dark:text-white font-medium">{{ rental.pickup_branch?.name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">To</span>
              <span class="text-gray-900 dark:text-white font-medium">{{ rental.dropoff_branch?.name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Agent</span>
              <span class="text-gray-900 dark:text-white font-medium">{{ rental.agent?.name ?? '—' }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden">
        <!-- Tab nav -->
        <div class="flex border-b border-gray-100 dark:border-gray-800 overflow-x-auto">
          <button
            v-for="tab in [
              { key: 'details',     label: 'Pricing', icon: FileText },
              { key: 'payments',    label: 'Payments', icon: CreditCard },
              { key: 'inspections', label: 'Inspections', icon: Car },
              { key: 'damages',     label: 'Damages', icon: Wrench },
            ]"
            :key="tab.key"
            :class="[
              'flex items-center gap-2 px-5 py-3.5 text-sm font-medium border-b-2 transition-colors whitespace-nowrap',
              activeTab === tab.key
                ? 'border-blue-600 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300',
            ]"
            @click="activeTab = tab.key as any"
          >
            <component :is="tab.icon" class="h-4 w-4" />
            {{ tab.label }}
          </button>
        </div>

        <!-- Tab: Pricing -->
        <div v-if="activeTab === 'details'" class="p-6">
          <div class="max-w-sm space-y-3">
            <div v-for="row in [
              { label: 'Base Rental',  value: rental.base_amount },
              { label: 'Insurance',    value: rental.insurance_amount },
              { label: 'Extra Services', value: rental.extras_amount },
              { label: 'Extra KM',     value: rental.extra_km_charges },
              { label: 'Late Return',  value: rental.late_return_charges },
              { label: 'Fuel Charges', value: rental.fuel_charges },
              { label: 'Damage Charges', value: rental.damage_charges },
            ]" :key="row.label" class="flex justify-between text-sm">
              <span class="text-gray-500">{{ row.label }}</span>
              <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(row.value) }}</span>
            </div>
            <div class="flex justify-between text-sm text-green-600">
              <span>Discount</span>
              <span>- {{ formatCurrency(rental.discount_amount) }}</span>
            </div>
            <div class="border-t border-gray-100 dark:border-gray-800 pt-3 flex justify-between font-bold text-gray-900 dark:text-white">
              <span>Total</span>
              <span>{{ formatCurrency(rental.total_amount) }}</span>
            </div>
          </div>

          <!-- Extras list -->
          <div v-if="rental.extra_services?.length" class="mt-6">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Extra Services</p>
            <div class="flex flex-wrap gap-2">
              <span v-for="s in rental.extra_services" :key="s.id" class="text-xs bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400 px-3 py-1 rounded-full">
                {{ s.name }} (×{{ s.pivot.quantity }}) — {{ formatCurrency(s.pivot.unit_price) }}
              </span>
            </div>
          </div>

          <!-- Insurance -->
          <div v-if="rental.insurance_plan" class="mt-4 p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800">
            <p class="text-xs font-semibold text-blue-700 dark:text-blue-400">{{ rental.insurance_plan.name }}</p>
            <p class="text-xs text-blue-600 mt-1">{{ rental.insurance_plan.description }}</p>
          </div>
        </div>

        <!-- Tab: Payments -->
        <div v-if="activeTab === 'payments'" class="p-6 space-y-4">
          <div class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-800">
            <div>
              <p class="text-xs text-gray-500">Total Due</p>
              <p class="text-xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(rental.total_amount) }}</p>
            </div>
            <div class="mx-4 h-8 w-px bg-gray-200 dark:bg-gray-700" />
            <div>
              <p class="text-xs text-gray-500">Paid</p>
              <p class="text-xl font-bold text-green-600">{{ formatCurrency(rental.payments?.filter((p: any) => p.status === 'completed').reduce((acc: number, p: any) => acc + Number(p.amount), 0)) }}</p>
            </div>
          </div>

          <!-- Payment list -->
          <div class="space-y-2">
            <div v-for="p in rental.payments" :key="p.id" class="flex items-center justify-between p-3 rounded-xl border border-gray-100 dark:border-gray-800">
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ p.method }}</p>
                <p class="text-xs text-gray-400">{{ formatDate(p.paid_at) }}</p>
              </div>
              <p class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(p.amount) }}</p>
            </div>
            <p v-if="!rental.payments?.length" class="text-sm text-center text-gray-400 py-4">No payments recorded.</p>
          </div>
        </div>

        <!-- Tab: Inspections -->
        <div v-if="activeTab === 'inspections'" class="p-6">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div v-for="insp in rental.inspections" :key="insp.id" class="p-4 rounded-xl border border-gray-100 dark:border-gray-800">
              <div class="flex items-center justify-between mb-3">
                <span :class="['text-xs font-semibold px-2 py-0.5 rounded-full', insp.type === 'pre_rental' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700']">
                  {{ insp.type === 'pre_rental' ? 'Pre-Rental' : 'Post-Rental' }}
                </span>
                <p class="text-xs text-gray-400">{{ formatDate(insp.inspected_at) }}</p>
              </div>
              <div class="grid grid-cols-2 gap-2 text-xs">
                <div v-for="side in ['front','back','left','right','interior']" :key="side" class="flex justify-between">
                  <span class="text-gray-500 capitalize">{{ side }}</span>
                  <span class="font-medium text-gray-700 dark:text-gray-300 capitalize">{{ insp[`condition_${side}`] }}</span>
                </div>
                <div class="flex justify-between col-span-2 border-t border-gray-100 dark:border-gray-800 pt-2 mt-1">
                  <span class="text-gray-500">Mileage</span>
                  <span class="font-medium text-gray-700 dark:text-gray-300">{{ insp.mileage?.toLocaleString() }} km</span>
                </div>
                <div class="flex justify-between col-span-2">
                  <span class="text-gray-500">Fuel</span>
                  <span class="font-medium text-gray-700 dark:text-gray-300">{{ insp.fuel_level }}%</span>
                </div>
              </div>
            </div>
            <p v-if="!rental.inspections?.length" class="text-sm text-gray-400 col-span-2 py-4 text-center">No inspections yet.</p>
          </div>
        </div>

        <!-- Tab: Damages -->
        <div v-if="activeTab === 'damages'" class="p-6">
          <div class="space-y-3">
            <div v-for="d in rental.damages" :key="d.id" class="p-4 rounded-xl border border-red-100 dark:border-red-900/30 bg-red-50/50 dark:bg-red-900/10">
              <div class="flex items-start justify-between">
                <div>
                  <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ d.description }}</p>
                  <p class="text-xs text-gray-500 mt-0.5 capitalize">Location: {{ d.location }} · Severity: {{ d.severity }}</p>
                </div>
                <span :class="['text-xs px-2 py-0.5 rounded-full', d.customer_liable ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600']">
                  {{ d.customer_liable ? 'Customer liable' : 'Not liable' }}
                </span>
              </div>
              <p class="text-sm font-semibold text-red-600 mt-2">Repair cost: {{ formatCurrency(d.repair_cost) }}</p>
            </div>
            <p v-if="!rental.damages?.length" class="text-sm text-gray-400 py-4 text-center">No damages reported.</p>
          </div>
        </div>
      </div>
    </div>
  <!-- </AppLayout> -->
</template>
