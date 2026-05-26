<script setup lang="ts">
// import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ChevronLeft, ChevronRight, Plus } from 'lucide-vue-next'
import { ref, watch } from 'vue'

const props = defineProps<{
  reservations: any
  filters: Record<string, string>
}>()

const filterStatus = ref(props.filters['filter[status]'] ?? '')

watch(filterStatus, (val) => {
  router.get('/reservations', { 'filter[status]': val || undefined }, { preserveState: true, replace: true })
})

const statusConfig: Record<string, string> = {
  pending:   'bg-yellow-100 text-yellow-700',
  confirmed: 'bg-blue-100 text-blue-700',
  converted: 'bg-green-100 text-green-700',
  cancelled: 'bg-red-100 text-red-700',
  expired:   'bg-gray-100 text-gray-600',
}

const formatDate = (d: string) =>
  new Date(d).toLocaleDateString('fr-MA', { day: '2-digit', month: 'short', year: 'numeric' })
</script>

<template>
  <!-- <AppLayout> -->
    <template #header>
      <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Reservations</h1>
    </template>

    <div class="p-4 lg:p-6 space-y-5">
      <div class="flex flex-wrap items-center gap-3">
        <select
          v-model="filterStatus"
          class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="converted">Converted</option>
          <option value="cancelled">Cancelled</option>
        </select>
        <Link
          href="/reservations/create"
          class="ml-auto flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 text-sm font-semibold"
        >
          <Plus class="h-4 w-4" /> New Reservation
        </Link>
      </div>

      <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase">Reservation #</th>
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
                v-for="r in reservations.data"
                :key="r.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors"
              >
                <td class="px-5 py-4">
                  <code class="text-xs font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">{{ r.reservation_number }}</code>
                </td>
                <td class="px-4 py-4 font-medium text-gray-900 dark:text-white">{{ r.customer?.user?.name }}</td>
                <td class="px-4 py-4 text-gray-600 dark:text-gray-400">{{ r.vehicle?.make }} {{ r.vehicle?.model }}</td>
                <td class="px-4 py-4 text-xs text-gray-600 dark:text-gray-400">{{ formatDate(r.pickup_date) }}</td>
                <td class="px-4 py-4 text-xs text-gray-600 dark:text-gray-400">{{ formatDate(r.dropoff_date) }}</td>
                <td class="px-4 py-4 font-semibold text-gray-900 dark:text-white">{{ Number(r.total_amount).toLocaleString() }} MAD</td>
                <td class="px-4 py-4">
                  <span :class="['inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium', statusConfig[r.status]]">
                    {{ r.status }}
                  </span>
                </td>
                <td class="px-4 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <Link :href="`/reservations/${r.id}`" class="text-xs font-medium text-blue-600">View</Link>
                    <Link
                      v-if="r.status === 'confirmed'"
                      :href="`/rentals/from-reservation/${r.id}`"
                      class="text-xs font-medium text-green-600"
                    >
                      Start Rental
                    </Link>
                  </div>
                </td>
              </tr>
              <tr v-if="!reservations.data?.length">
                <td colspan="8" class="px-5 py-10 text-center text-gray-400 text-sm">No reservations found.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800">
          <p class="text-xs text-gray-500">Showing {{ reservations.from }}–{{ reservations.to }} of {{ reservations.total }}</p>
          <div class="flex gap-1">
            <Link v-if="reservations.prev_page_url" :href="reservations.prev_page_url" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800"><ChevronLeft class="h-4 w-4" /></Link>
            <Link v-if="reservations.next_page_url" :href="reservations.next_page_url" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800"><ChevronRight class="h-4 w-4" /></Link>
          </div>
        </div>
      </div>
    </div>
  <!-- </AppLayout> -->
</template>
