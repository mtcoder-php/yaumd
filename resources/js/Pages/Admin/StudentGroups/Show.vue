<template>
    <AppLayout :title="group.name">
        <div class="max-w-4xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('admin.student-groups.index')"
                        class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition"
                    >
                        <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            {{ group.name }}
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                  :class="group.is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'">
                                {{ group.is_active ? 'Faol' : 'Nofaol' }}
                            </span>
                        </h1>
                        <p class="text-sm text-gray-500 mt-0.5">{{ group.direction?.name_uz || '—' }}</p>
                    </div>
                </div>
                <Link :href="route('admin.student-groups.edit', group.id)" class="btn-secondary">
                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                    Tahrirlash
                </Link>
            </div>

            <!-- Ma'lumot kartasi -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 grid grid-cols-1 sm:grid-cols-3 gap-5"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">O'quv yili</p>
                    <p class="text-sm font-semibold text-gray-900">{{ group.academic_year?.name || '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Kafedra</p>
                    <p class="text-sm font-semibold text-gray-900">{{ group.department?.name_uz || '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Ta'lim darajasi</p>
                    <p class="text-sm font-semibold text-gray-900">{{ degreeLabel(group.degree) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Ta'lim shakli</p>
                    <p class="text-sm font-semibold text-gray-900">{{ studyFormLabel(group.study_form) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Kurs</p>
                    <p class="text-sm font-semibold text-gray-900">{{ group.course_year }}-kurs</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Rahbar o'qituvchi</p>
                    <p class="text-sm font-semibold text-gray-900">{{ group.head_teacher?.full_name || 'Tayinlanmagan' }}</p>
                </div>
                <div v-if="group.hemis_id">
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">HEMIS ID</p>
                    <p class="text-sm font-semibold text-gray-900">{{ group.hemis_id }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Talabalar soni</p>
                    <p class="text-sm font-semibold text-gray-900">{{ group.students_count }} ta</p>
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
                            class="btn-primary whitespace-nowrap">
                        <Icon icon="mdi:account-plus-outline" class="w-4 h-4" />
                        Qo'shish
                    </button>
                </div>
                <p class="hint mt-2">Faqat shu guruhning yo'nalishiga tegishli va hali boshqa a'zo bo'lmagan talabalar ko'rsatiladi</p>
            </div>

            <!-- A'zolar -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="px-5 py-3 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-700">Guruh a'zolari ({{ members.length }})</p>
                </div>
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Talaba</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Talaba raqami</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                    <tr v-if="!members.length">
                        <td colspan="4" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:account-group-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Bu guruhda hali talaba yo'q</p>
                        </td>
                    </tr>
                    <tr v-for="s in members" :key="s.id" class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <Link :href="route('admin.students.show', s.id)"
                                  class="flex items-center gap-3 hover:underline">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-white text-xs flex-shrink-0"
                                     style="background: linear-gradient(135deg, #0f3460, #533483)">
                                    {{ initials(s) }}
                                </div>
                                <p class="text-sm font-semibold text-gray-900">{{ fullName(s) }}</p>
                            </Link>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ s.student_number || '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="statusClass(s.status)">
                                {{ statusLabel(s.status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button @click="confirmRemove(s)"
                                    class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1 ml-auto">
                                <Icon icon="mdi:account-minus-outline" class="w-3.5 h-3.5" />
                                Guruhdan chiqarish
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
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
                    <button @click="removeTarget = null" class="btn-secondary flex-1">Bekor qilish</button>
                    <button @click="submitRemove" class="btn-danger flex-1">Chiqarish</button>
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
    active:         'bg-green-50 text-green-700',
    academic_leave: 'bg-amber-50 text-amber-700',
    expelled:       'bg-red-50 text-red-700',
    graduated:      'bg-blue-50 text-blue-700',
    transferred:    'bg-gray-100 text-gray-500',
}[v] || 'bg-gray-100 text-gray-500')

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
.field-input:focus { border-color: #0f3460; background: white; }
.hint { color: #9ca3af; font-size: 0.7rem; }

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
.btn-danger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    background: #ef4444;
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
}
.btn-danger:hover { background: #dc2626; }
</style>
