<script setup lang="ts">
// import AppLayout from '@/Layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue';
import { BarChart3, Car, TrendingUp, Users } from 'lucide-vue-next'
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'

const props = defineProps<{
  summary: {
    total_revenue: number
    total_rentals: number
    avg_rental_duration: number
    avg_rental_value: number
    total_customers: number
    fleet_utilization: number
  }
  revenueByMonth: { month: string; revenue: number; rentals: number }[]
  revenueByCategory: { name: string; revenue: number; rentals: number }[]
  revenueByBranch: { name: string; city: string; revenue: number; rentals: number }[]
  topVehicles: { make: string; model: string; plate_number: string; rentals: number; revenue: number }[]
  topCustomers: { name: string; email: string; rentals: number; spent: number }[]
}>()

const fmt = (v: number) => `${Number(v).toLocaleString('fr-MA')} MAD`

const revenueChartOpts = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false } },
  plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
  xaxis: { categories: props.revenueByMonth.map((r) => r.month), labels: { style: { colors: '#9ca3af' } } },
  yaxis: { labels: { style: { colors: '#9ca3af' }, formatter: (v: number) => `${(v / 1000).toFixed(0)}k` } },
  colors: ['#3b82f6'],
  grid: { borderColor: '#f3f4f6' },
  dataLabels: { enabled: false },
  tooltip: { y: { formatter: (v: number) => fmt(v) } },
}))

const revenueSeries = computed(() => [{ name: 'Revenue (MAD)', data: props.revenueByMonth.map((r) => r.revenue) }])

const categoryOpts = computed(() => ({
  chart: { type: 'pie' },
  labels: props.revenueByCategory.map((c) => c.name),
  colors: ['#3b82f6', '#8b5cf6', '#f59e0b', '#10b981', '#ef4444'],
  legend: { position: 'bottom' },
  dataLabels: { formatter: (v: number) => `${v.toFixed(1)}%` },
}))

const categorySeries = computed(() => props.revenueByCategory.map((c) => Number(c.revenue)))
</script>

<template>
  <!-- <AppLayout> -->
      <Heading
      variant="small"
      title="Reports"
      description="View and analyze your vehicle rental data"
    />


    <div class="p-4 lg:p-6 space-y-6">
      <!-- KPI Row -->
      <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div v-for="kpi in [
          { label: 'Total Revenue', value: fmt(summary.total_revenue), icon: TrendingUp, color: 'text-blue-600', bg: 'bg-blue-100 dark:bg-blue-900/30' },
          { label: 'Total Rentals', value: summary.total_rentals.toLocaleString(), icon: Car, color: 'text-purple-600', bg: 'bg-purple-100 dark:bg-purple-900/30' },
          { label: 'Avg Duration', value: `${Number(summary.avg_rental_duration ?? 0).toFixed(1)} days`, icon: BarChart3, color: 'text-indigo-600', bg: 'bg-indigo-100 dark:bg-indigo-900/30' },
          { label: 'Fleet Utilization', value: `${summary.fleet_utilization}%`, icon: Users, color: 'text-green-600', bg: 'bg-green-100 dark:bg-green-900/30' },
        ]" :key="kpi.label"
          class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
          <div :class="['h-10 w-10 rounded-xl flex items-center justify-center mb-3', kpi.bg]">
            <component :is="kpi.icon" :class="['h-5 w-5', kpi.color]" />
          </div>
          <p class="text-xl font-bold text-gray-900 dark:text-white">{{ kpi.value }}</p>
          <p class="text-xs text-gray-500 mt-0.5">{{ kpi.label }}</p>
        </div>
      </div>

      <!-- Charts row 1 -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Monthly Revenue (12 months)</h3>
          <VueApexCharts type="bar" height="260" :options="revenueChartOpts" :series="revenueSeries" />
        </div>
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Revenue by Category</h3>
          <VueApexCharts v-if="categorySeries.length" type="pie" height="260" :options="categoryOpts" :series="categorySeries" />
          <p v-else class="text-xs text-gray-400 text-center py-10">No data</p>
        </div>
      </div>

      <!-- Branch breakdown -->
      <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Revenue by Branch</h3>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100 dark:border-gray-800">
                <th class="text-left py-2 text-xs font-semibold text-gray-500 uppercase">Branch</th>
                <th class="text-right py-2 text-xs font-semibold text-gray-500 uppercase">Rentals</th>
                <th class="text-right py-2 text-xs font-semibold text-gray-500 uppercase">Revenue</th>
                <th class="py-2 w-40" />
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-for="b in revenueByBranch" :key="b.name">
                <td class="py-3">
                  <p class="font-medium text-gray-900 dark:text-white">{{ b.name }}</p>
                  <p class="text-xs text-gray-400">{{ b.city }}</p>
                </td>
                <td class="py-3 text-right text-gray-600 dark:text-gray-400">{{ b.rentals }}</td>
                <td class="py-3 text-right font-semibold text-gray-900 dark:text-white">{{ fmt(b.revenue) }}</td>
                <td class="py-3">
                  <div class="h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden ml-4">
                    <div
                      class="h-full bg-blue-500 rounded-full"
                      :style="{
                        width: revenueByBranch.length
                          ? `${(Number(b.revenue) / Math.max(...revenueByBranch.map((x) => Number(x.revenue)))) * 100}%`
                          : '0%',
                      }"
                    />
                  </div>
                </td>
              </tr>
              <tr v-if="!revenueByBranch.length">
                <td colspan="4" class="py-6 text-center text-xs text-gray-400">No data available.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Top vehicles + top customers -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top 10 Vehicles</h3>
          <div class="space-y-2">
            <div v-for="(v, i) in topVehicles" :key="v.plate_number" class="flex items-center gap-3 py-2">
              <span class="h-6 w-6 rounded-full bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-500 flex items-center justify-center flex-shrink-0">{{ i + 1 }}</span>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ v.make }} {{ v.model }}</p>
                <p class="text-xs text-gray-400">{{ v.plate_number }} · {{ v.rentals }} rentals</p>
              </div>
              <p class="text-sm font-semibold text-gray-900 dark:text-white flex-shrink-0">{{ fmt(v.revenue) }}</p>
            </div>
            <p v-if="!topVehicles.length" class="text-xs text-gray-400 text-center py-4">No data</p>
          </div>
        </div>

        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top 10 Customers</h3>
          <div class="space-y-2">
            <div v-for="(c, i) in topCustomers" :key="c.email" class="flex items-center gap-3 py-2">
              <span class="h-6 w-6 rounded-full bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-500 flex items-center justify-center flex-shrink-0">{{ i + 1 }}</span>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ c.name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ c.email }} · {{ c.rentals }} rentals</p>
              </div>
              <p class="text-sm font-semibold text-gray-900 dark:text-white flex-shrink-0">{{ fmt(c.spent) }}</p>
            </div>
            <p v-if="!topCustomers.length" class="text-xs text-gray-400 text-center py-4">No data</p>
          </div>
        </div>
      </div>
    </div>
  <!-- </AppLayout> -->
</template>
