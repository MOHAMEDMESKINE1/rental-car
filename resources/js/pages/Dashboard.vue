<!-- <script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { dashboard } from '@/routes';
import type { Team } from '@/types';

defineOptions({
    layout: (props: { currentTeam?: Team | null }) => ({
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: props.currentTeam
                    ? dashboard(props.currentTeam.slug)
                    : '/',
            },
        ],
    }),
});
</script>

<template>
    <Head title="Dashboard" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
        </div>
        <div
            class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
        >
            <PlaceholderPattern />
        </div>
    </div>
</template> -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { AlertTriangle, ArrowRight, Car, Clock, TrendingUp, Users } from 'lucide-vue-next'
import { computed } from 'vue'

import VueApexCharts from 'vue3-apexcharts'

const props = defineProps<{
  stats: {
    total_vehicles: number
    available_vehicles: number
    active_rentals: number
    overdue_rentals: number
    pending_reservations: number
    total_customers: number
    revenue_today: number
    revenue_month: number
  }
  revenueChart: { month: string; revenue: number; count: number }[]
  vehicleStatusChart: { status: string; count: number }[]
  recentRentals: any[]
  upcomingReservations: any[]
  overdueRentals: any[]
}>()

const statusColors: Record<string, string> = {
  available: '#22c55e',
  reserved: '#f59e0b',
  rented: '#3b82f6',
  maintenance: '#8b5cf6',
  retired: '#6b7280',
}

const revenueOptions = computed(() => ({
  chart: { type: 'area', toolbar: { show: false }, sparkline: { enabled: false } },
  stroke: { curve: 'smooth', width: 2 },
  fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.01 } },
  xaxis: { categories: props.revenueChart.map((r) => r.month), labels: { style: { colors: '#9ca3af' } } },
  yaxis: { labels: { style: { colors: '#9ca3af' }, formatter: (v: number) => `${v.toLocaleString()} MAD` } },
  colors: ['#3b82f6'],
  grid: { borderColor: '#f3f4f6' },
  tooltip: { y: { formatter: (v: number) => `${v.toLocaleString()} MAD` } },
}))

const revenueSeries = computed(() => [{ name: 'Revenue', data: props.revenueChart.map((r) => r.revenue) }])

const donutOptions = computed(() => ({
  chart: { type: 'donut' },
  labels: props.vehicleStatusChart.map((s) => s.status.charAt(0).toUpperCase() + s.status.slice(1)),
  colors: props.vehicleStatusChart.map((s) => statusColors[s.status] ?? '#6b7280'),
  legend: { position: 'bottom' },
  plotOptions: { pie: { donut: { size: '65%' } } },
}))

const donutSeries = computed(() => props.vehicleStatusChart.map((s) => s.count))

const formatCurrency = (val: number) => `${Number(val).toLocaleString('fr-MA')} MAD`

const statusBadge = (status: string) => {
  const map: Record<string, string> = {
    active: 'bg-blue-100 text-blue-700',
    overdue: 'bg-red-100 text-red-700',
    completed: 'bg-green-100 text-green-700',
    pending: 'bg-yellow-100 text-yellow-700',
    confirmed: 'bg-indigo-100 text-indigo-700',
  }
  return map[status] ?? 'bg-gray-100 text-gray-600'
}
</script>

