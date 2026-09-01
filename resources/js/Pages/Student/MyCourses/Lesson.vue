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
                    <!-- YouTube/Vimeo — havola shunchaki veb-sahifa manzili, video fayl
                         emas, shuning uchun <video> emas, o'sha saytning o'z pleyeri
                         (iframe) orqali ko'rsatiladi -->
                    <iframe v-if="embedUrl" :src="embedUrl" class="w-full aspect-video" style="border:0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen />
                    <video v-else-if="lesson.video?.video_url" :src="lesson.video.video_url" controls class="w-full aspect-video bg-black" />
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

                <!-- SCORM 1.2 / SCORM 2004 / xAPI -->
                <div v-else-if="lesson.type === 'scorm'">
                    <div v-if="scormError" class="p-6 text-sm text-red-600 flex items-center gap-2">
                        <Icon icon="mdi:alert-circle-outline" class="w-5 h-5 flex-shrink-0" />
                        {{ scormError }}
                    </div>
                    <iframe v-else-if="scormFrameSrc" ref="scormFrameEl" :src="scormFrameSrc"
                            class="w-full" style="height: 70vh; border: 0" allowfullscreen />
                    <div v-else class="flex items-center justify-center text-gray-400" style="height: 40vh">
                        <Icon icon="mdi:loading" class="w-8 h-8 animate-spin" />
                    </div>
                    <p class="px-6 py-2 text-xs text-gray-400 border-t border-gray-100">
                        {{ lesson.scormPackage?.version === 'xapi' ? 'xAPI (Tin Can) paketi' : (lesson.scormPackage?.version === 'scorm2004' ? 'SCORM 2004 paketi' : 'SCORM 1.2 paketi') }}
                        — natija avtomatik saqlanadi.
                    </p>
                </div>

                <!-- Quiz / Topshiriq — hozircha alohida modul sifatida ishlab chiqilmagan -->
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
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { useToast } from 'vue-toastification'
import AppLayout from '@/Layouts/AppLayout.vue'
import { createScormRuntime } from '@/scormApi.js'

const props = defineProps({
    enrollment:         { type: Object, required: true },
    lesson:              { type: Object, required: true },
    completedLessonIds: { type: Array,  default: () => [] },
    scormAttempt:        { type: Object, default: null },
    xapiLaunch:          { type: Object, default: null },
    prevLesson:          { type: Object, default: null },
    nextLesson:          { type: Object, default: null },
})

const page = usePage()
const toast = useToast()
const completing = ref(false)

const isDone = computed(() => props.completedLessonIds.includes(props.lesson.id))

// YouTube/Vimeo havolasini o'zining iframe-pleyer manziliga aylantiradi.
// Masalan "https://www.youtube.com/watch?v=XXXX" yoki "https://youtu.be/XXXX"
// -> "https://www.youtube.com/embed/XXXX". Bunday havolani to'g'ridan-to'g'ri
// <video src="..."> ga berib bo'lmaydi — u video fayl emas, veb-sahifa.
const embedUrl = computed(() => {
    const video = props.lesson.video
    if (!video || !['youtube', 'vimeo'].includes(video.source) || !video.url) return null

    if (video.source === 'youtube') {
        const match = video.url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([a-zA-Z0-9_-]{6,})/)
        return match ? `https://www.youtube.com/embed/${match[1]}` : null
    }

    // vimeo
    const match = video.url.match(/vimeo\.com\/(?:video\/)?(\d+)/)
    return match ? `https://player.vimeo.com/video/${match[1]}` : null
})

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

// --- SCORM 1.2 / SCORM 2004 / xAPI ---
//
// SCORM kontenti API obyektini window.parent zanjiri bo'ylab qidiradi,
// shuning uchun u iframe ATA oynasiga (shu sahifaning o'ziga), iframe
// src o'rnatilishidan OLDIN joylashtiriladi. xAPI uchun esa alohida API
// shart emas — paket ichidagi kutubxona to'g'ridan-to'g'ri bizning
// statement endpointimizga (query-parametrlar orqali uzatilgan) HTTP
// so'rov yuboradi (ADL "Launch" konventsiyasi).
const scormFrameSrc = ref(null)
const scormError = ref(null)

const buildXapiLaunchUrl = (baseUrl, launch) => {
    const params = new URLSearchParams()
    params.set('endpoint', launch.endpoint)
    params.set('auth', 'Basic ' + btoa('anonymous:anonymous'))
    params.set('actor', JSON.stringify(launch.actor))
    params.set('registration', launch.registration)
    if (launch.activityId) params.set('activity_id', launch.activityId)
    const sep = baseUrl.includes('?') ? '&' : '?'
    return baseUrl + sep + params.toString()
}

onMounted(() => {
    if (props.lesson.type !== 'scorm') return

    const pkg = props.lesson.scormPackage
    if (!pkg || !pkg.full_launch_url) {
        scormError.value = "Paket topilmadi yoki fayllari yo'q. Administratorga xabar bering."
        return
    }

    if (pkg.version === 'xapi') {
        if (!props.xapiLaunch) {
            scormError.value = "xAPI ishga tushirish ma'lumotlari topilmadi."
            return
        }
        scormFrameSrc.value = buildXapiLaunchUrl(pkg.full_launch_url, props.xapiLaunch)
        return
    }

    const runtime = createScormRuntime({
        version: pkg.version, // 'scorm12' | 'scorm2004'
        initial: props.scormAttempt || {},
        student: {
            id: page.props.auth?.user?.id ?? '',
            name: page.props.auth?.user?.full_name || '',
        },
        attemptId: props.scormAttempt?.attempt_id
            || (window.crypto?.randomUUID ? window.crypto.randomUUID() : String(Date.now())),
        onCommit: (payload) => window.axios.post(
            route('admin.my-courses.lessons.scorm.commit', [props.enrollment.course_id, props.lesson.id]),
            payload
        ).then(() => {
            // Tugallandi/o'tdi deb belgilansa — "Tugatilgan" holatini va
            // progressni yangilash uchun shu propni qayta so'raymiz (iframe
            // qayta yuklanmaydi, faqat shu maydon yangilanadi).
            if (payload.completion_status === 'completed' || payload.success_status === 'passed') {
                router.reload({ only: ['completedLessonIds'] })
            }
        }).catch(() => {
            toast.error("SCORM natijasini saqlashda xatolik yuz berdi.")
        }),
    })

    if (pkg.version === 'scorm2004') {
        window.API_1484_11 = runtime
    } else {
        window.API = runtime
    }

    scormFrameSrc.value = pkg.full_launch_url
})

onBeforeUnmount(() => {
    delete window.API
    delete window.API_1484_11
})
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
