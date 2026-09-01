<template>
    <AppLayout :title="lesson.title_uz">
        <div class="max-w-3xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="route('admin.my-courses.show', enrollment.course_id)"
                      class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <div class="min-w-0">
                    <p class="text-xs text-gray-400 truncate">{{ enrollment.course?.title_uz }}</p>
                    <h1 class="text-lg font-bold text-gray-900 truncate">{{ lesson.title_uz }}</h1>
                </div>
                <span v-if="isDone" class="ml-auto inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 flex-shrink-0">
                    <Icon icon="mdi:check-circle" class="w-3.5 h-3.5" />
                    Tugatilgan
                </span>
            </div>

            <!-- Kontent -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <!-- Video -->
                <div v-if="lesson.type === 'video'">
                    <video v-if="lesson.video?.video_url" :src="lesson.video.video_url" controls class="w-full aspect-video bg-black" />
                    <div v-else class="aspect-video flex items-center justify-center bg-gray-50 text-gray-400">
                        <Icon icon="mdi:video-off-outline" class="w-10 h-10" />
                    </div>
                </div>

                <!-- PDF / fayl -->
                <div v-else-if="lesson.type === 'pdf'" class="p-6">
                    <div v-if="lesson.attachments?.length" class="space-y-2">
                        <a v-for="att in lesson.attachments" :key="att.id" :href="att.url" target="_blank"
                           class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 transition">
                            <Icon icon="mdi:file-pdf-box" class="w-6 h-6 text-red-500 flex-shrink-0" />
                            <span class="text-sm font-medium text-gray-800 flex-1 truncate">{{ att.title }}</span>
                            <Icon icon="mdi:download-outline" class="w-4 h-4 text-gray-400" />
                        </a>
                    </div>
                    <p v-else class="text-sm text-gray-400 text-center py-6">Fayl biriktirilmagan</p>
                </div>

                <!-- Matn -->
                <div v-else-if="lesson.type === 'text'" class="p-6 prose prose-sm max-w-none" v-html="lesson.content" />

                <!-- Quiz / Topshiriq / SCORM — hozircha alohida modul sifatida ishlab chiqilmagan -->
                <div v-else class="p-10 text-center text-gray-400">
                    <Icon :icon="lessonTypeIcon(lesson.type)" class="w-10 h-10 mx-auto mb-2 opacity-40" />
                    <p class="text-sm">{{ lessonTypeLabel(lesson.type) }} — tez kunda</p>
                </div>

                <!-- Tavsif -->
                <div v-if="lesson.description" class="px-6 py-4 border-t border-gray-100">
                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ lesson.description }}</p>
                </div>

                <!-- Qo'shimcha fayllar (video/matn darslarida ham bo'lishi mumkin) -->
                <div v-if="lesson.type !== 'pdf' && lesson.attachments?.length" class="px-6 py-4 border-t border-gray-100 space-y-2">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Qo'shimcha materiallar</p>
                    <a v-for="att in lesson.attachments" :key="att.id" :href="att.url" target="_blank"
                       class="flex items-center gap-3 p-2.5 rounded-xl border border-gray-100 hover:bg-gray-50 transition">
                        <Icon icon="mdi:paperclip" class="w-4 h-4 text-gray-400 flex-shrink-0" />
                        <span class="text-sm text-gray-700 flex-1 truncate">{{ att.title }}</span>
                        <Icon icon="mdi:download-outline" class="w-4 h-4 text-gray-400" />
                    </a>
                </div>
            </div>

            <!-- Tugatish tugmasi -->
            <button v-if="!isDone" @click="markComplete" :disabled="completing" class="btn-primary w-full">
                <Icon v-if="completing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                <Icon v-else icon="mdi:check-circle-outline" class="w-4 h-4" />
                {{ completing ? 'Belgilanmoqda...' : 'Darsni tugatdim' }}
            </button>

            <!-- Oldingi / Keyingi -->
            <div class="flex items-center gap-3">
                <Link v-if="prevLesson"
                      :href="route('admin.my-courses.lesson', [enrollment.course_id, prevLesson.id])"
                      class="btn-secondary flex-1 flex items-center gap-2 justify-start">
                    <Icon icon="mdi:chevron-left" class="w-4 h-4" />
                    <span class="truncate">{{ prevLesson.title_uz }}</span>
                </Link>
                <div v-else class="flex-1" />

                <Link v-if="nextLesson"
                      :href="route('admin.my-courses.lesson', [enrollment.course_id, nextLesson.id])"
                      class="btn-secondary flex-1 flex items-center gap-2 justify-end">
                    <span class="truncate">{{ nextLesson.title_uz }}</span>
                    <Icon icon="mdi:chevron-right" class="w-4 h-4" />
                </Link>
                <div v-else class="flex-1" />
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { useToast } from 'vue-toastification'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    enrollment:         { type: Object, required: true },
    lesson:              { type: Object, required: true },
    completedLessonIds: { type: Array,  default: () => [] },
    prevLesson:          { type: Object, default: null },
    nextLesson:          { type: Object, default: null },
})

const toast = useToast()
const completing = ref(false)

const isDone = computed(() => props.completedLessonIds.includes(props.lesson.id))

const lessonTypeLabel = (v) => ({ video: 'Video', pdf: 'Fayl/PDF', text: 'Matn', quiz: 'Test', assignment: 'Topshiriq', scorm: 'SCORM' }[v] || v)
const lessonTypeIcon = (v) => ({
    video: 'mdi:play-circle-outline',
    pdf: 'mdi:file-pdf-box',
    text: 'mdi:text-box-outline',
    quiz: 'mdi:help-circle-outline',
    assignment: 'mdi:clipboard-text-outline',
    scorm: 'mdi:package-variant-closed',
}[v] || 'mdi:file-outline')

const markComplete = () => {
    completing.value = true
    router.post(route('admin.my-courses.lessons.complete', [props.enrollment.course_id, props.lesson.id]), {}, {
        preserveScroll: true,
        onSuccess: (page) => {
            const flash = page.props.flash
            if (flash?.success) toast.success(flash.success)
        },
        onError: () => toast.error("Xatolik yuz berdi, qayta urinib ko'ring."),
        onFinish: () => { completing.value = false },
    })
}
</script>

<style scoped>
.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
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
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    background: white;
    color: #374151;
    font-size: 0.8rem;
    font-weight: 600;
    border: 1.5px solid #e5e7eb;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    max-width: 45%;
}
.btn-secondary:hover { background: #f9fafb; }
</style>