<template>

   <Head title="Dashboard" />

    <div class="p-4 lg:p-6 space-y-6">

      <!-- Overdue alert -->
      <div
        v-if="overdueRentals.length"
        class="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 px-4 py-3"
      >
        <AlertTriangle class="h-5 w-5 text-red-500 flex-shrink-0" />
        <p class="text-sm text-red-800 dark:text-red-400 font-medium">
          {{ overdueRentals.length }} rental{{ overdueRentals.length > 1 ? 's are' : ' is' }} overdue!
        </p>
        <Link
          href="/rentals?filter[status]=overdue"
          class="ml-auto text-sm font-semibold text-red-700 dark:text-red-400 flex items-center gap-1"
        >
          View <ArrowRight class="h-4 w-4" />
        </Link>
      </div>
    </div>
    <div class="p-4 lg:p-6 space-y-6">
      <!-- Overdue alert -->
      <div
        v-if="overdueRentals.length"
        class="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 px-4 py-3"
      >
        <AlertTriangle class="h-5 w-5 text-red-500 flex-shrink-0" />
        <p class="text-sm text-red-800 dark:text-red-400 font-medium">
          {{ overdueRentals.length }} rental{{ overdueRentals.length > 1 ? 's are' : ' is' }} overdue!
        </p>
        <Link href="/rentals?filter[status]=overdue" class="ml-auto text-sm font-semibold text-red-700 dark:text-red-400 flex items-center gap-1">
          View <ArrowRight class="h-4 w-4" />
        </Link>
      </div>

      <!-- KPI Cards -->
      <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-5">
          <div class="flex items-center justify-between">
            <div class="h-10 w-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
              <Car class="h-5 w-5 text-blue-600" />
            </div>
            <span class="text-xs font-medium text-green-600 bg-green-50 dark:bg-green-900/20 px-2 py-0.5 rounded-full">
              {{ stats.available_vehicles }} free
            </span>
          </div>
          <p class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_vehicles }}</p>
          <p class="text-sm text-gray-500 mt-1">Total Vehicles</p>
        </div>

        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-5">
          <div class="flex items-center justify-between">
            <div class="h-10 w-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
              <Clock class="h-5 w-5 text-indigo-600" />
            </div>
            <span v-if="stats.overdue_rentals" class="text-xs font-medium text-red-600 bg-red-50 px-2 py-0.5 rounded-full">
              {{ stats.overdue_rentals }} overdue
            </span>
          </div>
          <p class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.active_rentals }}</p>
          <p class="text-sm text-gray-500 mt-1">Active Rentals</p>
        </div>

        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-5">
          <div class="flex items-center justify-between">
            <div class="h-10 w-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
              <Users class="h-5 w-5 text-purple-600" />
            </div>
          </div>
          <p class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_customers }}</p>
          <p class="text-sm text-gray-500 mt-1">Customers</p>
        </div>

        <div class="rounded-2xl bg-gradient-to-br from-blue-600 to-blue-700 p-5 text-white">
          <div class="flex items-center justify-between">
            <div class="h-10 w-10 rounded-xl bg-white/20 flex items-center justify-center">
              <TrendingUp class="h-5 w-5" />
            </div>
            <span class="text-xs font-medium bg-white/20 px-2 py-0.5 rounded-full">This month</span>
          </div>
          <p class="mt-4 text-2xl font-bold">{{ formatCurrency(stats.revenue_month) }}</p>
          <p class="text-sm text-blue-100 mt-1">Revenue</p>
          <p class="text-xs text-blue-200 mt-1">Today: {{ formatCurrency(stats.revenue_today) }}</p>
        </div>
      </div>

      <!-- Charts -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Revenue (Last 12 months)</h3>
          <VueApexCharts type="area" height="240" :options="revenueOptions" :series="revenueSeries" />
        </div>

        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Fleet Status</h3>
          <VueApexCharts type="donut" height="240" :options="donutOptions" :series="donutSeries" />
        </div>
      </div>

      <!-- Tables -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Recent Rentals -->
        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Recent Rentals</h3>
            <Link href="/rentals" class="text-xs text-blue-600 hover:underline flex items-center gap-1">
              View all <ArrowRight class="h-3.5 w-3.5" />
            </Link>
          </div>
          <div class="divide-y divide-gray-50 dark:divide-gray-800">
            <div v-for="rental in recentRentals" :key="rental.id" class="flex items-center gap-3 px-5 py-3">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ rental.customer?.user?.name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ rental.vehicle?.make }} {{ rental.vehicle?.model }} — {{ rental.rental_number }}</p>
              </div>
              <span :class="['text-xs font-medium px-2 py-0.5 rounded-full', statusBadge(rental.status)]">
                {{ rental.status }}
              </span>
            </div>
            <p v-if="!recentRentals.length" class="px-5 py-6 text-sm text-center text-gray-400">No rentals yet.</p>
          </div>
        </div>

        <!-- Upcoming Reservations -->
        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Upcoming Pickups</h3>
            <Link href="/reservations" class="text-xs text-blue-600 hover:underline flex items-center gap-1">
              View all <ArrowRight class="h-3.5 w-3.5" />
            </Link>
          </div>
          <div class="divide-y divide-gray-50 dark:divide-gray-800">
            <div v-for="res in upcomingReservations" :key="res.id" class="flex items-center gap-3 px-5 py-3">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ res.customer?.user?.name }}</p>
                <p class="text-xs text-gray-400 truncate">
                  {{ res.vehicle?.make }} {{ res.vehicle?.model }} · {{ new Date(res.pickup_date).toLocaleDateString('fr-MA') }}
                </p>
              </div>
              <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ Number(res.total_amount).toLocaleString() }} MAD</p>
            </div>
            <p v-if="!upcomingReservations.length" class="px-5 py-6 text-sm text-center text-gray-400">No upcoming reservations.</p>
          </div>
        </div>
      </div>
    </div>
</template>
