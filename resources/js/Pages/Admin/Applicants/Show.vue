<template>
    <AppLayout>
        <div class="p-6">

            <!-- Header -->
            <div class="flex items-center gap-4 mb-6">
                <Link
                    :href="route('admin.applicants.index')"
                    class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition"
                >
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">
                        {{ applicant.last_name }} {{ applicant.first_name }} {{ applicant.middle_name }}
                    </h1>
                    <p class="text-sm text-gray-500 font-mono">{{ applicant.application_number }}</p>
                </div>
                <div class="ml-auto flex items-center gap-3">
                    <!-- Tahrirlash -->
                    <Link
                        :href="route('admin.applicants.edit', applicant.id)"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition hover:shadow-lg"
                        style="background: linear-gradient(135deg, #0f3460, #533483)"
                    >
                        <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                        Tahrirlash
                    </Link>

                    <!-- Status o'zgartirish -->
                    <select
                        v-model="selectedStatus"
                        class="text-sm px-3 py-2 rounded-xl border font-medium"
                        :class="statusBadge(selectedStatus)"
                        @change="updateStatus"
                    >
                        <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                <!-- Chap ustun: asosiy ma'lumotlar -->
                <div class="lg:col-span-2 space-y-5">

                    <!-- Ta'lim ma'lumotlari -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5">
                        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <Icon icon="mdi:school-outline" class="w-4 h-4 text-[#0f3460]" />
                            Ta'lim ma'lumotlari
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="info-label">Ta'lim turi</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="educationTypeBadge(applicant.education_type)">
                                    {{ educationTypeLabel(applicant.education_type) }}
                                </span>
                            </div>
                            <div>
                                <p class="info-label">Ta'lim shakli</p>
                                <p class="info-value">{{ studyFormLabel(applicant.study_form) }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="info-label">Yo'nalish</p>
                                <p class="info-value font-semibold">{{ applicant.direction?.name_uz || '—' }}</p>
                                <p class="text-xs text-gray-400">{{ applicant.direction?.faculty?.name_uz || '' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Shaxsiy ma'lumotlar -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5">
                        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <Icon icon="mdi:account-outline" class="w-4 h-4 text-[#0f3460]" />
                            Shaxsiy ma'lumotlar
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="info-label">Familiya</p>
                                <p class="info-value">{{ applicant.first_name }}</p>
                            </div>
                            <div>
                                <p class="info-label">Ism</p>
                                <p class="info-value">{{ applicant.last_name }}</p>
                            </div>
                            <div>
                                <p class="info-label">Otasining ismi</p>
                                <p class="info-value">{{ applicant.middle_name }}</p>
                            </div>
                            <div>
                                <p class="info-label">Jins</p>
                                <p class="info-value">{{ applicant.gender === 'male' ? 'Erkak' : 'Ayol' }}</p>
                            </div>
                            <div>
                                <p class="info-label">Tug'ilgan sana</p>
                                <p class="info-value">{{ applicant.birth_day }}/{{ applicant.birth_month }}/{{ applicant.birth_year }}</p>
                            </div>
                            <div>
                                <p class="info-label">Millati</p>
                                <p class="info-value">{{ applicant.nationality || '—' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Hujjat va aloqa -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5">
                        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <Icon icon="mdi:card-account-details-outline" class="w-4 h-4 text-[#0f3460]" />
                            Hujjat va aloqa
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="info-label">Pasport seriyasi</p>
                                <p class="info-value font-mono font-semibold">{{ applicant.passport_series }}</p>
                            </div>
                            <div>
                                <p class="info-label">JShShIR</p>
                                <p class="info-value font-mono">{{ applicant.jshshir || '—' }}</p>
                            </div>
                            <div>
                                <p class="info-label">Telefon</p>
                                <a :href="`tel:${applicant.phone}`" class="info-value text-[#0f3460] hover:underline">
                                    {{ applicant.phone }}
                                </a>
                            </div>
                            <div>
                                <p class="info-label">Qo'shimcha raqam</p>
                                <a v-if="applicant.extra_phone" :href="`tel:${applicant.extra_phone}`"
                                   class="info-value text-[#0f3460] hover:underline">
                                    {{ applicant.extra_phone }}
                                </a>
                                <p v-else class="info-value">—</p>
                            </div>
                            <div class="col-span-2">
                                <p class="info-label">Manzil</p>
                                <p class="info-value">
                                    {{ applicant.region?.name_uz || '' }}
                                    {{ applicant.district?.name_uz ? ', ' + applicant.district.name_uz : '' }}
                                    {{ applicant.address ? ', ' + applicant.address : '' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Magistr fayllari -->
                    <div v-if="applicant.education_type === 'master'" class="bg-white rounded-2xl border border-gray-100 p-5">
                        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <Icon icon="mdi:file-document-outline" class="w-4 h-4 text-[#0f3460]" />
                            Yuklangan hujjatlar
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <a
                                v-for="file in masterFiles"
                                :key="file.key"
                                :href="applicant[file.key] ? `/storage/${applicant[file.key]}` : '#'"
                                :target="applicant[file.key] ? '_blank' : ''"
                                class="flex flex-col items-center gap-2 p-4 rounded-xl border transition-all"
                                :class="applicant[file.key]
                                    ? 'border-blue-100 bg-blue-50 hover:bg-blue-100'
                                    : 'border-gray-100 bg-gray-50 opacity-50 cursor-not-allowed'"
                            >
                                <Icon
                                    :icon="applicant[file.key] ? 'mdi:file-check-outline' : 'mdi:file-off-outline'"
                                    class="w-8 h-8"
                                    :class="applicant[file.key] ? 'text-blue-600' : 'text-gray-400'"
                                />
                                <span class="text-xs font-medium text-center"
                                      :class="applicant[file.key] ? 'text-blue-700' : 'text-gray-400'">
                                    {{ file.label }}
                                </span>
                            </a>
                        </div>
                    </div>

                </div>

                <!-- O'ng ustun: status va qo'shimcha -->
                <div class="space-y-5">

                    <!-- Status kartasi -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5">
                        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">Status</h2>
                        <div class="flex flex-col gap-2">
                            <button
                                v-for="s in statuses"
                                :key="s.value"
                                @click="changeStatus(s.value)"
                                class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium transition-all text-left"
                                :class="selectedStatus === s.value
                                    ? 'ring-2 ring-offset-1 ring-[#0f3460] ' + s.class
                                    : s.class + ' opacity-60 hover:opacity-100'"
                            >
                                <Icon :icon="s.icon" class="w-4 h-4 flex-shrink-0" />
                                {{ s.label }}
                                <Icon v-if="selectedStatus === s.value" icon="mdi:check" class="w-4 h-4 ml-auto" />
                            </button>
                        </div>
                    </div>

                    <!-- Ariza ma'lumotlari -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5">
                        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">Ariza</h2>
                        <div class="space-y-3">
                            <div>
                                <p class="info-label">Ariza raqami</p>
                                <p class="text-sm font-mono font-bold text-[#0f3460]">{{ applicant.application_number }}</p>
                            </div>
                            <div>
                                <p class="info-label">Topshirilgan sana</p>
                                <p class="info-value">{{ formatDate(applicant.created_at) }}</p>
                            </div>
                            <div v-if="applicant.interview_at">
                                <p class="info-label">Suhbat sanasi</p>
                                <p class="info-value">{{ formatDate(applicant.interview_at) }}</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    applicant: { type: Object, required: true },
})

const selectedStatus = ref(props.applicant.status)

const statuses = [
    { value: 'new',        label: 'Yangi',            icon: 'mdi:file-outline',        class: 'bg-blue-50 text-blue-700' },
    { value: 'accepted',   label: 'Qabul qilindi',    icon: 'mdi:check-circle-outline', class: 'bg-green-50 text-green-700' },
    { value: 'interview',  label: 'Suhbat',           icon: 'mdi:account-voice',        class: 'bg-yellow-50 text-yellow-700' },
    { value: 'tested',     label: 'Test',             icon: 'mdi:clipboard-text-outline', class: 'bg-purple-50 text-purple-700' },
    { value: 'contracted', label: 'Kontrakt',         icon: 'mdi:file-sign',            class: 'bg-indigo-50 text-indigo-700' },
    { value: 'enrolled',   label: "Ro'yxatga olindi", icon: 'mdi:school-outline',       class: 'bg-teal-50 text-teal-700' },
    { value: 'rejected',   label: 'Rad etildi',       icon: 'mdi:close-circle-outline', class: 'bg-red-50 text-red-700' },
]

const masterFiles = [
    { key: 'passport_file',         label: 'Pasport nusxasi' },
    { key: 'diploma_file',          label: 'Diplom nusxasi' },
    { key: 'diploma_appendix_file', label: 'Diplom ilovasi' },
]

const changeStatus = (status) => {
    selectedStatus.value = status
    router.patch(route('admin.applicants.status', props.applicant.id), { status }, {
        preserveState: true,
        preserveScroll: true,
    })
}

const updateStatus = () => {
    router.patch(route('admin.applicants.status', props.applicant.id), {
        status: selectedStatus.value
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

const statusBadge = (status) => {
    return statuses.find(s => s.value === status)?.class || 'bg-gray-50 text-gray-600'
}

const educationTypeLabel = (type) => {
    const types = { bachelor: 'Bakalavr', master: 'Magistr', transfer: 'Transfer', second: '2-mutaxassislik' }
    return types[type] || type
}

const educationTypeBadge = (type) => {
    const badges = {
        bachelor: 'bg-blue-50 text-blue-700',
        master:   'bg-purple-50 text-purple-700',
        transfer: 'bg-orange-50 text-orange-700',
        second:   'bg-teal-50 text-teal-700',
    }
    return badges[type] || 'bg-gray-50 text-gray-600'
}

const studyFormLabel = (form) => {
    const forms = { full_time: 'Kunduzgi', evening: 'Kechki', distance: 'Masofaviy' }
    return forms[form] || form
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('uz-UZ', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    })
}
</script>

<style scoped>
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
