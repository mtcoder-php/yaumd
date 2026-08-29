<template>
    <AppLayout title="Yo'nalish-fanlar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Yo'nalish — Fanlar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Yo'nalishlarga test fanlarini biriktirish</p>
                </div>
            </div>

            <!-- Kafedra tabs -->
            <div class="overflow-x-auto scrollbar-hide">
                <div class="flex items-center gap-2 w-max">
                    <button
                        v-for="dept in allDepartments"
                        :key="dept.id"
                        @click="activeDept = dept.id"
                        class="px-4 py-2 text-sm font-medium rounded-xl border transition-all whitespace-nowrap"
                        :class="activeDept === dept.id
                            ? 'border-[#0f3460] text-[#0f3460] bg-blue-50'
                            : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                    >
                        {{ dept.short_name || dept.name_uz }}
                        <span class="ml-1 text-xs opacity-60">({{ dept.directions?.length || 0 }})</span>
                    </button>
                </div>
            </div>

            <!-- Tanlangan kafedra yo'nalishlari -->
            <div v-if="activeDeptData" class="space-y-4">

                <!-- Kafedra nomi -->
                <div class="flex items-center gap-3 px-1">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                         style="background: linear-gradient(135deg, #0f3460, #533483)">
                        {{ activeDeptData.short_name?.substring(0, 2) || 'K' }}
                    </div>
                    <div>
                        <p class="text-base font-bold text-gray-900">{{ activeDeptData.name_uz }}</p>
                        <p class="text-xs text-gray-400">{{ activeDeptData.directions?.length || 0 }} ta yo'nalish</p>
                    </div>
                </div>

                <!-- Yo'nalishlar -->
                <div v-if="!activeDeptData.directions?.length"
                     class="bg-white rounded-2xl border border-gray-100 p-12 text-center text-gray-400"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <Icon icon="mdi:school-off-outline" class="w-10 h-10 mx-auto mb-2 opacity-40" />
                    <p class="text-sm">Bu kafedrada yo'nalish yo'q</p>
                </div>

                <div
                    v-for="direction in activeDeptData.directions"
                    :key="direction.id"
                    class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                    style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)"
                >
                    <!-- Yo'nalish header -->
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-50"
                         style="background: linear-gradient(135deg, #f8faff, #f5f3ff)">
                        <div>
                            <p class="text-sm font-bold text-gray-900">{{ direction.name_uz }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ direction.degree === 'bachelor' ? 'Bakalavr' : 'Magistr' }} •
                                {{ direction.duration_years }} yil •
                                {{ direction.subjects?.length || 0 }} ta fan
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <p class="text-xs text-gray-400">Jami ball</p>
                                <p class="text-sm font-bold" style="color:#0f3460">
                                    {{ totalScore(direction) }}
                                </p>
                            </div>
                            <button
                                @click="openAdd(direction)"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-white transition"
                                style="background: linear-gradient(135deg, #0f3460, #533483)"
                            >
                                <Icon icon="mdi:plus" class="w-3.5 h-3.5" />
                                Fan biriktirish
                            </button>
                        </div>
                    </div>

                    <!-- Fanlar jadvali -->
                    <div v-if="!direction.subjects?.length" class="px-5 py-6 text-center text-gray-400">
                        <Icon icon="mdi:book-off-outline" class="w-7 h-7 mx-auto mb-1.5 opacity-40" />
                        <p class="text-xs">Hali fan biriktirilmagan</p>
                    </div>

                    <table v-else class="w-full">
                        <thead>
                        <tr class="border-b border-gray-50">
                            <th class="text-left px-5 py-2 text-xs font-semibold text-gray-400">Fan</th>
                            <th class="text-left px-3 py-2 text-xs font-semibold text-gray-400">Blok</th>
                            <th class="text-center px-3 py-2 text-xs font-semibold text-gray-400">Savollar</th>
                            <th class="text-center px-3 py-2 text-xs font-semibold text-gray-400">Ball/savol</th>
                            <th class="text-center px-3 py-2 text-xs font-semibold text-gray-400">Jami</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                        <tr v-for="ds in direction.subjects" :key="ds.id"
                            class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3">
                                <p class="text-sm font-medium text-gray-800">{{ ds.subject?.name_uz }}</p>
                                <p class="text-xs text-gray-400">{{ ds.subject?.name_ru }}</p>
                            </td>
                            <td class="px-3 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold"
                                          :class="blockBadge(ds.block_type)">
                                        {{ blockLabel(ds.block_type) }}
                                    </span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="text-sm font-bold text-gray-700">{{ ds.questions_count }}</span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="text-sm font-bold" style="color:#0f3460">{{ ds.score_per_question }}</span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                    <span class="text-sm font-bold text-green-600">
                                        {{ (ds.questions_count * ds.score_per_question).toFixed(1) }}
                                    </span>
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-2">
                                    <button @click="openEdit(ds)" class="text-amber-600 hover:text-amber-800">
                                        <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                                    </button>
                                    <button @click="confirmDelete(ds)" class="text-red-500 hover:text-red-700">
                                        <Icon icon="mdi:delete-outline" class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Fan biriktirish modal -->
        <div v-if="addModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="addModal = false">
            <div class="bg-white rounded-2xl w-full max-w-md p-6">
                <h3 class="text-base font-bold text-gray-900 mb-1">Fan biriktirish</h3>
                <p class="text-xs text-gray-400 mb-5">{{ selectedDirection?.name_uz }}</p>
                <div class="space-y-4">
                    <div>
                        <label class="field-label">Fan <span class="req">*</span></label>
                        <select v-model="addForm.subject_id" class="field-input">
                            <option value="">Tanlang</option>
                            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name_uz }}</option>
                        </select>
                        <p v-if="addErrors.subject_id" class="err">{{ addErrors.subject_id }}</p>
                    </div>
                    <div>
                        <label class="field-label">Blok turi <span class="req">*</span></label>
                        <div class="flex flex-col gap-2">
                            <button v-for="bt in blockTypes" :key="bt.value" type="button"
                                    @click="selectBlockType(bt)"
                                    class="flex items-center justify-between px-4 py-3 rounded-xl border-2 text-left transition-all"
                                    :style="addForm.block_type === bt.value
                                    ? 'border-color:#0f3460; background:linear-gradient(135deg,#eff6ff,#f5f3ff)'
                                    : 'border-color:#e5e7eb; background:#fafafa'">
                                <div>
                                    <p class="text-sm font-semibold"
                                       :style="addForm.block_type === bt.value ? 'color:#0f3460' : 'color:#374151'">
                                        {{ bt.label }}
                                    </p>
                                    <p class="text-xs text-gray-400">{{ bt.desc }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-400">Standart</p>
                                    <p class="text-xs font-semibold" style="color:#0f3460">
                                        {{ bt.defaultCount }} savol × {{ bt.defaultScore }} ball
                                    </p>
                                </div>
                            </button>
                        </div>
                        <p v-if="addErrors.block_type" class="err">{{ addErrors.block_type }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="field-label">Savollar soni <span class="req">*</span></label>
                            <input v-model="addForm.questions_count" type="number" min="1" max="100"
                                   class="field-input" :class="addErrors.questions_count ? 'field-error' : ''">
                            <p v-if="addErrors.questions_count" class="err">{{ addErrors.questions_count }}</p>
                        </div>
                        <div>
                            <label class="field-label">Ball/savol <span class="req">*</span></label>
                            <input v-model="addForm.score_per_question" type="number" min="0.1" max="10" step="0.1"
                                   class="field-input" :class="addErrors.score_per_question ? 'field-error' : ''">
                            <p v-if="addErrors.score_per_question" class="err">{{ addErrors.score_per_question }}</p>
                        </div>
                    </div>
                    <div v-if="addForm.questions_count && addForm.score_per_question"
                         class="p-3 rounded-xl text-center"
                         style="background: linear-gradient(135deg, #eff6ff, #f5f3ff)">
                        <p class="text-xs text-gray-500">Jami ball</p>
                        <p class="text-xl font-bold" style="color:#0f3460">
                            {{ (addForm.questions_count * addForm.score_per_question).toFixed(1) }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button @click="addModal = false" class="btn-secondary flex-1">Bekor qilish</button>
                    <button @click="submitAdd" :disabled="adding" class="btn-primary flex-1">
                        <Icon v-if="adding" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                        Biriktirish
                    </button>
                </div>
            </div>
        </div>

        <!-- Tahrirlash modal -->
        <div v-if="editModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="editModal = false">
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <h3 class="text-base font-bold text-gray-900 mb-1">Tahrirlash</h3>
                <p class="text-xs text-gray-400 mb-5">{{ editTarget?.subject?.name_uz }}</p>
                <div class="space-y-4">
                    <div>
                        <label class="field-label">Blok turi</label>
                        <select v-model="editForm.block_type" class="field-input">
                            <option v-for="bt in blockTypes" :key="bt.value" :value="bt.value">{{ bt.label }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="field-label">Savollar soni</label>
                            <input v-model="editForm.questions_count" type="number" min="1" max="100" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Ball/savol</label>
                            <input v-model="editForm.score_per_question" type="number" min="0.1" max="10" step="0.1" class="field-input">
                        </div>
                    </div>
                    <div v-if="editForm.questions_count && editForm.score_per_question"
                         class="p-3 rounded-xl text-center"
                         style="background: linear-gradient(135deg, #eff6ff, #f5f3ff)">
                        <p class="text-xs text-gray-500">Jami ball</p>
                        <p class="text-xl font-bold" style="color:#0f3460">
                            {{ (editForm.questions_count * editForm.score_per_question).toFixed(1) }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button @click="editModal = false" class="btn-secondary flex-1">Bekor qilish</button>
                    <button @click="submitEdit" class="btn-primary flex-1">Saqlash</button>
                </div>
            </div>
        </div>

        <!-- O'chirish modal -->
        <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="deleteTarget = null">
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <Icon icon="mdi:delete-outline" class="w-6 h-6 text-red-500" />
                </div>
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Fanni olib tashlash</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget?.subject?.name_uz }}</strong> fanini olib tashlaysizmi?
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
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    faculties: { type: Array, default: () => [] },
    subjects:  { type: Array, default: () => [] },
})

// Barcha kafedralarni faculties dan yig'amiz
const allDepartments = computed(() =>
    props.faculties.flatMap(f => f.departments || [])
)

const activeDept     = ref(allDepartments.value[0]?.id || null)
const addModal       = ref(false)
const editModal      = ref(false)
const deleteTarget   = ref(null)
const selectedDirection = ref(null)
const editTarget     = ref(null)
const adding         = ref(false)
const addErrors      = ref({})

const addForm = ref({
    direction_id:       '',
    subject_id:         '',
    block_type:         '',
    questions_count:    10,
    score_per_question: 1.1,
})

const editForm = ref({
    block_type:         '',
    questions_count:    10,
    score_per_question: 1.1,
})

const blockTypes = [
    { value: 'mandatory',   label: 'Majburiy blok',  desc: 'Barcha abituriyentlar uchun',      defaultCount: 10, defaultScore: 1.1 },
    { value: 'specialty_1', label: '1-asosiy fan',   desc: 'Mutaxassislik bloki (yuqori ball)', defaultCount: 30, defaultScore: 3.1 },
    { value: 'specialty_2', label: '2-asosiy fan',   desc: "Mutaxassislik bloki (o'rta ball)",  defaultCount: 30, defaultScore: 2.1 },
]

const activeDeptData = computed(() =>
    allDepartments.value.find(d => d.id === activeDept.value)
)

const totalScore = (direction) => {
    if (!direction.subjects?.length) return 0
    return direction.subjects.reduce((sum, ds) =>
        sum + (ds.questions_count * ds.score_per_question), 0
    ).toFixed(1)
}

const blockLabel = (type) => blockTypes.find(b => b.value === type)?.label || type
const blockBadge = (type) => ({
    mandatory:   'bg-blue-50 text-blue-700',
    specialty_1: 'bg-green-50 text-green-700',
    specialty_2: 'bg-orange-50 text-orange-700',
}[type] || 'bg-gray-100 text-gray-600')

const selectBlockType = (bt) => {
    addForm.value.block_type         = bt.value
    addForm.value.questions_count    = bt.defaultCount
    addForm.value.score_per_question = bt.defaultScore
}

const openAdd = (direction) => {
    selectedDirection.value          = direction
    addForm.value.direction_id       = direction.id
    addForm.value.subject_id         = ''
    addForm.value.block_type         = ''
    addForm.value.questions_count    = 10
    addForm.value.score_per_question = 1.1
    addErrors.value                  = {}
    addModal.value                   = true
}

const submitAdd = () => {
    addErrors.value = {}
    if (!addForm.value.subject_id) { addErrors.value.subject_id = 'Fanni tanlang'; return }
    if (!addForm.value.block_type) { addErrors.value.block_type = 'Blok turini tanlang'; return }
    adding.value = true
    router.post(route('admin.direction-subjects.store'), addForm.value, {
        onSuccess: () => { addModal.value = false; adding.value = false },
        onError:   (errors) => { addErrors.value = errors; adding.value = false },
    })
}

const openEdit = (ds) => {
    editTarget.value                  = ds
    editForm.value.block_type         = ds.block_type
    editForm.value.questions_count    = ds.questions_count
    editForm.value.score_per_question = ds.score_per_question
    editModal.value                   = true
}

const submitEdit = () => {
    router.put(route('admin.direction-subjects.update', editTarget.value.id), editForm.value, {
        onSuccess: () => { editModal.value = false },
    })
}

const confirmDelete = (ds) => { deleteTarget.value = ds }
const submitDelete  = () => {
    router.delete(route('admin.direction-subjects.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
    })
}
</script>

<style scoped>
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
.scrollbar-hide::-webkit-scrollbar { display: none; }
.field-label { display: block; font-size: 0.78rem; font-weight: 600; color: #374151; margin-bottom: 0.375rem; }
.req { color: #ef4444; }
.field-input { width: 100%; padding: 0.6rem 0.875rem; border-radius: 0.625rem; border: 1.5px solid #e5e7eb; font-size: 0.875rem; color: #111827; background: #fafafa; outline: none; transition: border-color 0.2s; appearance: auto; }
.field-input:focus { border-color: #0f3460; background: white; }
.field-error { border-color: #f87171 !important; background: #fef2f2 !important; }
.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }
.btn-primary { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.75rem; background: linear-gradient(135deg, #0f3460, #533483); color: white; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; }
.btn-primary:hover { box-shadow: 0 6px 20px rgba(15,52,96,0.3); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.75rem; background: white; color: #374151; font-size: 0.875rem; font-weight: 600; border: 1.5px solid #e5e7eb; cursor: pointer; }
.btn-secondary:hover { background: #f9fafb; }
.btn-danger { display: inline-flex; align-items: center; justify-content: center; padding: 0.625rem 1.25rem; border-radius: 0.75rem; background: #ef4444; color: white; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer; }
.btn-danger:hover { background: #dc2626; }
</style>
