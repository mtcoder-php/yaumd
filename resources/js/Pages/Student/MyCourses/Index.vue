<template>
    <AppLayout title="Kurslarim">
        <div class="max-w-5xl mx-auto space-y-5">

            <div>
                <h1 class="text-xl font-bold text-gray-900">Kurslarim</h1>
                <p class="text-sm text-gray-500 mt-0.5">Sizga yozilgan kurslar ro'yxati</p>
            </div>

            <div v-if="!enrollments.length"
                 class="bg-white rounded-2xl border border-gray-100 p-16 text-center text-gray-400"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <Icon icon="mdi:school-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                <p class="text-sm">Hozircha hech qanday kursga yozilmagansiz</p>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <Link v-for="e in enrollments" :key="e.id"
                      :href="route('admin.my-courses.show', e.course_id)"
                      class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md transition-all block"
                      style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="flex items-start gap-4">
                        <div class="w-16 h-16 rounded-xl bg-gray-100 overflow-hidden flex items-center justify-center flex-shrink-0">
                            <img v-if="e.course?.thumbnail_url" :src="e.course.thumbnail_url" class="w-full h-full object-cover" alt="">
                            <Icon v-else icon="mdi:school-outline" class="w-7 h-7 text-gray-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ e.course?.title_uz }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ e.course?.category?.name_uz || 'Kategoriyasiz' }}</p>
                            <div class="flex items-center gap-2 mt-3">
                                <div class="flex-1 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                                    <div class="h-full rounded-full" :style="`width:${e.progress || 0}%; background: linear-gradient(135deg,#0f3460,#533483)`" />
                                </div>
                                <span class="text-xs font-semibold text-gray-600 flex-shrink-0">{{ e.progress || 0 }}%</span>
                            </div>
                            <span class="inline-flex mt-2 px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="statusClass(e.status)">
                                {{ statusLabel(e.status) }}
                            </span>
                        </div>
                    </div>
                </Link>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
    enrollments: { type: Array, default: () => [] },
})

const statusLabel = (v) => ({ active: 'Faol', completed: 'Tugatilgan', dropped: "Tark etilgan", expired: 'Muddati o\'tgan' }[v] || v)
const statusClass = (v) => ({
    active:    'bg-blue-50 text-blue-700',
    completed: 'bg-green-50 text-green-700',
    dropped:   'bg-gray-100 text-gray-500',
    expired:   'bg-amber-50 text-amber-700',
}[v] || 'bg-gray-100 text-gray-500')
</script>
