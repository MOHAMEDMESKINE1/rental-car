<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { Car, ChevronLeft, ChevronRight, Filter, Plus, Search, Wrench } from 'lucide-vue-next'
import { ref, watch } from 'vue'

const props = defineProps<{
  vehicles: any
  categories: any[]
  branches: any[]
  filters: Record<string, string>
}>()

const search = ref(props.filters['filter[plate_number]'] ?? '')
const filterStatus = ref(props.filters['filter[status]'] ?? '')
const filterCategory = ref(props.filters['filter[category_id]'] ?? '')
const filterBranch = ref(props.filters['filter[branch_id]'] ?? '')

watch([search, filterStatus, filterCategory, filterBranch], () => {
  router.get(
    '/vehicles',
    {
      'filter[plate_number]': search.value || undefined,
      'filter[status]': filterStatus.value || undefined,
      'filter[category_id]': filterCategory.value || undefined,
      'filter[branch_id]': filterBranch.value || undefined,
    },
    { preserveState: true, replace: true },
  )
})

const statusConfig: Record<string, { label: string; class: string }> = {
  available:   { label: 'Available',   class: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' },
  reserved:    { label: 'Reserved',    class: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' },
  rented:      { label: 'Rented',      class: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' },
  maintenance: { label: 'Maintenance', class: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' },
  retired:     { label: 'Retired',     class: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' },
}

const fuelIcon: Record<string, string> = { gasoline: '⛽', diesel: '🛢️', electric: '⚡', hybrid: '🌿' }
</script>

<template>
  <AppLayout>
    <template #header>
      <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Fleet Management</h1>
    </template>

    <div class="p-4 lg:p-6 space-y-5">
      <!-- Toolbar -->
      <div class="flex flex-wrap items-center gap-3">
        <!-- Search -->
        <div class="relative flex-1 min-w-[200px] max-w-sm">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
          <input
            v-model="search"
            type="text"
            placeholder="Search plate number..."
            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Filters -->
        <select
          v-model="filterStatus"
          class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">All Status</option>
          <option value="available">Available</option>
          <option value="reserved">Reserved</option>
          <option value="rented">Rented</option>
          <option value="maintenance">Maintenance</option>
          <option value="retired">Retired</option>
        </select>

        <select
          v-model="filterCategory"
          class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">All Categories</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>

        <select
          v-model="filterBranch"
          class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">All Branches</option>
          <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
        </select>

        <Link
          href="/vehicles/create"
          class="ml-auto flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 text-sm font-semibold transition-colors"
        >
          <Plus class="h-4 w-4" /> Add Vehicle
        </Link>
      </div>

      <!-- Table -->
      <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Vehicle</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Plate</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Branch</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Mileage</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Fuel</th>
                <th class="px-4 py-3.5" />
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr
                v-for="v in vehicles.data"
                :key="v.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors"
              >
                <!-- Vehicle info -->
                <td class="px-5 py-4">
                  <div class="flex items-center gap-3">
                    <div class="h-10 w-14 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center overflow-hidden flex-shrink-0">
                      <img
                        v-if="v.thumbnail_url"
                        :src="v.thumbnail_url"
                        :alt="v.make"
                        class="h-full w-full object-cover"
                      />
                      <Car v-else class="h-5 w-5 text-gray-400" />
                    </div>
                    <div>
                      <p class="font-semibold text-gray-900 dark:text-white">{{ v.year }} {{ v.make }} {{ v.model }}</p>
                      <p class="text-xs text-gray-400 capitalize">{{ v.color }} · {{ v.transmission }} · {{ fuelIcon[v.fuel_type] }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-4">
                  <code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded font-mono">{{ v.plate_number }}</code>
                </td>
                <td class="px-4 py-4 text-gray-600 dark:text-gray-400">{{ v.category?.name }}</td>
                <td class="px-4 py-4 text-gray-600 dark:text-gray-400">{{ v.branch?.city }}</td>
                <td class="px-4 py-4">
                  <span
                    :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', statusConfig[v.status]?.class]"
                  >
                    {{ statusConfig[v.status]?.label ?? v.status }}
                  </span>
                </td>
                <td class="px-4 py-4 text-gray-600 dark:text-gray-400">{{ v.mileage.toLocaleString() }} km</td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2">
                    <div class="h-1.5 w-16 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                      <div
                        :class="['h-full rounded-full transition-all', v.fuel_level > 50 ? 'bg-green-500' : v.fuel_level > 25 ? 'bg-yellow-500' : 'bg-red-500']"
                        :style="{ width: v.fuel_level + '%' }"
                      />
                    </div>
                    <span class="text-xs text-gray-500">{{ v.fuel_level }}%</span>
                  </div>
                </td>
                <td class="px-4 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="`/vehicles/${v.id}`"
                      class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    >
                      View
                    </Link>
                    <Link
                      :href="`/vehicles/${v.id}/edit`"
                      class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400"
                    >
                      Edit
                    </Link>
                  </div>
                </td>
              </tr>
              <tr v-if="!vehicles.data?.length">
                <td colspan="8" class="px-5 py-10 text-center text-gray-400 text-sm">No vehicles found.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800">
          <p class="text-xs text-gray-500">
            Showing {{ vehicles.from }}–{{ vehicles.to }} of {{ vehicles.total }} vehicles
          </p>
          <div class="flex gap-1">
            <Link
              v-if="vehicles.prev_page_url"
              :href="vehicles.prev_page_url"
              class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800"
            >
              <ChevronLeft class="h-4 w-4" />
            </Link>
            <Link
              v-if="vehicles.next_page_url"
              :href="vehicles.next_page_url"
              class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800"
            >
              <ChevronRight class="h-4 w-4" />
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
