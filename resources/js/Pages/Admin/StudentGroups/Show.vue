<template>
    <AppLayout :title="group.name">
        <div class="max-w-4xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('admin.student-groups.index')"
                        class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition flex-shrink-0"
                    >
                        <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-xl font-bold text-gray-900">{{ group.name }}</h1>
                            <span class="badge-pill" :class="group.is_active ? 'badge-success' : 'badge-neutral'">
                                {{ group.is_active ? 'Faol' : 'Nofaol' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-0.5">{{ group.direction?.name_uz || '—' }}</p>
                    </div>
                </div>
                <Link :href="route('admin.student-groups.edit', group.id)" class="btn-brand">
                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                    Tahrirlash
                </Link>
            </div>

            <!-- Ma'lumot kartasi -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <h2 class="text-sm font-bold text-gray-800 mb-4">Guruh ma'lumotlari</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">O'quv yili</p>
                        <p class="text-sm font-semibold text-gray-900">{{ group.academic_year?.name || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Kafedra</p>
                        <p class="text-sm font-semibold text-gray-900">{{ group.department?.name_uz || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Ta'lim darajasi</p>
                        <p class="text-sm font-semibold text-gray-900">{{ degreeLabel(group.degree) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Ta'lim shakli</p>
                        <p class="text-sm font-semibold text-gray-900">{{ studyFormLabel(group.study_form) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Kurs</p>
                        <p class="text-sm font-semibold text-gray-900">{{ group.course_year }}-kurs</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Tutor (guruh rahbari)</p>
                        <p class="text-sm font-semibold text-gray-900">{{ group.tutor?.full_name || 'Tayinlanmagan' }}</p>
                    </div>
                    <div v-if="group.hemis_id">
                        <p class="text-xs text-gray-400 mb-1">HEMIS ID</p>
                        <p class="text-sm font-semibold text-gray-900">{{ group.hemis_id }}</p>
                    </div>
                </div>
            </div>

            <!-- Kichik statistika -->
            <div class="grid grid-cols-1 max-w-xs">
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1 bg-brand-600" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Talabalar</p>
                        <p class="text-2xl font-bold text-gray-900">{{ group.students_count ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Talaba qo'shish -->
            <div class="bg-white rounded-2xl border border-gray-100 p-5"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <p class="text-sm font-semibold text-gray-700 mb-3">Guruhga talaba qo'shish</p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <select v-model="addForm.student_id" class="field-input flex-1">
                        <option value="">{{ availableStudents.length ? 'Talabani tanlang' : "Qo'shsa bo'ladigan talaba topilmadi" }}</option>
                        <option v-for="s in availableStudents" :key="s.id" :value="s.id">
                            {{ fullName(s) }}{{ s.student_number ? ' — ' + s.student_number : '' }}
                        </option>
                    </select>
                    <button type="button" @click="submitAdd" :disabled="!addForm.student_id || addForm.processing"
                            class="btn-brand whitespace-nowrap justify-center">
                        <Icon icon="mdi:account-plus-outline" class="w-4 h-4" />
                        Qo'shish
                    </button>
                </div>
                <p class="hint mt-2">Faqat shu guruhning yo'nalishiga tegishli va hali boshqa a'zo bo'lmagan talabalar ko'rsatiladi</p>
            </div>

            <!-- A'zolar -->
            <div>
                <h2 class="text-sm font-bold text-gray-800 mb-3">Guruh a'zolari ({{ members.length }})</h2>
                <div class="table-grid-wrap">
                    <table class="table-grid">
                        <thead>
                        <tr>
                            <th>Talaba</th>
                            <th>Talaba raqami</th>
                            <th>Status</th>
                            <th class="text-right">Amallar</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-if="!members.length">
                            <td colspan="4" class="text-center py-16 text-gray-400">
                                <Icon icon="mdi:account-group-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                                <p class="text-sm">Bu guruhda hali talaba yo'q</p>
                            </td>
                        </tr>
                        <tr v-for="s in members" :key="s.id">
                            <td>
                                <Link :href="route('admin.students.show', s.id)" class="flex items-center gap-3 group">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-white text-xs flex-shrink-0 bg-brand-600">
                                        {{ initials(s) }}
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900 group-hover:text-brand-600 transition">{{ fullName(s) }}</p>
                                </Link>
                            </td>
                            <td class="text-sm text-gray-600">{{ s.student_number || '—' }}</td>
                            <td>
                                <span class="badge-pill" :class="statusClass(s.status)">
                                    {{ statusLabel(s.status) }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center justify-end">
                                    <button @click="confirmRemove(s)" title="Guruhdan chiqarish" class="btn-ghost-icon danger">
                                        <Icon icon="mdi:account-minus-outline" class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Remove modal -->
        <div v-if="removeTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="removeTarget = null">
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <Icon icon="mdi:account-minus-outline" class="w-6 h-6 text-red-500" />
                </div>
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Talabani guruhdan chiqarish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ fullName(removeTarget || {}) }}</strong> ni <strong>{{ group.name }}</strong> guruhidan chiqarasizmi?
                    Talabaning o'zi o'chirilmaydi.
                </p>
                <div class="flex gap-3">
                    <button @click="removeTarget = null" class="btn-neutral flex-1 justify-center">Bekor qilish</button>
                    <button @click="submitRemove" class="btn-danger-pill flex-1">Chiqarish</button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    group:             { type: Object, required: true },
    members:           { type: Array, default: () => [] },
    availableStudents: { type: Array, default: () => [] },
})

const degreeLabel = (v) => ({ bachelor: 'Bakalavr', master: 'Magistr' }[v] || v)
const studyFormLabel = (v) => ({ full_time: 'Kunduzgi', evening: 'Kechki', distance: 'Sirtqi' }[v] || v)

const statusOptions = {
    active:         "O'qimoqda",
    academic_leave: "Akademik ta'til",
    expelled:       'Chetlashtirilgan',
    graduated:      'Bitirgan',
    transferred:    "Ko'chirilgan",
}
const statusLabel = (v) => statusOptions[v] || v
const statusClass = (v) => ({
    active:         'badge-success',
    academic_leave: 'badge-warning',
    expelled:       'badge-danger',
    graduated:      'badge-brand',
    transferred:    'badge-neutral',
}[v] || 'badge-neutral')

const fullName = (s) => [s.last_name, s.first_name, s.middle_name].filter(Boolean).join(' ')
const initials = (s) => [s.last_name, s.first_name].filter(Boolean).map(n => n[0]).join('').toUpperCase()

const addForm = useForm({ student_id: '' })
const submitAdd = () => {
    addForm.post(route('admin.student-groups.students.add', props.group.id), {
        preserveScroll: true,
        onSuccess: () => { addForm.reset() },
    })
}

const removeTarget = ref(null)
const confirmRemove = (s) => { removeTarget.value = s }
const submitRemove = () => {
    router.delete(route('admin.student-groups.students.remove', [props.group.id, removeTarget.value.id]), {
        preserveScroll: true,
        onSuccess: () => { removeTarget.value = null },
    })
}
</script>

<style scoped>
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
.hint { color: #9ca3af; font-size: 0.7rem; }
</style>
