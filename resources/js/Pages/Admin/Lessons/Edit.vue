<template>
    <AppLayout :title="pageTitle">
        <div class="max-w-2xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="route('admin.courses.show', course.id)"
                      class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ pageTitle }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ course.title_uz }} — {{ module.title_uz }}</p>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <!-- Nomlar -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Dars nomi <span class="req">*</span></label>
                        <input v-model="form.title_uz" type="text" placeholder="Masalan: 1-dars: Kirish"
                               class="field-input" :class="form.errors.title_uz ? 'field-error' : ''">
                        <p v-if="form.errors.title_uz" class="err">{{ form.errors.title_uz }}</p>
                    </div>
                    <div>
                        <label class="field-label">Nomi (Rus)</label>
                        <input v-model="form.title_ru" type="text" placeholder="Ixtiyoriy" class="field-input">
                    </div>
                </div>

                <div>
                    <label class="field-label">Tavsif</label>
                    <textarea v-model="form.description" rows="3" class="field-input"></textarea>
                </div>

                <!-- Turi -->
                <div>
                    <label class="field-label">Dars turi <span class="req">*</span></label>
                    <select v-model="form.type" class="field-input" :class="form.errors.type ? 'field-error' : ''">
                        <option value="video">Video dars</option>
                        <option value="pdf">Fayl (PDF/hujjat)</option>
                        <option value="text">Matnli dars</option>
                        <option value="quiz" disabled>🔒 Test (tez orada)</option>
                        <option value="assignment" disabled>🔒 Topshiriq (tez orada)</option>
                        <option value="scorm">SCORM / xAPI paket</option>
                    </select>
                    <p v-if="form.errors.type" class="err">{{ form.errors.type }}</p>
                </div>

                <!-- SCORM / xAPI -->
                <div v-if="form.type === 'scorm'" class="space-y-3 p-4 rounded-xl bg-gray-50 border border-gray-100">
                    <div v-if="lesson.scormPackage" class="flex items-center gap-2 text-xs text-gray-500 bg-white rounded-lg px-3 py-2 border border-gray-100">
                        <Icon icon="mdi:check-circle-outline" class="w-4 h-4 text-green-600 flex-shrink-0" />
                        <span class="flex-1">
                            Paket mavjud: {{ lesson.scormPackage.title }}
                            ({{ { scorm12: 'SCORM 1.2', scorm2004: 'SCORM 2004', xapi: 'xAPI (Tin Can)' }[lesson.scormPackage.version] || lesson.scormPackage.version }}).
                            Yangisini tanlasangiz, eskisi almashtiriladi.
                        </span>
                    </div>
                    <div>
                        <label class="field-label">{{ lesson.scormPackage ? "Yangi paket fayli (.zip)" : "Paket fayli (.zip)" }} <span v-if="!lesson.scormPackage" class="req">*</span></label>
                        <input type="file" accept=".zip,application/zip" class="field-input"
                               :class="form.errors.scorm_file ? 'field-error' : ''"
                               @change="form.scorm_file = $event.target.files[0]">
                        <p class="hint">SCORM 1.2, SCORM 2004 yoki xAPI (Tin Can) paketini ZIP arxiv holida yuklang (imsmanifest.xml yoki tincan.xml ichida bo'lishi kerak). Bo'sh qoldirsangiz, mavjud paket o'zgarmaydi. Maksimum 500 MB.</p>
                        <p v-if="form.errors.scorm_file" class="err">{{ form.errors.scorm_file }}</p>
                    </div>
                </div>

                <!-- Video -->
                <div v-if="form.type === 'video'" class="space-y-4 p-4 rounded-xl bg-gray-50 border border-gray-100">
                    <div v-if="lesson.video" class="flex items-center gap-2 text-xs text-gray-500 bg-white rounded-lg px-3 py-2 border border-gray-100">
                        <Icon icon="mdi:check-circle-outline" class="w-4 h-4 text-green-600" />
                        Video mavjud ({{ lesson.video.source === 'upload' ? 'yuklangan fayl' : lesson.video.source }}).
                        Yangisini tanlasangiz, eskisi almashtiriladi.
                    </div>
                    <div>
                        <label class="field-label">Video manbasi</label>
                        <select v-model="form.video_source" class="field-input">
                            <option value="upload">Fayl yuklash</option>
                            <option value="youtube">YouTube havolasi</option>
                            <option value="vimeo">Vimeo havolasi</option>
                        </select>
                    </div>
                    <div v-if="form.video_source === 'upload'">
                        <label class="field-label">Yangi video fayl</label>
                        <input type="file" accept="video/mp4,video/webm,video/ogg" class="field-input"
                               @change="form.video_file = $event.target.files[0]">
                        <p class="hint">Bo'sh qoldirsangiz, mavjud video o'zgarmaydi</p>
                        <p v-if="form.errors.video_file" class="err">{{ form.errors.video_file }}</p>
                    </div>
                    <div v-else>
                        <label class="field-label">Video havolasi</label>
                        <input v-model="form.video_url" type="text" placeholder="https://youtube.com/watch?v=..."
                               class="field-input" :class="form.errors.video_url ? 'field-error' : ''">
                        <p v-if="form.errors.video_url" class="err">{{ form.errors.video_url }}</p>
                    </div>
                </div>

                <!-- Matn -->
                <div v-if="form.type === 'text'">
                    <label class="field-label">Dars matni</label>
                    <textarea v-model="form.content" rows="10" placeholder="Dars matnini shu yerga yozing"
                              class="field-input" :class="form.errors.content ? 'field-error' : ''"></textarea>
                    <p v-if="form.errors.content" class="err">{{ form.errors.content }}</p>
                </div>

                <!-- Mavjud fayllar -->
                <div v-if="lesson.attachments?.length">
                    <label class="field-label">Mavjud fayllar</label>
                    <div class="space-y-2">
                        <div v-for="att in lesson.attachments" :key="att.id"
                             class="flex items-center justify-between px-3 py-2 rounded-xl bg-gray-50 border border-gray-100 text-sm">
                            <a :href="att.url" target="_blank" class="flex items-center gap-2 text-gray-700 hover:underline truncate">
                                <Icon icon="mdi:file-outline" class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                {{ att.title }}
                            </a>
                            <button type="button" @click="deleteAttachment(att)" class="text-red-500 hover:text-red-700 flex-shrink-0 ml-2">
                                <Icon icon="mdi:close" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Yangi fayllar -->
                <div v-if="form.type !== 'scorm'">
                    <label class="field-label">
                        {{ form.type === 'pdf' ? "Yangi fayl qo'shish" : "Qo'shimcha fayl qo'shish" }}
                    </label>
                    <input type="file" multiple class="field-input" @change="form.files = Array.from($event.target.files)">
                    <p class="hint">Bu yerda tanlangan fayllar mavjudlariga qo'shiladi (eskilarini almashtirmaydi)</p>
                    <p v-if="form.errors.files" class="err">{{ form.errors.files }}</p>
                </div>

                <!-- Qo'shimcha sozlamalar -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Davomiyligi (daqiqa)</label>
                        <input v-model.number="form.duration" type="number" min="0" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Tartib raqami</label>
                        <input v-model.number="form.order" type="number" min="0" class="field-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="flex items-center gap-2 text-sm text-gray-700 p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <input type="checkbox" v-model="form.is_free" class="rounded">
                        Bepul namuna sifatida ko'rsatish
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700 p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <input type="checkbox" v-model="form.is_published" class="rounded">
                        Nashr qilingan
                    </label>
                </div>
            </div>

            <!-- Tugmalar -->
            <div class="flex gap-3">
                <Link :href="route('admin.courses.show', course.id)"
                      class="btn-secondary flex-1 flex items-center justify-center gap-2">
                    <Icon icon="mdi:close" class="w-4 h-4" />
                    Bekor qilish
                </Link>
                <button type="button" @click="submit" :disabled="form.processing" class="btn-primary flex-1">
                    <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                    <Icon v-else icon="mdi:content-save-outline" class="w-4 h-4" />
                    {{ form.processing ? 'Saqlanmoqda...' : 'Saqlash' }}
                </button>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    course: { type: Object, required: true },
    module: { type: Object, required: true },
    lesson: { type: Object, required: true },
})

