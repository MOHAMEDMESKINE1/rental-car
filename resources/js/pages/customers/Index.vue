<script setup lang="ts">
// import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/components/ui/button/Button.vue';
import { Link, router } from '@inertiajs/vue3'
import { ChevronLeft, ChevronRight, Icon, LoaderCircle, Plus, Search, ShieldAlert, ShieldCheck } from 'lucide-vue-next'
import { ref, watch } from 'vue'
import {create} from "@/routes/admin/customers";
const props = defineProps<{
  customers: any
  filters: Record<string, string>
}>()

const search = ref('')

watch(search, (val) => {
  router.get('/customers', { search: val || undefined }, { preserveState: true, replace: true })
})
</script>

<template>
  <!-- <AppLayout> -->
    <Header class="m-5">
      <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Customers</h1>
    </Header>

    <div class="p-4 lg:p-6 space-y-5">
      <div class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px] max-w-sm">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
          <input
            v-model="search"
            type="text"
            placeholder="Search by name, license..."
            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <ModalLink
        :as="Button" :href="create().url" #default="{ loading }"

          class="ml-auto flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 text-sm font-semibold"
        >
           <LoaderCircle v-if="loading" class="h-4 w-4 animate-spin" />
            <Plus v-else  /> Add Customer
        </ModalLink>
      </div>

      <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase">Customer</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">License</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Phone</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Rentals</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3.5" />
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr
                v-for="c in customers.data"
                :key="c.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors"
              >
                <td class="px-5 py-4">
                  <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-700 dark:text-blue-400 font-semibold text-sm flex-shrink-0">
                      {{ c.user?.name?.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <p class="font-medium text-gray-900 dark:text-white">{{ c.user?.name }}</p>
                      <p class="text-xs text-gray-400">{{ c.user?.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-4">
                  <p class="text-gray-700 dark:text-gray-300">{{ c.license_number ?? '—' }}</p>
                  <p v-if="c.license_expiry" class="text-xs text-gray-400">Exp: {{ c.license_expiry }}</p>
                </td>
                <td class="px-4 py-4 text-gray-600 dark:text-gray-400">{{ c.phone ?? '—' }}</td>
                <td class="px-4 py-4 text-gray-600 dark:text-gray-400">{{ c.rentals_count ?? 0 }}</td>
                <td class="px-4 py-4">
                  <span v-if="c.is_blacklisted" class="inline-flex items-center gap-1 text-xs font-medium text-red-700 bg-red-100 dark:bg-red-900/20 dark:text-red-400 px-2 py-0.5 rounded-full">
                    <ShieldAlert class="h-3 w-3" /> Blacklisted
                  </span>
                  <span v-else class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-100 dark:bg-green-900/20 dark:text-green-400 px-2 py-0.5 rounded-full">
                    <ShieldCheck class="h-3 w-3" /> Active
                  </span>
                </td>
                <td class="px-4 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <Link :href="`/customers/${c.id}`" class="text-xs font-medium text-blue-600">View</Link>
                    <Link :href="`/customers/${c.id}/edit`" class="text-xs font-medium text-gray-500">Edit</Link>
                  </div>
                </td>
              </tr>
              <tr v-if="!customers.data?.length">
                <td colspan="6" class="px-5 py-10 text-center text-gray-400 text-sm">No customers found.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Pagination -->
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800">
          <p class="text-xs text-gray-500">Showing {{ customers.from }}–{{ customers.to }} of {{ customers.total }}</p>
          <div class="flex gap-1">
            <Link v-if="customers.prev_page_url" :href="customers.prev_page_url" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800"><ChevronLeft class="h-4 w-4" /></Link>
            <Link v-if="customers.next_page_url" :href="customers.next_page_url" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800"><ChevronRight class="h-4 w-4" /></Link>
          </div>
        </div>
      </div>
    </div>
  <!-- </AppLayout> -->
</template>
