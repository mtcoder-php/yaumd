<template>
    <AppLayout :title="`${course.title_uz} — O'quvchilar`">
        <div class="max-w-5xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="route('admin.courses.show', course.id)"
                      class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ course.title_uz }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">O'quvchilarni boshqarish — {{ enrollments.total }} ta yozilgan</p>
                </div>
                <button @click="enrollOpen = true" class="btn-brand ml-auto">
                    <Icon icon="mdi:account-plus-outline" class="w-4 h-4" />
                    Yozish
                </button>
            </div>

            <!-- Jadval -->
            <div class="table-grid-wrap">
                <table class="table-grid">
                    <thead>
                    <tr>
                        <th>Foydalanuvchi</th>
                        <th>Yozilgan sana</th>
                        <th class="text-center">Progress</th>
                        <th>Holati</th>
                        <th>To'lov</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!enrollments.data?.length">
                        <td colspan="6" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:account-school-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Hali hech kim yozilmagan</p>
                        </td>
                    </tr>
                    <tr v-for="e in enrollments.data ?? []" :key="e.id">
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-white text-xs flex-shrink-0 bg-brand-600">
                                    {{ initials(e.user?.full_name) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ e.user?.full_name || '—' }}</p>
                                    <p class="text-xs text-gray-400">{{ e.user?.email || '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-sm text-gray-600">{{ formatDate(e.enrolled_at) }}</td>
                        <td>
                            <div class="flex items-center gap-2 justify-center">
                                <div class="w-16 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-brand-600" :style="`width:${e.progress || 0}%`" />
                                </div>
                                <span class="text-xs font-semibold text-gray-600">{{ e.progress || 0 }}%</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge-pill" :class="statusClass(e.status)">
                                {{ statusLabel(e.status) }}
                            </span>
                        </td>
                        <td>
                            <select :value="e.payment_status" @change="updatePayment(e, $event.target.value)"
                                    class="text-xs font-semibold rounded-lg border px-2 py-1 outline-none"
                                    :class="paymentClass(e.payment_status)">
                                <option value="pending">Kutilmoqda</option>
                                <option value="paid">To'langan</option>
                                <option value="failed">Muvaffaqiyatsiz</option>
                                <option value="refunded">Qaytarilgan</option>
                            </select>
                        </td>
                        <td>
                            <div class="flex items-center justify-end">
                                <button @click="confirmRemove(e)" title="Chiqarish" class="btn-ghost-icon danger">
                                    <Icon icon="mdi:account-remove-outline" class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="(enrollments.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-xs text-gray-500">{{ enrollments.from }}–{{ enrollments.to }} / {{ enrollments.total }}</p>
                    <div class="flex items-center gap-1.5">
                        <template v-for="link in (enrollments.links ?? [])" :key="link.label">
                            <component
                                :is="link.url ? Link : 'span'"
                                :href="link.url ?? undefined"
                                class="pagination-btn"
                                :class="[link.active ? 'active' : '', !link.url ? 'disabled' : '']"
                            >
                                <ChevronLeftIcon v-if="isPrevLabel(link.label)" class="w-4 h-4" />
                                <ChevronRightIcon v-else-if="isNextLabel(link.label)" class="w-4 h-4" />
                                <span v-else v-html="link.label" />
                            </component>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Yozish modali -->
        <div v-if="enrollOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="closeEnroll">
            <div class="bg-white rounded-2xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-gray-900">Kursga yozish</h3>
                    <button @click="closeEnroll" class="text-gray-400 hover:text-gray-600">
                        <Icon icon="mdi:close" class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Rejim tanlash -->
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="enrollMode = 'student'"
                                class="px-3 py-2.5 rounded-xl border-2 text-sm font-medium transition-all"
                                :class="enrollMode === 'student' ? 'option-active text-brand-600' : 'option-idle text-gray-700'">
                            <Icon icon="mdi:account-outline" class="w-4 h-4 inline mr-1" />
                            Bitta talaba
                        </button>
                        <button type="button" @click="enrollMode = 'group'"
                                class="px-3 py-2.5 rounded-xl border-2 text-sm font-medium transition-all"
                                :class="enrollMode === 'group' ? 'option-active text-brand-600' : 'option-idle text-gray-700'">
                            <Icon icon="mdi:account-group-outline" class="w-4 h-4 inline mr-1" />
                            Butun guruh
                        </button>
                    </div>

                    <div v-if="enrollMode === 'student'">
                        <label class="field-label"><span class="req">*</span> Talaba</label>
                        <select v-model="form.student_id" class="field-input"
                                :class="form.errors.student_id ? 'field-error' : ''">
                            <option value="">Tanlang</option>
                            <option v-for="s in students" :key="s.id" :value="s.id">
                                {{ s.last_name }} {{ s.first_name }} ({{ s.student_number || '—' }})
                            </option>
                        </select>
                        <p v-if="form.errors.student_id" class="err">{{ form.errors.student_id }}</p>
                    </div>

                    <div v-else>
                        <label class="field-label"><span class="req">*</span> Guruh</label>
                        <select v-model="form.group_id" class="field-input"
                                :class="form.errors.group_id ? 'field-error' : ''">
                            <option value="">Tanlang</option>
                            <option v-for="g in course.groups ?? []" :key="g.id" :value="g.id">{{ g.name }}</option>
                        </select>
                        <p v-if="form.errors.group_id" class="err">{{ form.errors.group_id }}</p>
                        <p class="text-xs text-gray-400 mt-1">Guruhdagi tizimga kirish hisobi bor barcha talabalar yoziladi.</p>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button @click="closeEnroll" class="btn-neutral flex-1 justify-center">Bekor qilish</button>
                    <button @click="submitEnroll" :disabled="form.processing" class="btn-brand flex-1 justify-center">
                        <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                        {{ form.processing ? 'Yozilmoqda...' : 'Yozish' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Chiqarish modali -->
        <div v-if="removeTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="removeTarget = null">
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <Icon icon="mdi:account-remove-outline" class="w-6 h-6 text-red-500" />
                </div>
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Kursdan chiqarish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ removeTarget.user?.full_name }}</strong>ni ushbu kursdan chiqarasizmi?
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
import { Link, router, useForm } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
import { useToast } from 'vue-toastification'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    course:      { type: Object, required: true },
    enrollments: { type: Object, required: true },
    students:    { type: Array,  default: () => [] },
})

