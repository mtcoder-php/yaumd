<template>
    <AppLayout :title="fullName">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center gap-4 flex-wrap">
                <Link :href="route('admin.students.index')"
                      class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <div class="w-11 h-11 rounded-xl flex items-center justify-center font-bold text-white text-sm flex-shrink-0 bg-brand-600">
                    {{ initials }}
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ fullName }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5 font-mono">{{ student.student_number || student.hemis_id || '—' }}</p>
                </div>
                <div class="ml-auto flex items-center gap-3">
                    <Link :href="route('admin.students.edit', student.id)" class="btn-brand">
                        <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                        Tahrirlash
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                <!-- Chap ustun -->
                <div class="lg:col-span-2 space-y-5">

                    <!-- Akademik ma'lumotlar -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">
                            <Icon icon="mdi:school-outline" class="w-4 h-4 text-brand-600" />
                            Akademik ma'lumotlar
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="info-label">O'quv yili</p>
                                <p class="info-value font-semibold">{{ student.academic_year?.name || '—' }}</p>
                            </div>
                            <div>
                                <p class="info-label">Kurs</p>
                                <p class="info-value">{{ student.course_year }}-kurs</p>
                            </div>
                            <div class="col-span-2">
                                <p class="info-label">Yo'nalish</p>
                                <p class="info-value font-semibold">{{ student.direction?.name_uz || '—' }}</p>
                                <p class="text-xs text-gray-400">{{ student.direction?.faculty?.name_uz || '' }}</p>
                            </div>
                            <div>
                                <p class="info-label">Kafedra</p>
                                <p class="info-value">{{ student.department?.name_uz || '—' }}</p>
                            </div>
                            <div>
                                <p class="info-label">Ta'lim darajasi</p>
                                <p class="info-value">{{ degreeLabel(student.degree) }}</p>
                            </div>
                            <div>
                                <p class="info-label">Ta'lim shakli</p>
                                <p class="info-value">{{ studyFormLabel(student.study_form) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Shaxsiy ma'lumotlar -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">
                            <Icon icon="mdi:account-outline" class="w-4 h-4 text-brand-600" />
                            Shaxsiy ma'lumotlar
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="info-label">Familiya</p>
                                <p class="info-value">{{ student.last_name }}</p>
                            </div>
                            <div>
                                <p class="info-label">Ism</p>
                                <p class="info-value">{{ student.first_name }}</p>
                            </div>
                            <div>
                                <p class="info-label">Sharifi</p>
                                <p class="info-value">{{ student.middle_name || '—' }}</p>
                            </div>
                            <div>
                                <p class="info-label">Jinsi</p>
                                <p class="info-value">{{ student.gender === 'male' ? 'Erkak' : (student.gender === 'female' ? 'Ayol' : '—') }}</p>
                            </div>
                            <div>
                                <p class="info-label">Tug'ilgan sana</p>
                                <p class="info-value">{{ birthDate }}</p>
                            </div>
                            <div>
                                <p class="info-label">Passport seriya va raqami</p>
                                <p class="info-value font-mono">{{ student.passport_series || '—' }}</p>
                            </div>
                            <div>
                                <p class="info-label">JSHSHIR</p>
                                <p class="info-value font-mono">{{ student.jshshir || '—' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Aloqa -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">
                            <Icon icon="mdi:card-account-phone-outline" class="w-4 h-4 text-brand-600" />
                            Aloqa ma'lumotlari
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="info-label">Telefon</p>
                                <a v-if="student.phone" :href="`tel:${student.phone}`" class="info-value text-brand-600 hover:underline">
                                    {{ student.phone }}
                                </a>
                                <p v-else class="info-value">—</p>
                            </div>
                            <div>
                                <p class="info-label">Email</p>
                                <p class="info-value">{{ student.email || '—' }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="info-label">Manzil</p>
                                <p class="info-value">{{ student.address || '—' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Guruhi -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">
                            <Icon icon="mdi:account-group-outline" class="w-4 h-4 text-brand-600" />
                            Guruhi
                        </h2>
                        <div v-if="student.groups?.length" class="space-y-2">
                            <div v-for="g in student.groups" :key="g.id"
                                 class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">{{ g.name }}</p>
                                    <p class="text-xs text-gray-400">{{ studyFormLabel(g.study_form) }} · {{ g.course_year }}-kurs</p>
                                </div>
                                <span class="badge-pill" :class="g.is_active ? 'badge-success' : 'badge-neutral'">
                                    {{ g.is_active ? 'Faol' : 'Nofaol' }}
                                </span>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-400">
                            <Icon icon="mdi:account-group-outline" class="w-8 h-8 mx-auto mb-2 opacity-40" />
                            <p class="text-sm">Talaba hali birorta guruhga biriktirilmagan</p>
                        </div>
                    </div>

                    <!-- Kurslari (LMS) -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">
                            <Icon icon="mdi:book-open-outline" class="w-4 h-4 text-brand-600" />
                            Kurslari (LMS)
                        </h2>
                        <div v-if="student.enrollments?.length" class="space-y-2">
                            <div v-for="e in student.enrollments" :key="e.id"
                                 class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">{{ e.course?.title || '—' }}</p>
                                    <p class="text-xs text-gray-400">Progress: {{ e.progress ?? 0 }}%</p>
                                </div>
                                <span class="badge-pill badge-brand">{{ e.status }}</span>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-400">
                            <Icon icon="mdi:book-open-outline" class="w-8 h-8 mx-auto mb-2 opacity-40" />
                            <p class="text-sm">Talaba hali birorta kursga yozilmagan</p>
                            <p class="text-xs text-gray-300 mt-1">LMS moduli tayyor bo'lgach shu yerda ko'rinadi</p>
                        </div>
                    </div>

                </div>

                <!-- O'ng ustun -->
                <div class="space-y-5">

                    <!-- Holati -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">Holati</h2>
                        <span class="badge-pill" :class="statusClass(student.status)">
                            {{ statusLabel(student.status) }}
                        </span>
                    </div>

                    <!-- Ro'yxat ma'lumotlari -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">Ro'yxat ma'lumotlari</h2>
                        <div class="space-y-3">
                            <div>
                                <p class="info-label">HEMIS ID</p>
                                <p class="text-sm font-mono font-bold text-brand-600">{{ student.hemis_id || '—' }}</p>
                            </div>
                            <div>
                                <p class="info-label">Talaba raqami</p>
                                <p class="text-sm font-mono font-bold text-brand-600">{{ student.student_number || '—' }}</p>
                            </div>
                            <div>
                                <p class="info-label">Tizimga qo'shilgan sana</p>
                                <p class="info-value">{{ formatDate(student.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tezkor amallar -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">Tezkor amallar</h2>
                        <div class="flex flex-col gap-2">
                            <Link :href="route('admin.students.edit', student.id)"
                                  class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium border border-gray-200 hover:bg-brand-50 hover:text-brand-600 hover:border-brand-200 transition">
                                <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                                Tahrirlash
                            </Link>
                            <button @click="confirmDelete = true"
                                    class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-red-500 border border-red-100 hover:bg-red-50 transition">
                                <Icon icon="mdi:delete-outline" class="w-4 h-4" />
                                O'chirish
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Delete modal -->
        <div v-if="confirmDelete" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="confirmDelete = false">
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <Icon icon="mdi:delete-outline" class="w-6 h-6 text-red-500" />
                </div>
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Talabani o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ fullName }}</strong>ni o'chirasizmi?
                </p>
                <div class="flex gap-3">
                    <button @click="confirmDelete = false" class="btn-neutral flex-1 justify-center">Bekor qilish</button>
                    <button @click="submitDelete" class="btn-danger-pill flex-1">O'chirish</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    student: { type: Object, required: true },
})

const fullName = computed(() => [props.student.last_name, props.student.first_name, props.student.middle_name].filter(Boolean).join(' '))
const initials = computed(() => [props.student.last_name, props.student.first_name].filter(Boolean).map(n => n[0]).join('').toUpperCase())

const birthDate = computed(() => {
    const { birth_day, birth_month, birth_year } = props.student
    if (!birth_day || !birth_month || !birth_year) return '—'
    return `${String(birth_day).padStart(2, '0')}.${String(birth_month).padStart(2, '0')}.${birth_year}`
})

const degreeLabel = (v) => ({ bachelor: 'Bakalavr', master: 'Magistr' }[v] || v)
const studyFormLabel = (v) => ({ full_time: 'Kunduzgi', evening: 'Kechki', distance: 'Sirtqi' }[v] || v)

const statusOptions = {
    active:         { label: "O'qimoqda", cls: 'badge-success' },
    academic_leave: { label: "Akademik ta'til", cls: 'badge-warning' },
    expelled:       { label: 'Chetlashtirilgan', cls: 'badge-danger' },
    graduated:      { label: 'Bitirgan', cls: 'badge-brand' },
    transferred:    { label: "Ko'chirilgan", cls: 'badge-neutral' },
}
const statusLabel = (v) => statusOptions[v]?.label || v
const statusClass = (v) => statusOptions[v]?.cls || 'badge-neutral'

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const confirmDelete = ref(false)
const submitDelete = () => {
    router.delete(route('admin.students.destroy', props.student.id), {
        onSuccess: () => { confirmDelete.value = false },
    })
}
</script>

<style scoped>
.section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 1rem;
}
.info-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.25rem;
}
.info-value {
    font-size: 0.875rem;
    color: #111827;
}
</style>
