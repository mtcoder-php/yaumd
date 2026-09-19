<template>
    <AppLayout title="Suhbatlar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Suhbatlar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Suhbat statusidagi abituriyentlar</p>
                </div>
            </div>

            <!-- Stat kartalar -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1 bg-brand-600" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Jami suhbatda</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1" style="background:#f59e0b" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Kutilmoqda</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.pending }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1" style="background:#22c55e" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">O'tdi</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.passed }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1" style="background:#ef4444" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">O'tmadi</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.failed }}</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4 flex flex-wrap gap-3"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <div class="flex-1 min-w-48 relative">
                    <Icon icon="mdi:magnify" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Ism yoki pasport seriyasi..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-brand-600 bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <select v-model="filters.direction_id" class="select-filter" @change="applyFilters">
                    <option value="">Barcha yo'nalishlar</option>
                    <option v-for="d in directions" :key="d.id" :value="d.id">{{ d.name_uz }}</option>
                </select>

                <button v-if="hasFilters" @click="resetFilters" class="btn-neutral">
                    <Icon icon="mdi:close" class="w-4 h-4" />
                    Tozalash
                </button>
            </div>

            <!-- Table -->
            <div class="table-grid-wrap">
                <table class="table-grid">
                    <thead>
                    <tr>
                        <th>Abituriyent</th>
                        <th>Yo'nalish</th>
                        <th>Suhbat natijasi</th>
                        <th>Kimlar o'tkazdi</th>
                        <th>Sana</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!applicants.data?.length">
                        <td colspan="6" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:account-off-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Suhbatda abituriyent topilmadi</p>
                            <p class="text-xs mt-1">Abituriyentni "Suhbat" statusiga o'tkazing</p>
                        </td>
                    </tr>
                    <tr v-for="a in applicants.data ?? []" :key="a.id">

                        <!-- Abituriyent -->
                        <td>
                            <p class="text-sm font-medium text-gray-900">
                                {{ a.last_name }} {{ a.first_name }}
                            </p>
                            <p class="text-xs text-gray-400 font-mono">{{ a.passport_series }}</p>
                        </td>

                        <!-- Yo'nalish -->
                        <td>
                            <p class="text-xs text-gray-700">{{ a.direction?.name_uz || '—' }}</p>
                            <p class="text-xs text-gray-400">{{ a.direction?.department?.name_uz || '' }}</p>
                        </td>

                        <!-- Suhbat natijasi -->
                        <td>
                            <span v-if="a.interview" class="badge-pill" :class="a.interview.result === 'passed' ? 'badge-success' : 'badge-danger'">
                                <Icon :icon="a.interview.result === 'passed' ? 'mdi:check-circle' : 'mdi:close-circle'" class="w-3.5 h-3.5" />
                                {{ a.interview.result === 'passed' ? "O'tdi" : "O'tmadi" }}
                            </span>
                            <span v-else class="badge-pill badge-warning">
                                <Icon icon="mdi:clock-outline" class="w-3.5 h-3.5" />
                                Kutilmoqda
                            </span>
                        </td>

                        <!-- Kimlar o'tkazdi -->
                        <td class="text-sm text-gray-600">
                            {{ a.interview?.interviewer?.full_name || '—' }}
                        </td>

                        <!-- Sana -->
                        <td class="text-xs text-gray-400">
                            {{ a.interview?.interviewed_at ? formatDate(a.interview.interviewed_at) : '—' }}
                        </td>

                        <!-- Amallar -->
                        <td>
                            <div class="flex justify-end">
                                <button v-if="!a.interview" @click="openInterview(a)" class="btn-brand-outline">
                                    <Icon icon="mdi:account-check-outline" class="w-3.5 h-3.5" />
                                    Natija kiritish
                                </button>
                                <span v-else class="text-xs text-gray-400">Yakunlangan</span>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="(applicants.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-xs text-gray-500">{{ applicants.from }}–{{ applicants.to }} / {{ applicants.total }}</p>
                    <div class="flex items-center gap-1.5">
                        <template v-for="link in (applicants.links ?? [])" :key="link.label">
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

        <!-- Suhbat natijasi modal -->
        <div v-if="interviewModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="interviewModal = false">
            <div class="bg-white rounded-2xl w-full max-w-md p-6">

                <h3 class="text-base font-bold text-gray-900 mb-1">Suhbat natijasi</h3>
                <p class="text-sm text-gray-500 mb-5">
                    {{ selectedApplicant?.last_name }} {{ selectedApplicant?.first_name }}
                    <span class="font-mono text-gray-400 text-xs ml-1">({{ selectedApplicant?.passport_series }})</span>
                </p>

                <div class="space-y-4">

                    <!-- Natija -->
                    <div>
                        <label class="field-label"><span class="req">*</span> Natija</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button"
                                    @click="form.result = 'passed'"
                                    class="flex flex-col items-center gap-2 py-4 rounded-xl border-2 transition-all"
                                    :class="form.result === 'passed' ? 'result-passed-active' : 'result-idle'">
                                <Icon icon="mdi:check-circle-outline" class="w-8 h-8"
                                      :class="form.result === 'passed' ? 'text-green-500' : 'text-gray-400'" />
                                <span class="text-sm font-semibold"
                                      :class="form.result === 'passed' ? 'text-green-600' : 'text-gray-500'">
                                    O'tdi
                                </span>
                                <p class="text-xs text-gray-400">Test sessiyasi yaratiladi</p>
                            </button>
                            <button type="button"
                                    @click="form.result = 'failed'"
                                    class="flex flex-col items-center gap-2 py-4 rounded-xl border-2 transition-all"
                                    :class="form.result === 'failed' ? 'result-failed-active' : 'result-idle'">
                                <Icon icon="mdi:close-circle-outline" class="w-8 h-8"
                                      :class="form.result === 'failed' ? 'text-red-500' : 'text-gray-400'" />
                                <span class="text-sm font-semibold"
                                      :class="form.result === 'failed' ? 'text-red-600' : 'text-gray-500'">
                                    O'tmadi
                                </span>
                                <p class="text-xs text-gray-400">Rad etiladi</p>
                            </button>
                        </div>
                        <p v-if="formErrors.result" class="err">{{ formErrors.result }}</p>
                    </div>

                    <!-- Izoh -->
                    <div>
                        <label class="field-label">Izoh (ixtiyoriy)</label>
                        <RichTextEditor v-model="form.notes" placeholder="Suhbat haqida qisqacha izoh..." :height="160" />
                    </div>

                </div>

                <div class="flex gap-3 mt-6">
                    <button @click="interviewModal = false" class="btn-neutral flex-1 justify-center">Bekor qilish</button>
                    <button @click="submitInterview" :disabled="submitting || !form.result" class="btn-brand flex-1 justify-center">
                        <Icon v-if="submitting" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                        <Icon v-else icon="mdi:check" class="w-4 h-4" />
                        {{ submitting ? 'Saqlanmoqda...' : 'Saqlash' }}
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
import AppLayout from '@/Layouts/AppLayout.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'

