<template>
    <AppLayout :title="enrollment.course?.title_uz">
        <div class="max-w-3xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="route('admin.my-courses.index')"
                      class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ enrollment.course?.title_uz }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ enrollment.course?.category?.name_uz || 'Kategoriyasiz' }}</p>
                </div>
            </div>

            <!-- Progress kartasi -->
            <div class="bg-white rounded-2xl border border-gray-100 p-5"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-semibold text-gray-900">Umumiy progress</p>
                    <span class="text-sm font-bold" style="color:#0f3460">{{ enrollment.progress || 0 }}%</span>
                </div>
                <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                    <div class="h-full rounded-full" :style="`width:${enrollment.progress || 0}%; background: linear-gradient(135deg,#0f3460,#533483)`" />
                </div>
                <p v-if="enrollment.status === 'completed'" class="text-xs text-green-600 font-semibold mt-2 flex items-center gap-1">
                    <Icon icon="mdi:certificate-outline" class="w-4 h-4" />
                    Tabriklaymiz, kursni tugatdingiz!
                </p>
            </div>

            <!-- O'quv dasturi -->
            <div>
                <p class="text-base font-bold text-gray-900 mb-3">O'quv dasturi</p>

                <div v-if="!enrollment.course?.modules?.length"
                     class="bg-white rounded-2xl border border-gray-100 p-10 text-center text-gray-400"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <Icon icon="mdi:folder-outline" class="w-10 h-10 mx-auto mb-2 opacity-40" />
                    <p class="text-sm">Bu kursda hali modul yo'q</p>
                </div>

                <div v-else class="space-y-3">
                    <div v-for="(module, mi) in enrollment.course.modules" :key="module.id"
                         class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                                  style="background: linear-gradient(135deg,#0f3460,#533483)">{{ mi + 1 }}</span>
                            <p class="text-sm font-semibold text-gray-900">{{ module.title_uz }}</p>
                        </div>
                        <div class="divide-y divide-gray-50">
                            <Link v-for="lesson in module.lessons ?? []" :key="lesson.id"
                                  :href="route('admin.my-courses.lesson', [enrollment.course_id, lesson.id])"
                                  class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition-colors">
                                <Icon v-if="isDone(lesson.id)" icon="mdi:check-circle" class="w-5 h-5 flex-shrink-0" style="color:#16a34a" />
                                <Icon v-else :icon="lessonTypeIcon(lesson.type)" class="w-5 h-5 flex-shrink-0 text-gray-400" />
                                <span class="text-sm text-gray-800 flex-1">{{ lesson.title_uz }}</span>
                                <span v-if="lesson.duration" class="text-xs text-gray-400">{{ lesson.duration }} daq.</span>
                                <Icon icon="mdi:chevron-right" class="w-4 h-4 text-gray-300" />
                            </Link>
                            <p v-if="!module.lessons?.length" class="px-5 py-3 text-xs text-gray-400">Darslar yo'q</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    enrollment:         { type: Object, required: true },
    completedLessonIds: { type: Array,  default: () => [] },
})

const isDone = (lessonId) => props.completedLessonIds.includes(lessonId)

const lessonTypeIcon = (v) => ({
    video: 'mdi:play-circle-outline',
    pdf: 'mdi:file-pdf-box',
    text: 'mdi:text-box-outline',
    quiz: 'mdi:help-circle-outline',
    assignment: 'mdi:clipboard-text-outline',
    scorm: 'mdi:package-variant-closed',
}[v] || 'mdi:file-outline')
</script>
