<template>
    <AppLayout title="Dashboard">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div
                v-for="stat in stats"
                :key="stat.label"
                class="bg-white rounded-xl border border-gray-200 p-5"
            >
                <p class="text-xs text-gray-400 mb-1">{{ stat.label }}</p>
                <p class="text-2xl font-semibold text-gray-900">{{ stat.value }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-sm font-medium text-gray-700 mb-4">Tizim holati</h2>
            <div class="space-y-3">
                <div
                    v-for="item in status"
                    :key="item.label"
                    class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0"
                >
                    <span class="text-sm text-gray-600">{{ item.label }}</span>
                    <span
                        class="text-xs px-2.5 py-1 rounded-full font-medium"
                        :class="item.ok
                            ? 'bg-green-50 text-green-700'
                            : 'bg-red-50 text-red-600'"
                    >
                        {{ item.ok ? 'Ishlayapti' : 'Xato' }}
                    </span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'

const stats = [
    { label: 'Talabalar', value: '0' },
    { label: 'Abituriyentlar', value: '0' },
    { label: 'Kurslar', value: '0' },
    { label: 'Kitoblar', value: '0' },
]

const status = [
    { label: 'Ma\'lumotlar bazasi', ok: true },
    { label: 'Auth tizimi', ok: true },
    { label: 'RBAC / Rollar', ok: true },
    { label: 'SMS Gateway', ok: false },
    { label: 'HEMIS integratsiya', ok: false },
]
</script>
