<template>
    <AppLayout :title="pageTitle">
        <div class="max-w-2xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.student-groups.index')"
                    class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition"
                >
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <h1 class="text-xl font-bold text-gray-900">
                    {{ pageTitle }}
                </h1>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <div class="space-y-5">

                    <!-- Nomi + HEMIS ID -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">Guruh nomi <span class="req">*</span></label>
                            <input v-model="form.name" type="text" placeholder="Masalan: MT-1-24"
                                   class="field-input" :class="form.errors.name ? 'field-error' : ''">
                            <p v-if="form.errors.name" class="err">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="field-label">HEMIS ID</label>
                            <input v-model="form.hemis_id" type="text" placeholder="Ixtiyoriy"
                                   class="field-input" :class="form.errors.hemis_id ? 'field-error' : ''">
                            <p v-if="form.errors.hemis_id" class="err">{{ form.errors.hemis_id }}</p>
                        </div>
                    </div>

                    <!-- O'quv yili -->
                    <div>
                        <label class="field-label">O'quv yili <span class="req">*</span></label>
                        <select v-model="form.academic_year_id" class="field-input"
                                :class="form.errors.academic_year_id ? 'field-error' : ''">
                            <option value="">Tanlang</option>
                            <option v-for="y in academicYears" :key="y.id" :value="y.id">
                                {{ y.name }}{{ y.is_active ? ' (joriy)' : '' }}
                            </option>
                        </select>
                        <p v-if="form.errors.academic_year_id" class="err">{{ form.errors.academic_year_id }}</p>
                    </div>

                    <!-- Kafedra + Yo'nalish -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">Kafedra</label>
                            <select v-model="form.department_id" class="field-input" @change="form.direction_id = ''">
                                <option value="">Tanlang</option>
                                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name_uz }}</option>
                            </select>
                            <p class="hint">Avval kafedrani tanlang — yo'nalishlar shunga qarab chiqadi</p>
                        </div>
                        <div>
                            <label class="field-label">Yo'nalish <span class="req">*</span></label>
                            <select v-model="form.direction_id" class="field-input"
                                    :class="form.errors.direction_id ? 'field-error' : ''"
                                    :disabled="!form.department_id"
                                    @change="onDirectionChange">
                                <option value="">{{ form.department_id ? 'Tanlang' : 'Avval kafedrani tanlang' }}</option>
                                <option v-for="d in filteredDirections" :key="d.id" :value="d.id">{{ d.name_uz }}</option>
                            </select>
                            <p v-if="form.department_id && !filteredDirections.length" class="hint text-amber-600">
                                Bu kafedraga biriktirilgan yo'nalish topilmadi
                            </p>
                            <p v-if="form.errors.direction_id" class="err">{{ form.errors.direction_id }}</p>
                        </div>
                    </div>

                    <!-- Daraja / Shakl / Kurs -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="field-label">Ta'lim darajasi <span class="req">*</span></label>
                            <select v-model="form.degree" class="field-input">
                                <option value="bachelor">Bakalavr</option>
                                <option value="master">Magistr</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Ta'lim shakli <span class="req">*</span></label>
                            <select v-model="form.study_form" class="field-input">
                                <option value="full_time">Kunduzgi</option>
                                <option value="evening">Kechki</option>
                                <option value="distance">Sirtqi</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Kurs <span class="req">*</span></label>
                            <select v-model.number="form.course_year" class="field-input">
                                <option v-for="c in courseYearOptions" :key="c" :value="c">{{ c }}-kurs</option>
                            </select>
                        </div>
                    </div>

                    <!-- Rahbar o'qituvchi -->
                    <div>
                        <label class="field-label">Rahbar (kurator) o'qituvchi</label>
                        <select v-model="form.head_teacher_id" class="field-input">
                            <option value="">Tayinlanmagan</option>
                            <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.full_name }}</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 border border-gray-100">
                        <div>
                            <p class="text-sm font-semibold text-gray-700">Guruh faol</p>
                            <p class="text-xs text-gray-400 mt-0.5">Nofaol guruhlar (bitirgan/tarqatilgan) tanlovlarda ko'rinmaydi</p>
                        </div>
                        <button type="button" @click="form.is_active = !form.is_active"
                                class="relative w-11 h-6 rounded-full transition-all duration-300"
                                :style="form.is_active ? 'background:linear-gradient(135deg,#0f3460,#533483)' : 'background:#e5e7eb'">
                            <span class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-all duration-300"
                                  :class="form.is_active ? 'left-6' : 'left-1'"></span>
                        </button>
                    </div>

                </div>

                <!-- Tugmalar -->
                <div class="flex gap-3 mt-6">
                    <Link :href="route('admin.student-groups.index')"
                          class="btn-secondary flex-1 flex items-center justify-center gap-2">
                        <Icon icon="mdi:close" class="w-4 h-4" />
                        Bekor qilish
                    </Link>
                    <button type="button" @click="submit" :disabled="form.processing" class="btn-primary flex-1">
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
    group:         { type: Object, default: null },
    academicYears: { type: Array, default: () => [] },
    directions:    { type: Array, default: () => [] },
    departments:   { type: Array, default: () => [] },
    teachers:      { type: Array, default: () => [] },
})

