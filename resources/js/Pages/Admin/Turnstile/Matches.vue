<template>
    <AppLayout title="Turniket moslashtirish">
        <div class="space-y-5">

            <!-- Header -->
            <div>
                <h1 class="text-xl font-bold text-gray-900">Turniket — shaxslarni moslashtirish</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    Face ID terminallardan kelgan employeeNo'larni (masalan "s6236", "u596")
                    talaba/xodim yozuvlari bilan solishtirish
                </p>
            </div>

            <!-- Status tab'lari -->
            <div class="flex flex-wrap gap-2">
                <button v-for="tab in tabs" :key="tab.value"
                        @click="setStatus(tab.value)"
                        class="px-4 py-2 text-sm rounded-xl border transition flex items-center gap-2"
                        :class="filters.status === tab.value
                            ? 'bg-brand-600 text-white border-transparent'
                            : 'text-gray-600 border-gray-200 hover:bg-gray-50'">
                    {{ tab.label }}
                    <span class="text-xs px-1.5 py-0.5 rounded-full"
                          :class="filters.status === tab.value ? 'bg-white/20' : 'bg-gray-100 text-gray-500'">
                        {{ counts[tab.value] ?? 0 }}
                    </span>
                </button>
            </div>

            <!-- Qidiruv -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4 flex flex-wrap gap-3"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="flex-1 min-w-48 relative">
                    <Icon icon="mdi:magnify" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="employeeNo yoki ism bo'yicha qidirish..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-brand-600 bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>
                <button v-if="filters.search" @click="filters.search = ''; applyFilters()" class="btn-neutral">
                    <Icon icon="mdi:close" class="w-4 h-4" />
                    Tozalash
                </button>
            </div>

            <!-- Ro'yxat -->
            <div class="space-y-3">
                <div v-if="!matches.data?.length"
                     class="bg-white rounded-2xl border border-gray-100 p-16 text-center text-gray-400">
                    <Icon icon="mdi:account-search-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                    <p class="text-sm">Hech narsa topilmadi</p>
                </div>

                <div v-for="m in matches.data ?? []" :key="m.id"
                     class="bg-white rounded-2xl border border-gray-100 p-4"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-sm font-bold text-brand-600">{{ m.employee_no }}</span>
                                <span class="badge-pill" :class="statusClass(m.status)">
                                    {{ statusLabel(m.status) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-700 mt-1">{{ m.person_name || '—' }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                Terminaldan kelgan ism (o'zgartirish mumkin emas — faqat solishtirish uchun)
                            </p>
                        </div>

                        <!-- Allaqachon hal qilingan bo'lsa -->
                        <div v-if="m.status === 'auto_matched' || m.status === 'matched'" class="text-right">
                            <p class="text-sm font-semibold text-green-700">{{ m.matched_name }}</p>
                            <p class="text-xs text-gray-400">
                                {{ m.matched_type === 'student' ? 'Talaba' : 'Xodim' }}
                                <span v-if="m.confidence !== null"> · {{ Math.round(m.confidence * 100) }}%</span>
                            </p>
                        </div>
                    </div>

                    <!-- Rad etilgan bo'lsa -->
                    <p v-if="m.status === 'rejected'" class="text-xs text-gray-400 mt-2">
                        Admin tomonidan rad etilgan — YAUMD'da mos yozuv yo'q deb belgilangan.
                    </p>

                    <!-- Hal qilinmagan bo'lsa: taklif etilgan nomzodlar + qidiruv -->
                    <div v-if="m.status === 'needs_review' || m.status === 'unmatched'" class="mt-3 space-y-3">

                        <div v-if="m.candidates?.length" class="flex flex-wrap gap-2">
                            <button v-for="c in m.candidates" :key="`${c.type}-${c.id}`"
                                    @click="assign(m, c.type, c.id, c.name)"
                                    class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 hover:border-brand-600 hover:bg-brand-50 flex items-center gap-1.5">
                                <Icon icon="mdi:check-circle-outline" class="w-3.5 h-3.5 text-green-600" />
                                {{ c.name }}
                                <span class="text-gray-400">({{ Math.round(c.score * 100) }}%)</span>
                            </button>
                        </div>
                        <p v-else class="text-xs text-gray-400">Avtomatik taklif topilmadi.</p>

                        <div class="flex flex-wrap items-center gap-2">
                            <button @click="toggleSearch(m.id)"
                                    class="text-xs font-medium text-gray-500 hover:text-gray-700 flex items-center gap-1">
                                <Icon icon="mdi:magnify" class="w-3.5 h-3.5" />
                                Boshqa nomzod qidirish
                            </button>
                            <button @click="reject(m)"
                                    class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1">
                                <Icon icon="mdi:close-circle-outline" class="w-3.5 h-3.5" />
                                Mos yozuv yo'q (rad etish)
                            </button>
                        </div>

                        <!-- Qo'lda qidiruv paneli -->
                        <div v-if="openSearchId === m.id" class="border border-gray-100 rounded-xl p-3 bg-gray-50 space-y-2">
                            <div class="flex gap-2">
                                <select v-model="searchType" class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white">
                                    <option value="student">Talaba</option>
                                    <option value="staff">Xodim</option>
                                </select>
                                <input v-model="searchQuery" @input="debouncedSearchCandidates"
                                       type="text" placeholder="Ism yoki ID bo'yicha qidirish..."
                                       class="flex-1 text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:outline-none focus:border-brand-600">
                            </div>
                            <div v-if="searchResults.length" class="space-y-1 max-h-48 overflow-y-auto">
                                <button v-for="r in searchResults" :key="`${r.type}-${r.id}`"
                                        @click="assign(m, r.type, r.id, r.name)"
                                        class="w-full text-left px-3 py-1.5 text-xs rounded-lg hover:bg-white flex items-center justify-between">
                                    <span>{{ r.name }}</span>
                                    <span class="text-gray-400">{{ r.extra }}</span>
                                </button>
                            </div>
                            <p v-else-if="searchQuery" class="text-xs text-gray-400 px-1">Natija yo'q</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="(matches.last_page ?? 1) > 1"
                 class="bg-white rounded-2xl border border-gray-100 px-4 py-3 flex items-center justify-between flex-wrap gap-3"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <p class="text-xs text-gray-500">{{ matches.from }}–{{ matches.to }} / {{ matches.total }}</p>
                <div class="flex items-center gap-1.5">
                    <template v-for="link in (matches.links ?? [])" :key="link.label">
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
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
import { useToast } from 'vue-toastification'
import AppLayout from '@/Layouts/AppLayout.vue'

const toast = useToast()

const STATUS_STYLES = {
    needs_review: { label: "Ko'rib chiqish", class: 'badge-warning' },
    unmatched: { label: 'Topilmadi', class: 'badge-neutral' },
    auto_matched: { label: 'Avtomatik', class: 'badge-brand' },
    matched: { label: 'Tasdiqlangan', class: 'badge-success' },
    rejected: { label: 'Rad etilgan', class: 'badge-danger' },
}

const statusLabel = (status) => STATUS_STYLES[status]?.label || status
const statusClass = (status) => STATUS_STYLES[status]?.class || 'badge-neutral'

const props = defineProps({
    matches: { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
    counts: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
})

const tabs = [
    { value: 'needs_review', label: "Ko'rib chiqish kerak" },
    { value: 'unmatched', label: 'Topilmadi' },
    { value: 'auto_matched', label: 'Avtomatik moslashtirilgan' },
    { value: 'matched', label: 'Tasdiqlangan' },
    { value: 'rejected', label: 'Rad etilgan' },
]

const filters = ref({
    status: props.filters.status || 'needs_review',
    search: props.filters.search || '',
})

const applyFilters = () => {
    router.get(route('admin.turnstile.matches.index'), filters.value, {
        preserveState: true, replace: true,
    })
}

const setStatus = (status) => {
    filters.value.status = status
    applyFilters()
}

let searchTimer = null
const debouncedSearch = () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(applyFilters, 400)
}

// Qo'lda nomzod qidirish paneli
const openSearchId = ref(null)
const searchType = ref('student')
const searchQuery = ref('')
const searchResults = ref([])

const toggleSearch = (id) => {
    openSearchId.value = openSearchId.value === id ? null : id
    searchQuery.value = ''
    searchResults.value = []
}

let candidateSearchTimer = null
const debouncedSearchCandidates = () => {
    clearTimeout(candidateSearchTimer)
    candidateSearchTimer = setTimeout(async () => {
        if (!searchQuery.value.trim()) {
            searchResults.value = []
            return
        }
        try {
            const response = await fetch(
                route('admin.turnstile.matches.search') + `?type=${searchType.value}&q=${encodeURIComponent(searchQuery.value)}`,
                { headers: { Accept: 'application/json' } }
            )
            searchResults.value = await response.json()
        } catch (e) {
            searchResults.value = []
        }
    }, 300)
}

const assign = (match, type, id, name) => {
    if (!confirm(`"${match.employee_no}" ni "${name}" bilan moslashtirishni tasdiqlaysizmi?`)) {
        return
    }
    router.post(route('admin.turnstile.matches.assign', match.id), { type, id }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Muvaffaqiyatli moslashtirildi')
            openSearchId.value = null
        },
    })
}

const reject = (match) => {
    if (!confirm(`"${match.employee_no}" uchun mos YAUMD yozuvi yo'q deb belgilaysizmi? Bu holat keyingi avtomatik moslashtirishlarda o'tkazib yuboriladi.`)) {
        return
    }
    router.post(route('admin.turnstile.matches.reject', match.id), {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('Rad etildi'),
    })
}

// Laravel'ning standart pagination yorliqlari o'rniga sof strelka
// ikonkalarini ko'rsatish uchun (boshqa sahifalardagi bilan bir xil naqsh).
const isPrevLabel = (label) => /Previous|&laquo;|«/i.test(label)
const isNextLabel = (label) => /Next|&raquo;|»/i.test(label)
</script>
