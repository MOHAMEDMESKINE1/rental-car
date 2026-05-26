<script setup lang="ts">
// import AppLayout from '@/Layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue';
import { Link, router } from '@inertiajs/vue3'
import { AlertTriangle, ChevronLeft, ChevronRight, Plus, Search } from 'lucide-vue-next'
import { ref, watch } from 'vue'

const props = defineProps<{
  rentals: any
  filters: Record<string, string>
  summary: { active: number; overdue: number; completed: number }
}>()

const filterStatus = ref(props.filters['filter[status]'] ?? '')

watch(filterStatus, (val) => {
  router.get('/rentals', { 'filter[status]': val || undefined }, { preserveState: true, replace: true })
})

const statusConfig: Record<string, { label: string; class: string }> = {
  active:    { label: 'Active',    class: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' },
  overdue:   { label: 'Overdue',   class: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' },
  completed: { label: 'Completed', class: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' },
  cancelled: { label: 'Cancelled', class: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' },
}

const isOverdue = (rental: any) =>
  rental.status === 'active' && new Date(rental.planned_dropoff_at) < new Date()

const formatDate = (d: string) =>
  new Date(d).toLocaleDateString('fr-MA', { day: '2-digit', month: 'short', year: 'numeric' })

const formatAmount = (val: number) => `${Number(val).toLocaleString()} MAD`
</script>

<template>
  <!-- <AppLayout> -->
     <Heading
      variant="small"
      title=" Rental"
      description=" View and schedule rental for your vehicles"
    />


    <div class="p-4 lg:p-6 space-y-5">
      <!-- Summary cards -->
      <div class="grid grid-cols-3 gap-4">
        <div
          v-for="(item, key) in [
            { label: 'Active', value: summary.active, class: 'text-blue-600' },
            { label: 'Overdue', value: summary.overdue, class: 'text-red-600' },
            { label: 'Completed Today', value: summary.completed, class: 'text-green-600' },
          ]"
          :key="item.label"
          class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 px-5 py-4 text-center"
        >
          <p :class="['text-2xl font-bold', item.class]">{{ item.value }}</p>
          <p class="text-xs text-gray-500 mt-1">{{ item.label }}</p>
        </div>
      </div>

      <!-- Toolbar -->
      <div class="flex flex-wrap items-center gap-3">
        <select
          v-model="filterStatus"
          class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="overdue">Overdue</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>

        <Link
          href="/reservations"
          class="ml-auto flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 text-sm font-semibold"
        >
          <Plus class="h-4 w-4" /> New Reservation
        </Link>
      </div>

      <!-- Table -->
      <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase">Rental #</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Customer</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Vehicle</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Pickup</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Dropoff</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Total</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3.5" />
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr
                v-for="rental in rentals.data"
                :key="rental.id"
                :class="[
                  'transition-colors',
                  isOverdue(rental)
                    ? 'bg-red-50/50 dark:bg-red-900/10 hover:bg-red-50 dark:hover:bg-red-900/20'
                    : 'hover:bg-gray-50 dark:hover:bg-gray-800/40',
                ]"
              >
                <td class="px-5 py-4">
                  <div class="flex items-center gap-2">
                    <AlertTriangle v-if="isOverdue(rental)" class="h-4 w-4 text-red-500 flex-shrink-0" />
                    <code class="text-xs font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">{{ rental.rental_number }}</code>
                  </div>
                </td>
                <td class="px-4 py-4">
                  <p class="font-medium text-gray-900 dark:text-white">{{ rental.customer?.user?.name }}</p>
                  <p class="text-xs text-gray-400">{{ rental.pickup_branch?.city }}</p>
                </td>
                <td class="px-4 py-4">
                  <p class="text-gray-700 dark:text-gray-300">{{ rental.vehicle?.make }} {{ rental.vehicle?.model }}</p>
                  <p class="text-xs text-gray-400">{{ rental.vehicle?.plate_number }}</p>
                </td>
                <td class="px-4 py-4 text-gray-600 dark:text-gray-400 text-xs">{{ formatDate(rental.planned_pickup_at) }}</td>
                <td class="px-4 py-4">
                  <p :class="['text-xs', isOverdue(rental) ? 'text-red-600 font-semibold' : 'text-gray-600 dark:text-gray-400']">
                    {{ formatDate(rental.planned_dropoff_at) }}
                  </p>
                </td>
                <td class="px-4 py-4 font-semibold text-gray-900 dark:text-white">{{ formatAmount(rental.total_amount) }}</td>
                <td class="px-4 py-4">
                  <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', statusConfig[isOverdue(rental) ? 'overdue' : rental.status]?.class]">
                    {{ isOverdue(rental) ? 'Overdue' : statusConfig[rental.status]?.label }}
                  </span>
                </td>
                <td class="px-4 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <Link :href="`/rentals/${rental.id}`" class="text-xs font-medium text-blue-600 hover:text-blue-700">View</Link>
                    <Link
                      v-if="rental.status === 'active'"
                      :href="`/rentals/${rental.id}/return`"
                      class="text-xs font-medium text-green-600 hover:text-green-700"
                    >
                      Return
                    </Link>
                  </div>
                </td>
              </tr>
              <tr v-if="!rentals.data?.length">
                <td colspan="8" class="px-5 py-10 text-center text-gray-400 text-sm">No rentals found.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800">
          <p class="text-xs text-gray-500">Showing {{ rentals.from }}–{{ rentals.to }} of {{ rentals.total }}</p>
          <div class="flex gap-1">
            <Link v-if="rentals.prev_page_url" :href="rentals.prev_page_url" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800"><ChevronLeft class="h-4 w-4" /></Link>
            <Link v-if="rentals.next_page_url" :href="rentals.next_page_url" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800"><ChevronRight class="h-4 w-4" /></Link>
          </div>
        </div>
      </div>
    </div>
  <!-- </AppLayout> -->
</template>
