<template>
    <AppLayout title="Turniket tekshiruvi">
        <div class="space-y-5">

            <!-- Header -->
            <div>
                <h1 class="text-xl font-bold text-gray-900">Turniket kirish tekshiruvi (test)</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    Bitta talabani tanlang — uning hozirgi kontrakt to'lov holatiga qarab turniket
                    ochadimi yo'qmi shu yerda ko'rasiz. Bu haqiqiy terminalga hech qanday ta'sir
                    qilmaydi — faqat qaror mantig'ini sinab ko'rish uchun.
                </p>
            </div>

            <!-- Qidiruv -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="relative">
                    <Icon icon="mdi:magnify" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="F.I.O., talaba raqami, HEMIS ID, JSHSHIR yoki pasport seriyasi..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-brand-600 bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <!-- Qidiruv natijalari -->
                <div v-if="search.trim() && results.length" class="mt-3 divide-y divide-gray-100 border border-gray-100 rounded-xl overflow-hidden">
                    <button v-for="s in results" :key="s.id"
                            @click="selectStudent(s)"
                            class="w-full text-left px-4 py-2.5 hover:bg-gray-50 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ s.name }}</p>
                            <p class="text-xs text-gray-400">
                                {{ s.student_number || '—' }}
                                <span v-if="s.direction"> · {{ s.direction }}</span>
                                <span v-if="s.course_year"> · {{ s.course_year }}-kurs</span>
                            </p>
                        </div>
                        <span class="badge-pill" :class="s.status === 'active' ? 'badge-success' : 'badge-neutral'">
                            {{ s.status === 'active' ? 'Faol' : s.status }}
                        </span>
                    </button>
                </div>
                <p v-else-if="search.trim() && searched && !results.length" class="mt-3 text-xs text-gray-400 px-1">
                    Hech narsa topilmadi.
                </p>
            </div>

            <!-- Tanlangan talaba + natija -->
            <div v-if="selected" class="bg-white rounded-2xl border border-gray-100 p-5"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-brand-50 flex items-center justify-center">
                            <Icon icon="mdi:account" class="w-5 h-5 text-brand-600" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ selected.name }}</p>
                            <p class="text-xs text-gray-400">
                                {{ selected.student_number || '—' }}
                                <span v-if="selected.hemis_id"> · HEMIS: {{ selected.hemis_id }}</span>
                            </p>
                        </div>
                    </div>
                    <button @click="check" :disabled="loading" class="btn-brand">
                        <Icon icon="mdi:refresh" class="w-4 h-4" :class="{ 'animate-spin': loading }" />
                        {{ loading ? 'Tekshirilmoqda...' : 'Qayta tekshirish' }}
                    </button>
                </div>

                <!-- Natija -->
                <div v-if="result" class="rounded-2xl p-5 border"
                     :class="result.allowed ? 'bg-green-50 border-green-100' : 'bg-red-50 border-red-100'">
                    <div class="flex items-center gap-3">
                        <Icon :icon="result.allowed ? 'mdi:lock-open-variant' : 'mdi:lock'"
                              class="w-8 h-8"
                              :class="result.allowed ? 'text-green-600' : 'text-red-600'" />
                        <div>
                            <p class="text-lg font-bold" :class="result.allowed ? 'text-green-700' : 'text-red-700'">
                                {{ result.allowed ? 'Turniket OCHILADI' : 'Turniket YOPILADI' }}
                            </p>
                            <p class="text-sm" :class="result.allowed ? 'text-green-700' : 'text-red-700'">
                                {{ result.message }}
                            </p>
                        </div>
                    </div>

                    <div v-if="result.debt_amount !== undefined" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4">
                        <div class="bg-white/70 rounded-xl p-3">
                            <p class="text-xs text-gray-500">Qarz</p>
                            <p class="text-sm font-semibold text-gray-900">{{ formatSum(result.debt_amount) }}</p>
                        </div>
                        <div class="bg-white/70 rounded-xl p-3">
                            <p class="text-xs text-gray-500">Talab qilingan</p>
                            <p class="text-sm font-semibold text-gray-900">{{ formatSum(result.required_amount) }}</p>
                        </div>
                        <div class="bg-white/70 rounded-xl p-3">
                            <p class="text-xs text-gray-500">To'langan</p>
                            <p class="text-sm font-semibold text-gray-900">{{ formatSum(result.paid_amount) }}</p>
                        </div>
                        <div class="bg-white/70 rounded-xl p-3">
                            <p class="text-xs text-gray-500">Keyingi muddat</p>
                            <p class="text-sm font-semibold text-gray-900">{{ result.next_deadline || '—' }}</p>
                        </div>
                    </div>

                    <p v-if="result.reason" class="text-xs text-gray-400 mt-3">
                        Sabab kodi: <span class="font-mono">{{ result.reason }}</span>
                    </p>
                </div>
            </div>

            <div v-else class="bg-white rounded-2xl border border-gray-100 p-16 text-center text-gray-400">
                <Icon icon="mdi:fingerprint" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                <p class="text-sm">Tekshirish uchun avval talabani qidirib tanlang</p>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Icon } from '@iconify/vue'
import { useToast } from 'vue-toastification'
import AppLayout from '@/Layouts/AppLayout.vue'

const toast = useToast()

const search = ref('')
const results = ref([])
const searched = ref(false)
const selected = ref(null)
const result = ref(null)
const loading = ref(false)

let debounceTimer = null
const debouncedSearch = () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(runSearch, 300)
}

const runSearch = async () => {
    const q = search.value.trim()
    if (!q) {
        results.value = []
        searched.value = false
        return
    }
    try {
        const res = await fetch(`/admin/turnstile/access-check/search?q=${encodeURIComponent(q)}`, {
            headers: { Accept: 'application/json' },
        })
        results.value = await res.json()
    } catch (e) {
        results.value = []
    } finally {
        searched.value = true
    }
}

const selectStudent = (s) => {
    selected.value = s
    search.value = ''
    results.value = []
    result.value = null
    check()
}

const check = async () => {
    if (!selected.value) return
    loading.value = true
    try {
        const res = await fetch(`/admin/turnstile/access-check/check?student_id=${selected.value.id}`, {
            headers: { Accept: 'application/json' },
        })
        if (!res.ok) {
            throw new Error('request failed')
        }
        result.value = await res.json()
    } catch (e) {
        toast.error('Tekshirishda xatolik yuz berdi')
    } finally {
        loading.value = false
    }
}

const formatSum = (value) => {
    if (value === null || value === undefined) return '—'
    return new Intl.NumberFormat('uz-UZ').format(value) + " so'm"
}
</script>