const pageTitle = computed(() => 'Darsni tahrirlash')

const form = useForm({
    title_uz: props.lesson.title_uz || '',
    title_ru: props.lesson.title_ru || '',
    description: props.lesson.description || '',
    type: props.lesson.type || 'video',
    order: props.lesson.order ?? 0,
    duration: props.lesson.duration ?? 0,
    is_free: props.lesson.is_free ?? false,
    is_published: props.lesson.is_published ?? true,
    content: props.lesson.content || '',
    video_source: props.lesson.video?.source || 'upload',
    video_url: (props.lesson.video && ['youtube', 'vimeo'].includes(props.lesson.video.source)) ? props.lesson.video.url : '',
    video_file: null,
    files: [],
    scorm_file: null,
})

const submit = () => {
    form.put(route('admin.courses.lessons.update', [props.course.id, props.lesson.id]))
}

const deleteAttachment = (att) => {
    if (! confirm(`"${att.title}" faylini o'chirasizmi?`)) return
    router.delete(route('admin.courses.lessons.attachments.destroy', [props.course.id, props.lesson.id, att.id]), {
        preserveScroll: true,
    })
}
</script>

<style scoped>
.field-label {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.375rem;
}
.req { color: #ef4444; }
.field-input {
    width: 100%;
    padding: 0.6rem 0.875rem;
    border-radius: 0.625rem;
    border: 1.5px solid #e5e7eb;
    font-size: 0.875rem;
    color: #111827;
    background: #fafafa;
    outline: none;
    transition: border-color 0.2s;
    font-family: inherit;
}
.field-input:focus { border-color: #0f3460; background: white; }
.field-error { border-color: #f87171 !important; background: #fef2f2 !important; }
.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }
.hint { color: #9ca3af; font-size: 0.7rem; margin-top: 0.25rem; display: block; }

.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.5rem;
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
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    background: white;
    color: #374151;
    font-size: 0.875rem;
    font-weight: 600;
    border: 1.5px solid #e5e7eb;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}
.btn-secondary:hover { background: #f9fafb; }
</style>