const toast = useToast()

const initials = (name) => (name || '?').trim().split(/\s+/).map(p => p[0]).join('').slice(0, 2).toUpperCase()
const formatDate = (v) => v ? new Date(v).toLocaleDateString('uz-UZ') : '—'

const statusLabel = (v) => ({ active: 'Faol', completed: 'Tugatgan', dropped: "Tark etgan", expired: 'Muddati o\'tgan' }[v] || v)
const statusClass = (v) => ({
    active:    'badge-brand',
    completed: 'badge-success',
    dropped:   'badge-neutral',
    expired:   'badge-warning',
}[v] || 'badge-neutral')

const paymentClass = (v) => ({
    pending:  'border-amber-200 bg-amber-50 text-amber-700',
    paid:     'border-green-200 bg-green-50 text-green-700',
    failed:   'border-red-200 bg-red-50 text-red-700',
    refunded: 'border-gray-200 bg-gray-100 text-gray-500',
}[v] || 'border-gray-200 bg-gray-50 text-gray-600')

const updatePayment = (enrollment, value) => {
    router.patch(route('admin.courses.enrollments.payment-status', [props.course.id, enrollment.id]), {
        payment_status: value,
    }, {
        preserveScroll: true,
        onSuccess: () => toast.success("To'lov holati yangilandi!"),
        onError: () => toast.error("Xatolik yuz berdi, qayta urinib ko'ring."),
    })
}

// Yozish modali
const enrollOpen = ref(false)
const enrollMode = ref('student')
const form = useForm({ student_id: '', group_id: '' })

const closeEnroll = () => {
    enrollOpen.value = false
    form.reset()
    form.clearErrors()
}

const submitEnroll = () => {
    if (enrollMode.value === 'student') {
        form.group_id = ''
    } else {
        form.student_id = ''
    }

    form.post(route('admin.courses.enrollments.store', props.course.id), {
        preserveScroll: true,
        onSuccess: (page) => {
            closeEnroll()
            const flash = page.props.flash
            if (flash?.success) toast.success(flash.success)
            if (flash?.error) toast.error(flash.error)
        },
        onError: () => toast.error("Formada xatolik bor, tekshiring."),
    })
}

// Chiqarish modali
const removeTarget = ref(null)
const confirmRemove = (enrollment) => { removeTarget.value = enrollment }
const submitRemove = () => {
    router.delete(route('admin.courses.enrollments.destroy', [props.course.id, removeTarget.value.id]), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success("Talaba kursdan chiqarildi!")
            removeTarget.value = null
        },
        onError: () => toast.error("Xatolik yuz berdi."),
    })
}

// Laravel'ning standart pagination yorliqlari o'rniga sof strelka
// ikonkalarini ko'rsatish uchun (boshqa sahifalardagi bilan bir xil naqsh).
const isPrevLabel = (label) => /Previous|&laquo;|«/i.test(label)
const isNextLabel = (label) => /Next|&raquo;|»/i.test(label)
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
    font-family: inherit;
}
.field-input:focus { border-color: var(--color-brand-600); background: white; }
.field-error { border-color: #f87171 !important; background: #fef2f2 !important; }
.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }

.option-idle { border-color: #e5e7eb; background: #fafafa; }
.option-active { border-color: var(--color-brand-600); background: var(--color-brand-50); }
</style>
