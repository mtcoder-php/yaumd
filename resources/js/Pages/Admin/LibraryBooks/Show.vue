<template>
    <AppLayout :title="book.title">
        <div class="max-w-4xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.library.index')"
                          class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
                        <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            {{ book.title }}
                            <span class="badge-pill" :class="book.is_active ? 'badge-success' : 'badge-neutral'">
                                {{ book.is_active ? 'Faol' : 'Nofaol' }}
                            </span>
                        </h1>
                        <p class="text-sm text-gray-500 mt-0.5">{{ book.author }} — {{ book.category?.name_uz || 'Kategoriyasiz' }}</p>
                    </div>
                </div>
                <Link :href="route('admin.library.edit', book.id)" class="btn-brand">
                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                    Tahrirlash
                </Link>
            </div>

            <!-- Ma'lumot kartasi -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 flex gap-5"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="w-24 h-32 rounded-xl bg-gray-100 overflow-hidden flex items-center justify-center flex-shrink-0">
                    <img v-if="book.cover_image_url" :src="book.cover_image_url" class="w-full h-full object-cover" alt="">
                    <Icon v-else icon="mdi:book-outline" class="w-8 h-8 text-gray-400" />
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 flex-1">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">ISBN</p>
                        <p class="text-sm font-semibold text-gray-900">{{ book.isbn || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Nashriyot</p>
                        <p class="text-sm font-semibold text-gray-900">{{ book.publisher || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Nashr yili</p>
                        <p class="text-sm font-semibold text-gray-900">{{ book.published_year || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Til</p>
                        <p class="text-sm font-semibold text-gray-900">{{ languageLabel(book.language) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Sahifalar</p>
                        <p class="text-sm font-semibold text-gray-900">{{ book.page_count || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Joylashuvi</p>
                        <p class="text-sm font-semibold text-gray-900">{{ book.shelf_location || '—' }}</p>
                    </div>
                    <div class="col-span-2 sm:col-span-3">
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Bazaga kiritgan</p>
                        <p class="text-sm font-semibold text-gray-900">{{ book.added_by?.full_name || '—' }}</p>
                    </div>
                    <div v-if="book.description" class="col-span-2 sm:col-span-3">
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Tavsif</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ book.description }}</p>
                    </div>
                </div>
            </div>

            <!-- Nusxa qo'shish -->
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-base font-bold text-gray-900">Fizik nusxalar (inventar)</p>
                    <p class="text-xs text-gray-400 mt-0.5">Jami {{ book.copies?.length || 0 }} ta nusxa, shundan {{ availableCount }} tasi bo'sh</p>
                </div>
                <button @click="openCopyModal()" class="btn-brand">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Nusxa qo'shish
                </button>
            </div>

            <!-- Nusxalar jadvali -->
            <div class="table-grid-wrap">
                <div v-if="!book.copies?.length" class="p-12 text-center text-gray-400">
                    <Icon icon="mdi:barcode-off" class="w-10 h-10 mx-auto mb-2 opacity-40" />
                    <p class="text-sm">Hali fizik nusxa qo'shilmagan</p>
                </div>
                <table v-else class="table-grid">
                    <thead>
                    <tr>
                        <th>Inventar raqami</th>
                        <th>Holati</th>
                        <th>Izoh</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="c in book.copies" :key="c.id">
                        <td class="text-sm font-semibold text-gray-900">{{ c.inventory_code }}</td>
                        <td>
                            <span class="badge-pill" :class="statusClass(c.status)">
                                {{ statusLabel(c.status) }}
                            </span>
                        </td>
                        <td class="text-sm text-gray-500">{{ c.condition_notes || '—' }}</td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <button @click="openCopyModal(c)" title="Tahrirlash" class="btn-ghost-icon">
                                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                                </button>
                                <button @click="confirmDeleteCopy(c)" title="O'chirish" class="btn-ghost-icon danger">
                                    <Icon icon="mdi:delete-outline" class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Nusxa qo'shish/tahrirlash modali -->
        <div v-if="copyModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="closeCopyModal">
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <h3 class="text-base font-bold text-gray-900 mb-4">
                    {{ editingCopy ? 'Nusxani tahrirlash' : "Yangi nusxa qo'shish" }}
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="field-label"><span class="req">*</span> Inventar raqami</label>
                        <input v-model="copyForm.inventory_code" type="text" placeholder="Masalan: KUT-000123"
                               class="field-input" :class="copyForm.errors.inventory_code ? 'field-error' : ''">
                        <p v-if="copyForm.errors.inventory_code" class="err">{{ copyForm.errors.inventory_code }}</p>
                    </div>
                    <div>
                        <label class="field-label"><span class="req">*</span> Holati</label>
                        <select v-model="copyForm.status" class="field-input">
                            <option value="available">Mavjud (bo'sh)</option>
                            <option value="damaged">Shikastlangan</option>
                            <option value="lost">Yo'qolgan</option>
                            <option v-if="editingCopy?.status === 'loaned'" value="loaned">Talaba qo'lida</option>
                        </select>
                        <p v-if="copyForm.errors.status" class="err">{{ copyForm.errors.status }}</p>
                    </div>
                    <div>
                        <label class="field-label">Izoh</label>
                        <textarea v-model="copyForm.condition_notes" rows="2" placeholder="Ixtiyoriy" class="field-input"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button @click="closeCopyModal" class="btn-neutral flex-1 justify-center">Bekor qilish</button>
                    <button @click="submitCopy" :disabled="copyForm.processing" class="btn-brand flex-1 justify-center">
                        {{ copyForm.processing ? 'Saqlanmoqda...' : 'Saqlash' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Nusxa o'chirish modali -->
        <div v-if="deleteCopyTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="deleteCopyTarget = null">
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <Icon icon="mdi:delete-outline" class="w-6 h-6 text-red-500" />
                </div>
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Nusxani o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteCopyTarget?.inventory_code }}</strong> nusxasini o'chirasizmi?
                </p>
                <div class="flex gap-3">
                    <button @click="deleteCopyTarget = null" class="btn-neutral flex-1 justify-center">Bekor qilish</button>
                    <button @click="submitDeleteCopy" class="btn-danger-pill flex-1">O'chirish</button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    book: { type: Object, required: true },
})

const languageLabel = (v) => ({ uz: "O'zbek", ru: 'Rus', en: 'Ingliz' }[v] || v || '—')

const availableCount = computed(() => (props.book.copies || []).filter(c => c.status === 'available').length)

const statusLabel = (v) => ({
    available: 'Mavjud',
    loaned:    "Talaba qo'lida",
    damaged:   'Shikastlangan',
    lost:      "Yo'qolgan",
}[v] || v)

const statusClass = (v) => ({
    available: 'badge-success',
    loaned:    'badge-brand',
    damaged:   'badge-warning',
    lost:      'badge-danger',
}[v] || 'badge-neutral')

// Nusxa qo'shish/tahrirlash
const copyModalOpen = ref(false)
const editingCopy = ref(null)

const copyForm = useForm({
    inventory_code:  '',
    status:          'available',
    condition_notes: '',
})

const openCopyModal = (copy = null) => {
    editingCopy.value = copy
    copyForm.clearErrors()
    copyForm.inventory_code  = copy?.inventory_code  || ''
    copyForm.status          = copy?.status          || 'available'
    copyForm.condition_notes = copy?.condition_notes || ''
    copyModalOpen.value = true
}

const closeCopyModal = () => {
    copyModalOpen.value = false
    editingCopy.value = null
}

const submitCopy = () => {
    const onSuccess = () => closeCopyModal()

    if (editingCopy.value) {
        copyForm.put(route('admin.library.copies.update', [props.book.id, editingCopy.value.id]), { onSuccess })
    } else {
        copyForm.post(route('admin.library.copies.store', props.book.id), { onSuccess })
    }
}

// Nusxa o'chirish
const deleteCopyTarget = ref(null)
const confirmDeleteCopy = (c) => { deleteCopyTarget.value = c }
const submitDeleteCopy = () => {
    router.delete(route('admin.library.copies.destroy', [props.book.id, deleteCopyTarget.value.id]), {
        onSuccess: () => { deleteCopyTarget.value = null },
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
.req { color: #ef4444; margin-right: 0.15rem; }
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
.field-input:focus { border-color: var(--color-brand-600); background: white; }
.field-error { border-color: #f87171 !important; background: #fef2f2 !important; }
.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }
</style>
