<template>
    <AppLayout :title="`${subject.name_uz} — Savollar`">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('admin.subjects.index')"
                        class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition"
                    >
                        <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ subject.name_uz }}</h1>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ subject.name_ru }} • Jami: {{ questions.total }} ta savol
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Namuna shablon -->
                    <a
                        :href="route('admin.subjects.questions.template', subject.id)"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition"
                    >
                        <Icon icon="mdi:download-outline" class="w-4 h-4" />
                        Namuna
                    </a>

                    <!-- Import -->
                    <button
                        @click="importModal = true"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl border text-sm font-medium transition"
                        style="border-color:#0f3460; color:#0f3460"
                    >
                        <Icon icon="mdi:upload-outline" class="w-4 h-4" />
                        Fayldan yuklash
                    </button>

                    <!-- Yangi savol -->
                    <Link
                        :href="route('admin.subjects.questions.create', subject.id)"
                        class="btn-primary"
                    >
                        <Icon icon="mdi:plus" class="w-4 h-4" />
                        Yangi savol
                    </Link>
                </div>
            </div>

            <!-- Til filterlari -->
            <div class="flex items-center gap-2">
                <Link
                    :href="route('admin.subjects.questions.index', subject.id)"
                    class="px-4 py-2 text-sm font-medium rounded-xl border transition-all"
                    :class="!activeLang
                        ? 'border-[#0f3460] text-[#0f3460] bg-blue-50'
                        : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                >
                    Barchasi ({{ questions.total }})
                </Link>
                <Link
                    :href="route('admin.subjects.questions.index', subject.id) + '?lang=uz'"
                    class="px-4 py-2 text-sm font-medium rounded-xl border transition-all"
                    :class="activeLang === 'uz'
                        ? 'border-[#0f3460] text-[#0f3460] bg-blue-50'
                        : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                >
                    O'zbek tili ({{ uzCount }})
                </Link>
                <Link
                    :href="route('admin.subjects.questions.index', subject.id) + '?lang=ru'"
                    class="px-4 py-2 text-sm font-medium rounded-xl border transition-all"
                    :class="activeLang === 'ru'
                        ? 'border-purple-600 text-purple-600 bg-purple-50'
                        : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                >
                    Rus tili ({{ ruCount }})
                </Link>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider w-10">#</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Savol va variantlar</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">To'g'ri javob</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                    <tr v-if="!questions.data?.length">
                        <td colspan="5" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:help-circle-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Savollar yo'q</p>
                            <p class="text-xs mt-1">Yangi savol yarating yoki fayldan yuklang</p>
                        </td>
                    </tr>
                    <tr
                        v-for="(q, i) in questions.data"
                        :key="q.id"
                        class="hover:bg-gray-50 transition-colors"
                    >
                        <td class="px-4 py-4 text-xs text-gray-400 font-mono">
                            {{ (questions.current_page - 1) * questions.per_page + i + 1 }}
                        </td>
                        <td class="px-4 py-4">
                            <p class="text-sm font-medium text-gray-800 mb-2">{{ q.question }}</p>
                            <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="opt in ['a','b','c','d']"
                                        :key="opt"
                                        class="text-xs px-2.5 py-1 rounded-lg font-medium"
                                        :class="opt === q.correct_answer
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-gray-100 text-gray-500'"
                                    >
                                        {{ opt.toUpperCase() }}: {{ q['option_' + opt] }}
                                    </span>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700">
                                    <Icon icon="mdi:check-circle" class="w-3.5 h-3.5" />
                                    {{ q.correct_answer?.toUpperCase() }}
                                </span>
                        </td>
                        <td class="px-4 py-4">
                                <span
                                    class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                    :class="q.is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'"
                                >
                                    {{ q.is_active ? 'Faol' : 'Nofaol' }}
                                </span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <Link
                                    :href="route('admin.subjects.questions.edit', [subject.id, q.id])"
                                    class="text-xs font-medium text-amber-600 hover:text-amber-800 flex items-center gap-1"
                                >
                                    <Icon icon="mdi:pencil-outline" class="w-3.5 h-3.5" />
                                    Tahrir
                                </Link>
                                <button
                                    @click="confirmDelete(q)"
                                    class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1"
                                >
                                    <Icon icon="mdi:delete-outline" class="w-3.5 h-3.5" />
                                    O'chirish
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="(questions.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500">
                        {{ questions.from }}–{{ questions.to }} / {{ questions.total }}
                    </p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in (questions.links ?? [])" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1.5 text-xs rounded-lg transition"
                                :class="link.active ? 'text-white font-semibold' : 'text-gray-500 hover:bg-gray-100'"
                                :style="link.active ? 'background:linear-gradient(135deg,#0f3460,#533483)' : ''"
                                v-html="link.label"
                            />
                            <span v-else class="px-3 py-1.5 text-xs text-gray-300" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import modal -->
        <div
            v-if="importModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background: rgba(0,0,0,0.5)"
            @click.self="importModal = false"
        >
            <div class="bg-white rounded-2xl w-full max-w-md p-6">
                <h3 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
                    <Icon icon="mdi:upload-outline" class="w-5 h-5 text-[#0f3460]" />
                    Fayldan savollar yuklash
                </h3>
                <p class="text-xs text-gray-400 mb-5">
                    Fan: <strong>{{ subject.name_uz }}</strong>
                </p>

                <div class="space-y-4">
                    <!-- Savol tili -->
                    <div>
                        <label class="field-label">Savol tili <span class="req">*</span></label>
                        <div class="flex gap-3">
                            <button
                                v-for="lang in languages"
                                :key="lang.value"
                                type="button"
                                @click="importForm.language = lang.value"
                                class="flex-1 py-2 rounded-xl border-2 text-sm font-medium transition-all"
                                :style="importForm.language === lang.value
                                    ? 'border-color:#0f3460; background:#eff6ff; color:#0f3460'
                                    : 'border-color:#e5e7eb; color:#6b7280'"
                            >
                                {{ lang.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Fayl -->
                    <div>
                        <label class="field-label">Fayl (.txt yoki .docx) <span class="req">*</span></label>
                        <div
                            class="border-2 border-dashed rounded-xl p-4 text-center cursor-pointer transition-all"
                            :class="importForm.file ? 'border-green-400 bg-green-50' : 'border-gray-200 hover:border-[#0f3460]'"
                            @click="$refs.fileInput.click()"
                            @dragover.prevent
                            @drop.prevent="handleDrop"
                        >
                            <input ref="fileInput" type="file" accept=".txt,.docx" class="hidden" @change="handleFile">
                            <div v-if="!importForm.file">
                                <Icon icon="mdi:cloud-upload-outline" class="w-8 h-8 mx-auto mb-2 text-gray-400" />
                                <p class="text-sm text-gray-500">Fayl tanlash uchun bosing</p>
                                <p class="text-xs text-gray-400 mt-1">.txt yoki .docx — max 10MB</p>
                            </div>
                            <div v-else class="flex items-center justify-center gap-2">
                                <Icon icon="mdi:file-check-outline" class="w-5 h-5 text-green-600" />
                                <span class="text-sm text-green-700 font-medium">{{ importForm.file.name }}</span>
                                <button type="button" @click.stop="importForm.file = null">
                                    <Icon icon="mdi:close-circle" class="w-4 h-4 text-red-400" />
                                </button>
                            </div>
                        </div>
                        <p v-if="importErrors.file" class="err">{{ importErrors.file }}</p>
                    </div>

                    <a
                        :href="route('admin.subjects.questions.template', subject.id)"
                        class="flex items-center gap-1.5 text-xs hover:underline"
                        style="color:#0f3460"
                    >
                        <Icon icon="mdi:download-outline" class="w-3.5 h-3.5" />
                        Namuna shablonni yuklab olish
                    </a>
                </div>

                <div class="flex gap-3 mt-6">
                    <button @click="importModal = false" class="btn-secondary flex-1">Bekor qilish</button>
                    <button @click="submitImport" :disabled="importing" class="btn-primary flex-1">
                        <Icon v-if="importing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                        <Icon v-else icon="mdi:upload" class="w-4 h-4" />
                        {{ importing ? 'Yuklanmoqda...' : 'Yuklash' }}
                    </button>
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
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Savolni o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-2 line-clamp-2">{{ deleteTarget?.question }}</p>
                <p class="text-xs text-red-400 text-center mb-6">Bu amalni ortga qaytarib bo'lmaydi!</p>
                <div class="flex gap-3">
                    <button @click="deleteTarget = null" class="btn-secondary flex-1">Bekor qilish</button>
                    <button @click="deleteQuestion" class="btn-danger flex-1">O'chirish</button>
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
    subject:   { type: Object, required: true },
    questions: { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
    activeLang: { type: String, default: null },
    uzCount:   { type: Number, default: 0 },
    ruCount:   { type: Number, default: 0 },
})

const importModal  = ref(false)
const importing    = ref(false)
const importErrors = ref({})
const deleteTarget = ref(null)

const importForm = ref({ language: 'uz', file: null })

const languages = [
    { value: 'uz', label: "O'zbek" },
    { value: 'ru', label: 'Rus' },
]

const handleFile = (e) => {
    importForm.value.file = e.target.files[0] || null
}

const handleDrop = (e) => {
    importForm.value.file = e.dataTransfer.files[0] || null
}

const submitImport = () => {
    importErrors.value = {}
    if (!importForm.value.file) { importErrors.value.file = 'Fayl yuklang'; return }

    importing.value = true
    const data = new FormData()
    data.append('language', importForm.value.language)
    data.append('file', importForm.value.file)

    router.post(route('admin.subjects.questions.import', props.subject.id), data, {
        forceFormData: true,
        onSuccess: () => {
            importModal.value = false
            importing.value   = false
            importForm.value  = { language: 'uz', file: null }
        },
        onError: (errors) => {
            importErrors.value = errors
            importing.value    = false
        },
    })
}

const confirmDelete = (q) => { deleteTarget.value = q }

const deleteQuestion = () => {
    router.delete(route('admin.subjects.questions.destroy', [props.subject.id, deleteTarget.value.id]), {
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
    text-decoration: none;
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
