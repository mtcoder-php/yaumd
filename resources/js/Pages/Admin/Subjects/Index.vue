<template>
    <AppLayout title="Fanlar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Fanlar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Test savollari uchun fanlar ro'yxati</p>
                </div>
                <Link :href="route('admin.subjects.create')" class="btn-primary">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Yangi fan
                </Link>
            </div>

            <!-- Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-if="!subjects.length"
                    class="col-span-full text-center py-16 text-gray-400 bg-white rounded-2xl border border-gray-100"
                >
                    <Icon icon="mdi:book-off-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                    <p class="text-sm">Fan topilmadi</p>
                </div>

                <div
                    v-for="subject in subjects"
                    :key="subject.id"
                    class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md transition-all"
                    style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)"
                >
                    <!-- Fan sarlavhasi -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                style="background: linear-gradient(135deg, #0f3460, #533483)"
                            >
                                <Icon icon="mdi:book-open-outline" class="w-5 h-5 text-white" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ subject.name_uz }}</p>
                                <p class="text-xs text-gray-400">{{ subject.name_ru }}</p>
                            </div>
                        </div>
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold flex-shrink-0"
                            :class="subject.is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'"
                        >
                            {{ subject.is_active ? 'Faol' : 'Nofaol' }}
                        </span>
                    </div>

                    <!-- Savollar soni -->
                    <Link
                        :href="route('admin.subjects.questions.index', subject.id)"
                        class="flex items-center justify-between p-3 rounded-xl mb-3 transition-all hover:opacity-80"
                        style="background: linear-gradient(135deg, #eff6ff, #f5f3ff)"
                    >
                        <div class="flex items-center gap-2">
                            <Icon icon="mdi:help-circle-outline" class="w-4 h-4" style="color:#0f3460" />
                            <span class="text-sm font-semibold" style="color:#0f3460">
                                {{ subject.questions_count }} ta savol
                            </span>
                        </div>
                        <div class="flex items-center gap-2" style="color:#0f3460">
                            <span class="text-xs">
                                UZ: {{ subject.questions_uz_count }} |
                                RU: {{ subject.questions_ru_count }}
                            </span>
                            <Icon icon="mdi:arrow-right" class="w-4 h-4" />
                        </div>
                    </Link>

                    <!-- Amallar -->
                    <div class="flex items-center justify-end gap-3">
                        <Link
                            :href="route('admin.subjects.edit', subject.id)"
                            class="text-xs font-medium text-amber-600 hover:text-amber-800 flex items-center gap-1"
                        >
                            <Icon icon="mdi:pencil-outline" class="w-3.5 h-3.5" />
                            Tahrir
                        </Link>
                        <button
                            @click="confirmDelete(subject)"
                            class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1"
                        >
                            <Icon icon="mdi:delete-outline" class="w-3.5 h-3.5" />
                            O'chirish
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete modal -->
        <div
            v-if="deleteTarget"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background: rgba(0,0,0,0.5)"
            @click.self="deleteTarget = null"
        >
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <Icon icon="mdi:delete-outline" class="w-6 h-6 text-red-500" />
                </div>
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Fanni o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget?.name_uz }}</strong> fanini o'chirishni tasdiqlaysizmi?
                </p>
                <div class="flex gap-3">
                    <button @click="deleteTarget = null" class="btn-secondary flex-1">Bekor qilish</button>
                    <button @click="deleteSubject" class="btn-danger flex-1">O'chirish</button>
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
    subjects: { type: Array, default: () => [] },
})

const deleteTarget = ref(null)

const confirmDelete = (subject) => {
    deleteTarget.value = subject
}

const deleteSubject = () => {
    router.delete(route('admin.subjects.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
    })
}
</script>

<style scoped>
.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    background: linear-gradient(135deg, #0f3460, #533483);
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-primary:hover { box-shadow: 0 6px 20px rgba(15,52,96,0.3); }

.btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
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

.btn-danger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    background: #ef4444;
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
}
.btn-danger:hover { background: #dc2626; }
</style>
