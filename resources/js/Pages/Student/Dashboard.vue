<template>
    <AppLayout title="Dashboard">
        <div class="space-y-6">

            <div>
                <h1 class="text-xl font-bold text-gray-900">Xush kelibsiz, {{ firstName }}!</h1>
                <p class="text-sm text-gray-500 mt-0.5">Kurslaringiz, kutubxonangiz va shartnomangiz haqida qisqacha ma'lumot</p>
            </div>

            <!-- Stat kartalar -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    v-for="card in statCards"
                    :key="card.label"
                    class="bg-white rounded-2xl border border-gray-100 p-5 flex items-start gap-4"
                    style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)"
                >
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" :style="{ background: card.bg }">
                        <Icon :icon="card.icon" class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">{{ card.label }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ card.value }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                <!-- Davom eting -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-bold text-gray-700">Davom eting</h2>
                        <Link :href="route('admin.my-courses.index')" class="text-xs font-semibold" style="color:#0f3460">
                            Barchasi →
                        </Link>
                    </div>

                    <div v-if="!continueLearning.length" class="py-10 text-center text-gray-400">
                        <Icon icon="mdi:school-outline" class="w-10 h-10 mx-auto mb-2 opacity-40" />
                        <p class="text-sm">Hozircha faol kursingiz yo'q</p>
                        <Link :href="route('admin.course-catalog.index')" class="inline-block mt-3 text-xs font-semibold" style="color:#0f3460">
                            Kurslar katalogini ko'rish →
                        </Link>
                    </div>

                    <div v-else class="space-y-3">
                        <Link v-for="c in continueLearning" :key="c.enrollment_id"
                              :href="route('admin.my-courses.show', c.course_id)"
                              class="flex items-center gap-4 p-3 rounded-xl hover:bg-gray-50 transition">
                            <div class="w-14 h-14 rounded-xl bg-gray-100 overflow-hidden flex items-center justify-center flex-shrink-0">
                                <img v-if="c.thumbnail_url" :src="c.thumbnail_url" class="w-full h-full object-cover" alt="">
                                <Icon v-else icon="mdi:school-outline" class="w-6 h-6 text-gray-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ c.title }}</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <div class="flex-1 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                                        <div class="h-full rounded-full" :style="`width:${c.progress}%; background: linear-gradient(135deg,#0f3460,#533483)`" />
                                    </div>
                                    <span class="text-xs font-semibold text-gray-500 flex-shrink-0">{{ c.progress }}%</span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Shartnoma qisqacha -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-bold text-gray-700">Shartnoma</h2>
                        <Link v-if="contract" :href="route('admin.my-contract.show')" class="text-xs font-semibold" style="color:#0f3460">
                            Batafsil →
                        </Link>
                    </div>

                    <div v-if="!contract" class="py-8 text-center text-gray-400">
                        <Icon icon="mdi:file-document-outline" class="w-9 h-9 mx-auto mb-2 opacity-40" />
                        <p class="text-xs">Shartnoma mavjud emas (grant asosida)</p>
                    </div>

                    <div v-else class="space-y-3">
                        <div class="grid grid-cols-2 gap-2 text-center">
                            <div class="bg-green-50 rounded-lg p-2.5">
                                <p class="text-xs text-green-600 mb-0.5">To'langan</p>
                                <p class="text-sm font-bold text-green-700">{{ formatPrice(contract.paid_amount) }}</p>
                            </div>
                            <div class="bg-amber-50 rounded-lg p-2.5">
                                <p class="text-xs text-amber-600 mb-0.5">Qolgan</p>
                                <p class="text-sm font-bold text-amber-700">{{ formatPrice(contract.remaining_amount) }}</p>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>To'langan qismi</span>
                                <span class="font-semibold">{{ contract.paid_percent }}%</span>
                            </div>
                            <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full rounded-full" :style="`width:${contract.paid_percent}%; background: linear-gradient(135deg,#0f3460,#533483)`" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    continueLearning: { type: Array, default: () => [] },
    contract: { type: Object, default: null },
})

const page = usePage()
// MUHIM: full_name "FAMILYA ISM SHARIF" tartibida saqlanadi (masalan
// "TO'XTASINOV FURQATJON SOBIRJON O'G'LI") — shuning uchun birinchi so'z
// emas, IKKINCHI so'z (ism) olinadi. Agar kimdir bir so'zli ism kiritgan
// bo'lsa (juda kam holat), o'sha yagona so'z ishlatiladi.
const firstName = computed(() => {
    const parts = (page.props.auth?.user?.full_name || '').trim().split(/\s+/)
    return parts[1] || parts[0] || 'Talaba'
})

const formatPrice = (v) => new Intl.NumberFormat('uz-UZ').format(v || 0) + " so'm"

const statCards = computed(() => [
    { label: 'Faol kurslar', value: props.stats.enrollments_active ?? 0, icon: 'mdi:book-open-page-variant-outline', bg: 'linear-gradient(135deg,#4f46e5,#7c3aed)' },
    { label: 'Tugatilgan kurslar', value: props.stats.enrollments_completed ?? 0, icon: 'mdi:certificate-outline', bg: 'linear-gradient(135deg,#16a34a,#22c55e)' },
    { label: "O'rtacha progress", value: `${props.stats.avg_progress ?? 0}%`, icon: 'mdi:chart-line', bg: 'linear-gradient(135deg,#db2777,#ec4899)' },
    { label: 'Kutubxonadagi kitoblar', value: props.stats.library_access_count ?? 0, icon: 'mdi:bookshelf', bg: 'linear-gradient(135deg,#0f3460,#533483)' },
])
</script>
