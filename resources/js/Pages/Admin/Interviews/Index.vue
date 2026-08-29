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
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-white rounded-xl border border-gray-100 p-4"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <p class="text-xs text-gray-400 mb-1">Jami suhbatda</p>
                    <p class="text-2xl font-bold text-[#0f3460]">{{ stats.total }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <p class="text-xs text-gray-400 mb-1">Kutilmoqda</p>
                    <p class="text-2xl font-bold text-amber-500">{{ stats.pending }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <p class="text-xs text-gray-400 mb-1">O'tdi</p>
                    <p class="text-2xl font-bold text-green-600">{{ stats.passed }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <p class="text-xs text-gray-400 mb-1">O'tmadi</p>
                    <p class="text-2xl font-bold text-red-500">{{ stats.failed }}</p>
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
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <select v-model="filters.direction_id"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                        @change="applyFilters">
                    <option value="">Barcha yo'nalishlar</option>
                    <option v-for="d in directions" :key="d.id" :value="d.id">{{ d.name_uz }}</option>
                </select>

                <button v-if="hasFilters" @click="resetFilters"
                        class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 flex items-center gap-1.5">
                    <Icon icon="mdi:close" class="w-4 h-4" />
                    Tozalash
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Abituriyent</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Yo'nalish</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Suhbat natijasi</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Kimlar o'tkazdi</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Sana</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                    <tr v-if="!applicants.data?.length">
                        <td colspan="6" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:account-off-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Suhbatda abituriyent topilmadi</p>
                            <p class="text-xs mt-1">Abituriyentni "Suhbat" statusiga o'tkazing</p>
                        </td>
                    </tr>
                    <tr v-for="a in applicants.data ?? []" :key="a.id"
                        class="hover:bg-gray-50 transition-colors">

                        <!-- Abituriyent -->
                        <td class="px-4 py-3">
                            <p class="text-sm font-medium text-gray-900">
                                {{ a.last_name }} {{ a.first_name }}
                            </p>
                            <p class="text-xs text-gray-400 font-mono">{{ a.passport_series }}</p>
                        </td>

                        <!-- Yo'nalish -->
                        <td class="px-4 py-3">
                            <p class="text-xs text-gray-700">{{ a.direction?.name_uz || '—' }}</p>
                            <p class="text-xs text-gray-400">{{ a.direction?.department?.name_uz || '' }}</p>
                        </td>

                        <!-- Suhbat natijasi -->
                        <td class="px-4 py-3">
                                <span v-if="a.interview"
                                      class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                      :class="a.interview.result === 'passed'
                                        ? 'bg-green-50 text-green-700'
                                        : 'bg-red-50 text-red-700'">
                                    <Icon :icon="a.interview.result === 'passed' ? 'mdi:check-circle' : 'mdi:close-circle'" class="w-3.5 h-3.5" />
                                    {{ a.interview.result === 'passed' ? "O'tdi" : "O'tmadi" }}
                                </span>
                            <span v-else class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-700">
                                    <Icon icon="mdi:clock-outline" class="w-3.5 h-3.5" />
                                    Kutilmoqda
                                </span>
                        </td>

                        <!-- Kimlar o'tkazdi -->
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ a.interview?.interviewer?.full_name || '—' }}
                        </td>

                        <!-- Sana -->
                        <td class="px-4 py-3 text-xs text-gray-400">
                            {{ a.interview?.interviewed_at ? formatDate(a.interview.interviewed_at) : '—' }}
                        </td>

                        <!-- Amallar -->
                        <td class="px-4 py-3">
                            <button
                                v-if="!a.interview"
                                @click="openInterview(a)"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-white transition"
                                style="background: linear-gradient(135deg, #0f3460, #533483)">
                                <Icon icon="mdi:account-check-outline" class="w-3.5 h-3.5" />
                                Natija kiritish
                            </button>
                            <span v-else class="text-xs text-gray-400">Yakunlangan</span>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="(applicants.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500">{{ applicants.from }}–{{ applicants.to }} / {{ applicants.total }}</p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in (applicants.links ?? [])" :key="link.label">
                            <Link v-if="link.url" :href="link.url"
                                  class="px-3 py-1.5 text-xs rounded-lg transition"
                                  :class="link.active ? 'text-white font-semibold' : 'text-gray-500 hover:bg-gray-100'"
                                  :style="link.active ? 'background:linear-gradient(135deg,#0f3460,#533483)' : ''"
                                  v-html="link.label" />
                            <span v-else class="px-3 py-1.5 text-xs text-gray-300" v-html="link.label" />
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
                        <label class="field-label">Natija <span class="req">*</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button"
                                    @click="form.result = 'passed'"
                                    class="flex flex-col items-center gap-2 py-4 rounded-xl border-2 transition-all"
                                    :style="form.result === 'passed'
                                    ? 'border-color:#22c55e; background:#f0fdf4'
                                    : 'border-color:#e5e7eb; background:#fafafa'">
                                <Icon icon="mdi:check-circle-outline" class="w-8 h-8"
                                      :style="form.result === 'passed' ? 'color:#22c55e' : 'color:#9ca3af'" />
                                <span class="text-sm font-semibold"
                                      :style="form.result === 'passed' ? 'color:#16a34a' : 'color:#6b7280'">
                                    O'tdi
                                </span>
                                <p class="text-xs text-gray-400">Test sessiyasi yaratiladi</p>
                            </button>
                            <button type="button"
                                    @click="form.result = 'failed'"
                                    class="flex flex-col items-center gap-2 py-4 rounded-xl border-2 transition-all"
                                    :style="form.result === 'failed'
                                    ? 'border-color:#ef4444; background:#fef2f2'
                                    : 'border-color:#e5e7eb; background:#fafafa'">
                                <Icon icon="mdi:close-circle-outline" class="w-8 h-8"
                                      :style="form.result === 'failed' ? 'color:#ef4444' : 'color:#9ca3af'" />
                                <span class="text-sm font-semibold"
                                      :style="form.result === 'failed' ? 'color:#dc2626' : 'color:#6b7280'">
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
                        <textarea v-model="form.notes" rows="3" class="field-input"
                                  placeholder="Suhbat haqida qisqacha izoh..."
                                  style="resize:none"></textarea>
                    </div>

                </div>

                <div class="flex gap-3 mt-6">
                    <button @click="interviewModal = false" class="btn-secondary flex-1">Bekor qilish</button>
                    <button @click="submitInterview" :disabled="submitting || !form.result"
                            class="btn-primary flex-1"
                            :class="!form.result ? 'opacity-50 cursor-not-allowed' : ''">
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
import AppLayout from '@/Layouts/AppLayout.vue'

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
}
.field-input:focus { border-color: #0f3460; background: white; }
.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }

.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
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
}
.btn-secondary:hover { background: #f9fafb; }
</style>