const props = defineProps({
    applicants: { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
    filters:    { type: Object, default: () => ({}) },
    directions: { type: Array,  default: () => [] },
    stats:      { type: Object, default: () => ({}) },
})

const interviewModal    = ref(false)
const selectedApplicant = ref(null)
const submitting        = ref(false)
const formErrors        = ref({})

const form = ref({
    applicant_id: '',
    result:       '',
    notes:        '',
})

const filters = ref({
    search:       props.filters.search       || '',
    direction_id: props.filters.direction_id || '',
})

const hasFilters = computed(() => filters.value.search || filters.value.direction_id)

const applyFilters = () => {
    router.get(route('admin.interviews.index'), filters.value, {
        preserveState: true, replace: true,
    })
}

let searchTimer = null
const debouncedSearch = () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(applyFilters, 400)
}

const resetFilters = () => {
    filters.value = { search: '', direction_id: '' }
    applyFilters()
}

const openInterview = (applicant) => {
    selectedApplicant.value = applicant
    form.value = { applicant_id: applicant.id, result: '', notes: '' }
    formErrors.value = {}
    interviewModal.value = true
}

const submitInterview = () => {
    if (!form.value.result) {
        formErrors.value.result = 'Natijani tanlang'
        return
    }

    submitting.value = true
    router.post(route('admin.interviews.store'), form.value, {
        onSuccess: () => {
            interviewModal.value = false
            submitting.value     = false
        },
        onError: (errors) => {
            formErrors.value = errors
            submitting.value = false
        },
    })
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('uz-UZ', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}

// Laravel'ning standart pagination yorliqlari o'rniga sof strelka
// ikonkalarini ko'rsatish uchun (Foydalanuvchilar sahifasidagi bilan
// bir xil naqsh).
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
}
.field-input:focus { border-color: var(--color-brand-600); background: white; }
.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }

.result-idle { border-color: #e5e7eb; background: #fafafa; }
.result-passed-active { border-color: #22c55e; background: #f0fdf4; }
.result-failed-active { border-color: #ef4444; background: #fef2f2; }
</style>
