<template>
    <AppLayout :title="course.title_uz">
        <div class="max-w-4xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.courses.index')"
                          class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
                        <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            {{ course.title_uz }}
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="statusClass(course.status)">
                                {{ statusLabel(course.status) }}
                            </span>
                        </h1>
                        <p class="text-sm text-gray-500 mt-0.5">{{ course.category?.name_uz || 'Kategoriyasiz' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('admin.courses.enrollments.index', course.id)" class="btn-secondary">
                        <Icon icon="mdi:account-group-outline" class="w-4 h-4" />
                        O'quvchilarni boshqarish
                    </Link>
                    <Link :href="route('admin.courses.edit', course.id)" class="btn-secondary">
                        <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                        Tahrirlash
                    </Link>
                </div>
            </div>

            <!-- Ma'lumot kartasi -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 flex gap-5"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="w-32 h-24 rounded-xl bg-gray-100 overflow-hidden flex items-center justify-center flex-shrink-0">
                    <img v-if="course.thumbnail_url" :src="course.thumbnail_url" class="w-full h-full object-cover" alt="">
                    <Icon v-else icon="mdi:school-outline" class="w-8 h-8 text-gray-400" />
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 flex-1">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Narxi</p>
                        <p class="text-sm font-semibold text-gray-900">{{ formatPrice(course.discount_price || course.price) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Daraja</p>
                        <p class="text-sm font-semibold text-gray-900">{{ levelLabel(course.level) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Davomiyligi</p>
                        <p class="text-sm font-semibold text-gray-900">{{ course.duration_hours }} soat</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Talabalar</p>
                        <p class="text-sm font-semibold text-gray-900">{{ course.enrollments_count }} ta</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">O'qituvchilar</p>
                        <p class="text-sm font-semibold text-gray-900">{{ course.instructors?.map(i => i.full_name).join(', ') || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Sertifikat</p>
                        <p class="text-sm font-semibold text-gray-900">{{ course.has_certificate ? 'Ha' : "Yo'q" }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Yo'nalishlar</p>
                        <p class="text-sm font-semibold text-gray-900">{{ course.directions?.map(d => d.name_uz).join(', ') || 'Cheklovsiz' }}</p>
                    </div>
                </div>
            </div>

            <!-- Modul qo'shish -->
            <div class="flex items-center justify-between">
                <p class="text-base font-bold text-gray-900">O'quv dasturi (modullar va darslar)</p>
                <button @click="openModuleModal()" class="btn-primary">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Yangi modul
                </button>
            </div>

            <!-- Modullar -->
            <div v-if="!course.modules?.length"
                 class="bg-white rounded-2xl border border-gray-100 p-12 text-center text-gray-400"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <Icon icon="mdi:folder-outline" class="w-10 h-10 mx-auto mb-2 opacity-40" />
                <p class="text-sm">Hali modul qo'shilmagan</p>
            </div>

            <div v-for="module in course.modules" :key="module.id"
                 class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <!-- Modul header -->
                <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                    <div class="flex items-center gap-2">
                        <Icon icon="mdi:folder-outline" class="w-4 h-4 text-gray-400" />
                        <p class="text-sm font-bold text-gray-800">{{ module.title_uz }}</p>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold"
                              :class="module.is_published ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'">
                            {{ module.is_published ? 'Nashr qilingan' : 'Qoralama' }}
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <Link :href="route('admin.courses.lessons.create', [course.id, module.id])"
                              class="text-xs font-medium text-[#0f3460] hover:underline flex items-center gap-1">
                            <Icon icon="mdi:plus" class="w-3.5 h-3.5" />
                            Dars qo'shish
                        </Link>
                        <button @click="openModuleModal(module)" class="text-xs font-medium text-amber-600 hover:text-amber-800 flex items-center gap-1">
                            <Icon icon="mdi:pencil-outline" class="w-3.5 h-3.5" />
                            Tahrir
                        </button>
                        <button @click="confirmDeleteModule(module)" class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1">
                            <Icon icon="mdi:delete-outline" class="w-3.5 h-3.5" />
                            O'chirish
                        </button>
                    </div>
                </div>

                <!-- Darslar -->
                <div v-if="!module.lessons?.length" class="px-5 py-6 text-center text-sm text-gray-400">
                    Bu modulda hali dars yo'q
                </div>
                <div v-else class="divide-y divide-gray-50">
                    <div v-for="lesson in module.lessons" :key="lesson.id"
                         class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                 :style="`background: ${lessonTypeColor(lesson.type)}20; color: ${lessonTypeColor(lesson.type)}`">
                                <Icon :icon="lessonTypeIcon(lesson.type)" class="w-4 h-4" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ lesson.title_uz }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ lessonTypeLabel(lesson.type) }}
                                    <span v-if="lesson.duration"> · {{ lesson.duration }} daqiqa</span>
                                    <span v-if="lesson.is_free"> · Bepul namuna</span>
                                    <span v-if="!lesson.is_published"> · Qoralama</span>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <Link :href="route('admin.courses.lessons.edit', [course.id, lesson.id])"
                                  class="text-xs font-medium text-amber-600 hover:text-amber-800 flex items-center gap-1">
                                <Icon icon="mdi:pencil-outline" class="w-3.5 h-3.5" />
                                Tahrir
                            </Link>
                            <button @click="confirmDeleteLesson(lesson)" class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1">
                                <Icon icon="mdi:delete-outline" class="w-3.5 h-3.5" />
                                O'chirish
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modul modal (create/edit) -->
        <div v-if="moduleModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="moduleModalOpen = false">
            <div class="bg-white rounded-2xl w-full max-w-md p-6">
                <h3 class="text-base font-bold text-gray-900 mb-4">{{ editingModule ? 'Modulni tahrirlash' : 'Yangi modul' }}</h3>
                <div class="space-y-4">
                    <div>
                        <label class="field-label">Modul nomi <span class="req">*</span></label>
                        <input v-model="moduleForm.title_uz" type="text" placeholder="Masalan: 1-modul: Kirish"
                               class="field-input" :class="moduleForm.errors.title_uz ? 'field-error' : ''">
                        <p v-if="moduleForm.errors.title_uz" class="err">{{ moduleForm.errors.title_uz }}</p>
                    </div>
                    <div>
                        <label class="field-label">Tavsif</label>
                        <textarea v-model="moduleForm.description" rows="3" class="field-input"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">Tartib raqami</label>
                            <input v-model.number="moduleForm.order" type="number" min="0" class="field-input">
                        </div>
                        <div class="flex items-end pb-1.5">
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" v-model="moduleForm.is_published" class="rounded">
                                Nashr qilingan
                            </label>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button @click="moduleModalOpen = false" class="btn-secondary flex-1">Bekor qilish</button>
                    <button @click="submitModule" :disabled="moduleForm.processing" class="btn-primary flex-1">
                        {{ moduleForm.processing ? 'Saqlanmoqda...' : 'Saqlash' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete modal -->
        <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="deleteTarget = null">
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <Icon icon="mdi:delete-outline" class="w-6 h-6 text-red-500" />
                </div>
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">
                    {{ deleteTarget.kind === 'module' ? "Modulni o'chirish" : "Darsni o'chirish" }}
                </h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget.title_uz }}</strong> {{ deleteTarget.kind === 'module' ? 'modulini' : 'darsini' }} o'chirasizmi?
                </p>
                <div class="flex gap-3">
                    <button @click="deleteTarget = null" class="btn-secondary flex-1">Bekor qilish</button>
                    <button @click="submitDelete" class="btn-danger flex-1">O'chirish</button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    course: { type: Object, required: true },
})

const statusLabel = (v) => ({ draft: 'Qoralama', published: 'Nashr qilingan', archived: 'Arxivlangan' }[v] || v)
const statusClass = (v) => ({
    draft:     'bg-gray-100 text-gray-500',
    published: 'bg-green-50 text-green-700',
    archived:  'bg-amber-50 text-amber-700',
}[v] || 'bg-gray-100 text-gray-500')
const levelLabel = (v) => ({ beginner: "Boshlang'ich", intermediate: "O'rta", advanced: 'Yuqori', expert: 'Ekspert' }[v] || v)
const formatPrice = (v) => Number(v) > 0 ? new Intl.NumberFormat('uz-UZ').format(v) + " so'm" : 'Bepul'

const lessonTypeLabel = (v) => ({ video: 'Video', pdf: 'Fayl/PDF', text: 'Matn', quiz: 'Test', assignment: 'Topshiriq', scorm: 'SCORM' }[v] || v)
const lessonTypeIcon  = (v) => ({ video: 'mdi:play-circle-outline', pdf: 'mdi:file-pdf-box', text: 'mdi:text-box-outline', quiz: 'mdi:help-circle-outline', assignment: 'mdi:clipboard-text-outline', scorm: 'mdi:package-variant-closed' }[v] || 'mdi:file-outline')
const lessonTypeColor = (v) => ({ video: '#0f3460', pdf: '#dc2626', text: '#16a34a', quiz: '#7c3aed', assignment: '#d97706', scorm: '#6b7280' }[v] || '#6b7280')

// --- Modul modal ---
const moduleModalOpen = ref(false)
const editingModule = ref(null)
const moduleForm = useForm({
    title_uz: '', title_ru: '', description: '', order: 0, is_published: true,
})

const openModuleModal = (module = null) => {
    editingModule.value = module
    moduleForm.clearErrors()
    moduleForm.title_uz     = module?.title_uz     || ''
    moduleForm.title_ru     = module?.title_ru     || ''
    moduleForm.description  = module?.description  || ''
    moduleForm.order        = module?.order        ?? (props.course.modules?.length ?? 0)
    moduleForm.is_published = module?.is_published ?? true
    moduleModalOpen.value = true
}

const submitModule = () => {
    const opts = { preserveScroll: true, onSuccess: () => { moduleModalOpen.value = false } }
    if (editingModule.value) {
        moduleForm.put(route('admin.courses.modules.update', [props.course.id, editingModule.value.id]), opts)
    } else {
        moduleForm.post(route('admin.courses.modules.store', props.course.id), opts)
    }
}

// --- O'chirish modali (modul yoki dars) ---
const deleteTarget = ref(null)
const confirmDeleteModule = (module) => { deleteTarget.value = { ...module, kind: 'module' } }
const confirmDeleteLesson = (lesson) => { deleteTarget.value = { ...lesson, kind: 'lesson' } }

const submitDelete = () => {
    const target = deleteTarget.value
    const url = target.kind === 'module'
        ? route('admin.courses.modules.destroy', [props.course.id, target.id])
        : route('admin.courses.lessons.destroy', [props.course.id, target.id])

    router.delete(url, {
        preserveScroll: true,
        onSuccess: () => { deleteTarget.value = null },
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

.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
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
