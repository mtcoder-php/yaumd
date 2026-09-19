<template>
    <AppLayout title="Kurs kategoriyalari">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Kurs kategoriyalari</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Jami: {{ categories.length }} ta kategoriya</p>
                </div>
                <Link :href="route('admin.course-categories.create')" class="btn-brand">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Yangi kategoriya
                </Link>
            </div>

            <!-- Table -->
            <div class="table-grid-wrap">
                <table class="table-grid">
                    <thead>
                    <tr>
                        <th>Kategoriya</th>
                        <th>Ota-kategoriya</th>
                        <th class="text-center">Kurslar</th>
                        <th class="text-center">Tartib</th>
                        <th>Status</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!categories.length">
                        <td colspan="6" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:shape-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Kategoriya topilmadi</p>
                        </td>
                    </tr>
                    <tr v-for="c in categories" :key="c.id">
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white flex-shrink-0"
                                     :style="`background: ${c.color || 'var(--color-brand-600)'}`">
                                    <Icon :icon="c.icon || 'mdi:shape-outline'" class="w-5 h-5" />
                                </div>
                                <p class="text-sm font-semibold text-gray-900">{{ c.name_uz }}</p>
                            </div>
                        </td>
                        <td class="text-sm text-gray-600">{{ c.parent?.name_uz || '—' }}</td>
                        <td class="text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-sm font-bold bg-brand-50 text-brand-600">
                                {{ c.courses_count }}
                            </span>
                        </td>
                        <td class="text-center text-sm text-gray-600">{{ c.order }}</td>
                        <td>
                            <span class="badge-pill" :class="c.is_active ? 'badge-success' : 'badge-neutral'">
                                {{ c.is_active ? 'Faol' : 'Nofaol' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="route('admin.course-categories.edit', c.id)" title="Tahrirlash" class="btn-ghost-icon">
                                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                                </Link>
                                <button @click="confirmDelete(c)" title="O'chirish" class="btn-ghost-icon danger">
                                    <Icon icon="mdi:delete-outline" class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Delete modal -->
        <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="deleteTarget = null">
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <Icon icon="mdi:delete-outline" class="w-6 h-6 text-red-500" />
                </div>
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Kategoriyani o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget?.name_uz }}</strong> kategoriyasini o'chirasizmi?
                </p>
                <div class="flex gap-3">
                    <button @click="deleteTarget = null" class="btn-neutral flex-1 justify-center">Bekor qilish</button>
                    <button @click="submitDelete" class="btn-danger-pill flex-1">O'chirish</button>
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

defineProps({
    categories: { type: Array, default: () => [] },
})

const deleteTarget = ref(null)
const confirmDelete = (c) => { deleteTarget.value = c }
const submitDelete = () => {
    router.delete(route('admin.course-categories.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
    })
}
</script>
