<template>
    <AppLayout title="Fanlar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Fanlar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Test savollari uchun fanlar ro'yxati</p>
                </div>
                <Link :href="route('admin.subjects.create')" class="btn-brand">
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
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 bg-brand-600">
                                <Icon icon="mdi:book-open-outline" class="w-5 h-5 text-white" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ subject.name_uz }}</p>
                                <p class="text-xs text-gray-400">{{ subject.name_ru }}</p>
                            </div>
                        </div>
                        <span class="badge-pill" :class="subject.is_active ? 'badge-success' : 'badge-neutral'">
                            {{ subject.is_active ? 'Faol' : 'Nofaol' }}
                        </span>
                    </div>

                    <!-- Savollar soni -->
                    <Link
                        :href="route('admin.subjects.questions.index', subject.id)"
                        class="flex items-center justify-between p-3 rounded-xl mb-3 transition-all hover:opacity-80 bg-brand-50"
                    >
                        <div class="flex items-center gap-2">
                            <Icon icon="mdi:help-circle-outline" class="w-4 h-4 text-brand-600" />
                            <span class="text-sm font-semibold text-brand-600">
                                {{ subject.questions_count }} ta savol
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-brand-600">
                            <span class="text-xs">
                                UZ: {{ subject.questions_uz_count }} |
                                RU: {{ subject.questions_ru_count }}
                            </span>
                            <Icon icon="mdi:arrow-right" class="w-4 h-4" />
                        </div>
                    </Link>

                    <!-- Amallar -->
                    <div class="flex items-center justify-end gap-1">
                        <Link :href="route('admin.subjects.edit', subject.id)" title="Tahrirlash" class="btn-ghost-icon">
                            <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                        </Link>
                        <button @click="confirmDelete(subject)" title="O'chirish" class="btn-ghost-icon danger">
                            <Icon icon="mdi:delete-outline" class="w-4 h-4" />
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
                    <button @click="deleteTarget = null" class="btn-neutral flex-1 justify-center">Bekor qilish</button>
                    <button @click="deleteSubject" class="btn-danger-pill flex-1">O'chirish</button>
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
