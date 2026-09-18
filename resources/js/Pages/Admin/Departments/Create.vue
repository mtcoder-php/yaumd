<template>
    <AppLayout :title="isEdit ? 'Kafedrani tahrirlash' : 'Yangi kafedra'">
        <div class="max-w-3xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="route('admin.departments.index')"
                      class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <h1 class="text-xl font-bold text-gray-900">
                    {{ isEdit ? 'Kafedrani tahrirlash' : 'Yangi kafedra' }}
                </h1>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-sm font-bold text-gray-800">Kafedra ma'lumotlari</h2>
                    <div class="flex items-center gap-2">
                        <Link :href="route('admin.departments.index')" class="btn-neutral">
                            <Icon icon="mdi:arrow-left" class="w-4 h-4" />
                            Orqaga
                        </Link>
                        <button type="button" @click="submit" :disabled="form.processing" class="btn-brand">
                            <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                            <Icon v-else icon="mdi:content-save-outline" class="w-4 h-4" />
                            {{ form.processing ? 'Saqlanmoqda...' : 'Saqlash' }}
                        </button>
                    </div>
                </div>

                <div class="space-y-5">

                    <!-- Fakultet -->
                    <div>
                        <label class="field-label"><span class="req">*</span> Fakultet</label>
                        <select v-model="form.faculty_id" class="field-input"
                                :class="form.errors.faculty_id ? 'field-error' : ''">
                            <option value="">Tanlang</option>
                            <option v-for="f in faculties" :key="f.id" :value="f.id">{{ f.name_uz }}</option>
                        </select>
                        <p v-if="form.errors.faculty_id" class="err">{{ form.errors.faculty_id }}</p>
                    </div>

                    <!-- Nomlar -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="field-label"><span class="req">*</span> Nomi (O'zbek)</label>
                            <input v-model="form.name_uz" type="text" placeholder="Kafedra nomi"
                                   class="field-input" :class="form.errors.name_uz ? 'field-error' : ''">
                            <p v-if="form.errors.name_uz" class="err">{{ form.errors.name_uz }}</p>
                        </div>
                        <div>
                            <label class="field-label"><span class="req">*</span> Nomi (Rus)</label>
                            <input v-model="form.name_ru" type="text" placeholder="Название кафедры"
                                   class="field-input" :class="form.errors.name_ru ? 'field-error' : ''">
                            <p v-if="form.errors.name_ru" class="err">{{ form.errors.name_ru }}</p>
                        </div>
                        <div>
                            <label class="field-label">Nomi (Ingliz)</label>
                            <input v-model="form.name_en" type="text" placeholder="Department name"
                                   class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Qisqa nomi</label>
                            <input v-model="form.short_name" type="text" placeholder="Masalan: MPK"
                                   class="field-input">
                        </div>
                    </div>

                    <!-- Kafedra mudiri -->
                    <div>
                        <label class="field-label">Kafedra mudiri</label>
                        <select v-model="form.head_id" class="field-input">
                            <option value="">Tanlang</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.full_name }}</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 border border-gray-100">
                        <div>
                            <p class="text-sm font-semibold text-gray-700">Faol holat</p>
                            <p class="text-xs text-gray-400 mt-0.5">Kafedra ro'yxatda ko'rinadi</p>
                        </div>
                        <button type="button" @click="form.is_active = !form.is_active"
                                class="relative w-11 h-6 rounded-full transition-all duration-300"
                                :class="form.is_active ? 'bg-brand-600' : 'bg-gray-200'">
                            <span class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-all duration-300"
                                  :class="form.is_active ? 'left-6' : 'left-1'"></span>
                        </button>
                    </div>

                </div>

                <!-- Tugmalar (pastda ham) -->
                <div class="flex gap-3 mt-6">
                    <Link :href="route('admin.departments.index')" class="btn-neutral flex-1 justify-center">
                        <Icon icon="mdi:close" class="w-4 h-4" />
                        Bekor qilish
                    </Link>
                    <button type="button" @click="submit" :disabled="form.processing" class="btn-brand flex-1 justify-center">
                        <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                        <Icon v-else icon="mdi:content-save-outline" class="w-4 h-4" />
                        {{ form.processing ? 'Saqlanmoqda...' : 'Saqlash' }}
                    </button>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    department: { type: Object, default: null },
    faculties:  { type: Array,  default: () => [] },
    users:      { type: Array,  default: () => [] },
})

const isEdit = computed(() => !!props.department)

const form = useForm({
    faculty_id: props.department?.faculty_id || '',
    name_uz:    props.department?.name_uz    || '',
    name_ru:    props.department?.name_ru    || '',
    name_en:    props.department?.name_en    || '',
    short_name: props.department?.short_name || '',
    head_id:    props.department?.head_id    || '',
    is_active:  props.department?.is_active  ?? true,
})

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.departments.update', props.department.id))
    } else {
        form.post(route('admin.departments.store'))
    }
}
</script>

<style scoped>
.field-label {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.375rem;
}
.req { color: #ef4444; margin-right: 0.15rem; }
.field-input {
    width: 100%;
    padding: 0.6rem 0.875rem;
    border-radius: 0.625rem;
    border: 1.5px solid #e5e7eb;
    font-size: 0.875rem;
    color: #111827;
    background: #fafafa;
    outline: none;
    transition: border-color 0.2s;
    appearance: auto;
}
.field-input:focus { border-color: var(--color-brand-600); background: white; }
.field-error { border-color: #f87171 !important; background: #fef2f2 !important; }
.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }
</style>
