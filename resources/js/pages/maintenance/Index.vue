<script setup lang="ts">
// import AppLayout from '@/Layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue';
import { useForm, router } from '@inertiajs/vue3'
import { ChevronLeft, ChevronRight, Ham, Plus, Wrench } from 'lucide-vue-next'
import { ref } from 'vue'

const props = defineProps<{
  records: any
  vehicles: any[]
  filters: Record<string, string>
}>()

const showForm = ref(false)
const form = useForm({
  vehicle_id:     '',
  type:           'oil_change',
  title:          '',
  description:    '',
  cost:           '',
  scheduled_date: new Date().toISOString().slice(0, 10),
  performed_by:   '',
  garage_name:    '',
})

const submit = () => {
  form.post('/maintenance', {
    preserveScroll: true,
    onSuccess: () => { showForm.value = false; form.reset() },
  })
}

const completeForm = useForm({ completed_date: new Date().toISOString().slice(0, 10), cost: '', mileage: '' })
const completing = ref<string | null>(null)

const complete = (id: string) => {
  completeForm.patch(`/maintenance/${id}/complete`, { preserveScroll: true, onSuccess: () => { completing.value = null } })
}

const statusConfig: Record<string, string> = {
  scheduled:   'bg-yellow-100 text-yellow-700',
  in_progress: 'bg-blue-100 text-blue-700',
  completed:   'bg-green-100 text-green-700',
  cancelled:   'bg-gray-100 text-gray-600',
}

const typeLabels: Record<string, string> = {
  oil_change: 'Oil Change', tire_rotation: 'Tire Rotation',
  brake_service: 'Brake Service', inspection: 'Inspection',
  repair: 'Repair', other: 'Other',
}
</script>

<template>
  <!-- <AppLayout> -->
     <Heading
      variant="small"
      title="Maintenance"
      description="View and schedule maintenance for your vehicles"
    />

    <div class="p-4 lg:p-6 space-y-5">
      <div class="flex justify-end">
        <button
          class="flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 text-sm font-semibold"
          @click="showForm = !showForm"
        >
          <Plus class="h-4 w-4" /> Schedule Maintenance
        </button>
      </div>

      <!-- New maintenance form -->
      <div v-if="showForm" class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Schedule New Maintenance</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Vehicle *</label>
            <select v-model="form.vehicle_id" class="input-field">
              <option value="">Select vehicle</option>
              <option v-for="v in vehicles" :key="v.id" :value="v.id">{{ v.make }} {{ v.model }} — {{ v.plate_number }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Type *</label>
            <select v-model="form.type" class="input-field">
              <option v-for="(label, key) in typeLabels" :key="key" :value="key">{{ label }}</option>
            </select>
          </div>
          <div class="sm:col-span-2">
            <label class="block text-xs font-medium text-gray-500 mb-1">Title *</label>
            <input v-model="form.title" type="text" class="input-field" placeholder="e.g. 10,000 km Oil Change" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Scheduled Date *</label>
            <input v-model="form.scheduled_date" type="date" class="input-field" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Estimated Cost (MAD)</label>
            <input v-model="form.cost" type="number" min="0" class="input-field" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Garage Name</label>
            <input v-model="form.garage_name" type="text" class="input-field" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Performed By</label>
            <input v-model="form.performed_by" type="text" class="input-field" />
          </div>
        </div>
        <div class="mt-4 flex justify-end gap-3">
          <button class="px-4 py-2 rounded-xl text-sm text-gray-600 hover:bg-gray-100" @click="showForm = false">Cancel</button>
          <button class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-semibold" :disabled="form.processing" @click="submit">Schedule</button>
        </div>
      </div>

      <!-- Records table -->
      <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase">Vehicle</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Type</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Title</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Date</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Cost</th>
                <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3.5" />
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-for="rec in records.data" :key="rec.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                <td class="px-5 py-4">
                  <p class="font-medium text-gray-900 dark:text-white">{{ rec.vehicle?.make }} {{ rec.vehicle?.model }}</p>
                  <p class="text-xs text-gray-400">{{ rec.vehicle?.plate_number }}</p>
                </td>
                <td class="px-4 py-4 text-gray-600 dark:text-gray-400">{{ typeLabels[rec.type] }}</td>
                <td class="px-4 py-4 text-gray-700 dark:text-gray-300">{{ rec.title }}</td>
                <td class="px-4 py-4 text-xs text-gray-600 dark:text-gray-400">{{ rec.scheduled_date }}</td>
                <td class="px-4 py-4 text-gray-700 dark:text-gray-300">{{ rec.cost ? `${Number(rec.cost).toLocaleString()} MAD` : '—' }}</td>
                <td class="px-4 py-4">
                  <span :class="['text-xs font-medium px-2 py-0.5 rounded-full', statusConfig[rec.status]]">{{ rec.status }}</span>
                </td>
                <td class="px-4 py-4 text-right">
                  <button
                    v-if="rec.status === 'scheduled'"
                    class="text-xs font-medium text-green-600 hover:text-green-700"
                    @click="completing = rec.id"
                  >
                    Mark Complete
                  </button>
                  <!-- Inline complete dialog -->
                  <div v-if="completing === rec.id" class="mt-2 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-left space-y-2">
                    <input v-model="completeForm.completed_date" type="date" class="input-field text-xs" />
                    <input v-model="completeForm.cost" type="number" placeholder="Final cost (MAD)" class="input-field text-xs" />
                    <div class="flex gap-2">
                      <button class="text-xs px-3 py-1 rounded-lg bg-green-600 text-white" @click="complete(rec.id)">Save</button>
                      <button class="text-xs px-3 py-1 rounded-lg text-gray-500 hover:bg-gray-200" @click="completing = null">Cancel</button>
                    </div>
                  </div>
                </td>
              </tr>
              <tr v-if="!records.data?.length">
                <td colspan="7" class="px-5 py-10 text-center text-gray-400 text-sm">No maintenance records.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800">
          <p class="text-xs text-gray-500">{{ records.total }} records</p>
        </div>
      </div>
    </div>
  <!-- </AppLayout> -->
</template>

<style scoped>
.input-field { @apply w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500; }
</style>