const isEdit = computed(() => !!props.group)
const pageTitle = computed(() => isEdit.value ? 'Guruhni tahrirlash' : 'Yangi guruh')

// Edit rejimida kafedra tanlanmagan bo'lsa ham (eski yozuvlarda), yo'nalishdan kelib chiqib
// kafedrani avtomatik aniqlaymiz — aks holda "avval kafedrani tanlang" holatida qulflanib qoladi.
const inferredDepartmentId = (() => {
    if (props.group?.department_id) return props.group.department_id
    if (props.group?.direction_id) {
        return props.directions.find(d => d.id === props.group.direction_id)?.department_id ?? ''
    }
    return ''
})()

const form = useForm({
    name:              props.group?.name              || '',
    hemis_id:          props.group?.hemis_id           || '',
    academic_year_id:  props.group?.academic_year_id   || '',
    department_id:     inferredDepartmentId,
    direction_id:      props.group?.direction_id       || '',
    degree:            props.group?.degree             || 'bachelor',
    study_form:        props.group?.study_form         || 'full_time',
    course_year:       props.group?.course_year        || 1,
    head_teacher_id:   props.group?.head_teacher_id     || '',
    is_active:         props.group?.is_active           ?? true,
})

const filteredDirections = computed(() => {
    if (!form.department_id) return []
    return props.directions.filter(d => d.department_id === form.department_id)
})

// Yo'nalish tanlanganda, o'sha yo'nalishning ta'lim darajasini avtomatik moslashtiramiz
const onDirectionChange = () => {
    const direction = props.directions.find(d => d.id === form.direction_id)
    if (direction?.degree) form.degree = direction.degree
}

// Daraja + ta'lim shakliga qarab ruxsat etilgan kurslar:
// Magistr -> 1-2, Bakalavr+Kunduzgi -> 1-4, Bakalavr+Kechki/Sirtqi -> 1-5
const courseYearOptions = computed(() => {
    if (form.degree === 'master') return [1, 2]
    if (form.study_form === 'full_time') return [1, 2, 3, 4]
    return [1, 2, 3, 4, 5]
})

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.student-groups.update', props.group.id))
    } else {
        form.post(route('admin.student-groups.store'))
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
.req { color: #ef4444; }
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
.field-input:focus { border-color: #0f3460; background: white; }
.field-input:disabled { opacity: 0.6; cursor: not-allowed; }
.field-error { border-color: #f87171 !important; background: #fef2f2 !important; }
.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }
.hint { color: #9ca3af; font-size: 0.7rem; margin-top: 0.25rem; display: block; }

.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.5rem;
    border-radius: 0.75rem;
    background: linear-gradient(135deg, #0f3460, #533483);
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-primary:hover { box-shadow: 0 6px 20px rgba(15,52,96,0.3); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    background: white;
    color: #374151;
    font-size: 0.875rem;
    font-weight: 600;
    border: 1.5px solid #e5e7eb;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}
.btn-secondary:hover { background: #f9fafb; }
</style>
