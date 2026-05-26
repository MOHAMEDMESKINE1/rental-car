<script setup lang="ts">
 import Heading from '@/components/Heading.vue';
 import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form } from '@inertiajs/vue3';
import { ref } from 'vue';
 import { store } from '@/routes/admin/customers';
import { LoaderCircle } from 'lucide-vue-next';

const addCustomer = ref(null);
</script>


<template>
    <Modal ref="addCustomer">

            <!-- <Heading :title="__('pageTitle.decisionFiscale.add')" :description="__('pageTitle.decisionFiscale.desc')" /> -->


        <Form class="grid gap-6 mt-4" #default="{ processing, errors}" :action="store().url" method="post" @success="addDecisionFiscalModal?.close()">
            {{ errors }}
            <!-- titre -->
            <div class="grid gap-2">
                <Label for="titre">titre</Label>
                <Input id="titre" type="text" autofocus name="titre" />
                <!-- <InputError :message="errors.titre" /> -->
            </div>
            <!-- date_debut -->
            <div class="flex flex-col gap-2">
                <Label for="date_debut"> date_debut</Label>
                 <!-- <InputError :message="errors.date_debut" /> -->
            </div>
            <!-- documents -->
            <Label for="documents"> docs</Label>
            <div class="bg-gray-100 p-4 border border-gray-300 rounded-md ">
                <!-- <MultiFile name="documents_decisions_fiscales" :errors="errors" /> -->
            </div>


            <Button type="submit" class="mt-4 w-full" :tabindex="8" :disabled="processing">
                <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                add customer
            </Button>
        </Form>
    </Modal>
</template>

<style scoped>

</style>
