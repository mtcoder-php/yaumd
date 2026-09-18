<template>
    <AppLayout :title="isEdit ? 'Yo\'nalishni tahrirlash' : 'Yangi yo\'nalish'">
        <div class="max-w-3xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.directions.index')"
                    class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition"
                >
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <h1 class="text-xl font-bold text-gray-900">
                    {{ isEdit ? "Yo'nalishni tahrirlash" : "Yangi yo'nalish" }}
                </h1>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-sm font-bold text-gray-800">Yo'nalish ma'lumotlari</h2>
                    <div class="flex items-center gap-2">
                        <Link :href="route('admin.directions.index')" class="btn-neutral">
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

                    <!-- Kafedra -->
                    <div>
                        <label class="field-label">Kafedra</label>
                        <select v-model="form.department_id" class="field-input">
                            <option value="">Tanlang</option>
                            <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name_uz }}</option>
                        </select>
                    </div>

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
                            <input v-model="form.name_uz" type="text" placeholder="Yo'nalish nomi"
                                   class="field-input" :class="form.errors.name_uz ? 'field-error' : ''">
                            <p v-if="form.errors.name_uz" class="err">{{ form.errors.name_uz }}</p>
                        </div>
                        <div>
                            <label class="field-label"><span class="req">*</span> Nomi (Rus)</label>
                            <input v-model="form.name_ru" type="text" placeholder="Название направления"
                                   class="field-input" :class="form.errors.name_ru ? 'field-error' : ''">
                            <p v-if="form.errors.name_ru" class="err">{{ form.errors.name_ru }}</p>
                        </div>
                        <div>
                            <label class="field-label">Nomi (Ingliz)</label>
                            <input v-model="form.name_en" type="text" placeholder="Direction name"
                                   class="field-input">
                        </div>
                        <div>
                            <label class="field-label">HEMIS kodi</label>
                            <input v-model="form.hemis_code" type="text" placeholder="5330200"
                                   class="field-input">
                        </div>
                    </div>

                    <!-- Daraja va muddati -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="field-label"><span class="req">*</span> Daraja</label>
                            <div class="flex gap-2">
                                <button v-for="d in degrees" :key="d.value" type="button"
                                        @click="form.degree = d.value"
                                        class="flex-1 py-2.5 rounded-xl border-2 text-sm font-semibold transition-all"
                                        :class="form.degree === d.value
                                            ? 'border-brand-600 bg-brand-50 text-brand-600'
                                            : 'border-gray-200 bg-gray-50 text-gray-500'">
                                    {{ d.label }}
                                </button>
                            </div>
                            <p v-if="form.errors.degree" class="err">{{ form.errors.degree }}</p>
                        </div>
                        <div>
                            <label class="field-label"><span class="req">*</span> O'qish muddati (yil)</label>
                            <input v-model="form.duration_years" type="number" min="1" max="6"
                                   class="field-input" :class="form.errors.duration_years ? 'field-error' : ''">
                            <p v-if="form.errors.duration_years" class="err">{{ form.errors.duration_years }}</p>
                        </div>
                    </div>

                    <!-- Kvota -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">Grant kvotasi</label>
                            <input v-model="form.quota_grant" type="number" min="0" placeholder="0"
                                   class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Kontrakt kvotasi</label>
                            <input v-model="form.quota_contract" type="number" min="0" placeholder="0"
                                   class="field-input">
                        </div>
                    </div>

                    <!-- Kontrakt narxi -->
                    <div>
                        <label class="field-label">Yillik to'lov (so'm)</label>
                        <div class="relative">
                            <input v-model="form.annual_fee" type="number" min="0" placeholder="0"
                                   class="field-input pr-16">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">so'm</span>
                        </div>
                        <p v-if="form.annual_fee" class="text-xs text-gray-400 mt-1">
                            {{ formatAmount(form.annual_fee) }}
                        </p>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 border border-gray-100">
                        <div>
                            <p class="text-sm font-semibold text-gray-700">Faol holat</p>
                            <p class="text-xs text-gray-400 mt-0.5">Yo'nalish ro'yxatda va arizada ko'rinadi</p>
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
                    <Link :href="route('admin.directions.index')" class="btn-neutral flex-1 justify-center">
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
    direction:   { type: Object, default: null },
    faculties:   { type: Array,  default: () => [] },
    departments: { type: Array,  default: () => [] },
})

const isEdit = computed(() => !!props.direction)

const form = useForm({
    faculty_id:     props.direction?.faculty_id     || '',
    department_id:  props.direction?.department_id  || '',
    hemis_code:     props.direction?.hemis_code     || '',
    name_uz:        props.direction?.name_uz        || '',
    name_ru:        props.direction?.name_ru        || '',
    name_en:        props.direction?.name_en        || '',
    degree:         props.direction?.degree         || 'bachelor',
    duration_years: props.direction?.duration_years || 4,
    quota_grant:    props.direction?.quota_grant    || 0,
    quota_contract: props.direction?.quota_contract || 0,
    annual_fee:     props.direction?.annual_fee     || 0,
    is_active:      props.direction?.is_active      ?? true,
})

const degrees = [
    { value: 'bachelor', label: 'Bakalavr' },
    { value: 'master',   label: 'Magistr' },
]

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.directions.update', props.direction.id))
    } else {
        form.post(route('admin.directions.store'))
    }
}

const formatAmount = (amount) => {
    if (!amount) return ''
    return new Intl.NumberFormat('uz-UZ').format(amount) + ' so\'m'
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
