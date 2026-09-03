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
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                  :class="book.is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'">
                                {{ book.is_active ? 'Faol' : 'Nofaol' }}
                            </span>
                        </h1>
                        <p class="text-sm text-gray-500 mt-0.5">{{ book.author }} — {{ book.category?.name_uz || 'Kategoriyasiz' }}</p>
                    </div>
                </div>
                <Link :href="route('admin.library.edit', book.id)" class="btn-secondary">
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
                <button @click="openCopyModal()" class="btn-primary">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Nusxa qo'shish
                </button>
            </div>

            <!-- Nusxalar jadvali -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div v-if="!book.copies?.length" class="p-12 text-center text-gray-400">
                    <Icon icon="mdi:barcode-off" class="w-10 h-10 mx-auto mb-2 opacity-40" />
                    <p class="text-sm">Hali fizik nusxa qo'shilmagan</p>
                </div>
                <table v-else class="w-full">
                    <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Inventar raqami</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Holati</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Izoh</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                    <tr v-for="c in book.copies" :key="c.id" class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ c.inventory_code }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="statusClass(c.status)">
                                {{ statusLabel(c.status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ c.condition_notes || '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <button @click="openCopyModal(c)"
                                        class="text-xs font-medium text-amber-600 hover:text-amber-800 flex items-center gap-1">
                                    <Icon icon="mdi:pencil-outline" class="w-3.5 h-3.5" />
                                    Tahrir
                                </button>
                                <button @click="confirmDeleteCopy(c)"
                                        class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1">
                                    <Icon icon="mdi:delete-outline" class="w-3.5 h-3.5" />
                                    O'chirish
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
                        <label class="field-label">Inventar raqami <span class="req">*</span></label>
                        <input v-model="copyForm.inventory_code" type="text" placeholder="Masalan: KUT-000123"
                               class="field-input" :class="copyForm.errors.inventory_code ? 'field-error' : ''">
                        <p v-if="copyForm.errors.inventory_code" class="err">{{ copyForm.errors.inventory_code }}</p>
                    </div>
                    <div>
                        <label class="field-label">Holati <span class="req">*</span></label>
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
                    <button @click="closeCopyModal" class="btn-secondary flex-1">Bekor qilish</button>
                    <button @click="submitCopy" :disabled="copyForm.processing" class="btn-primary flex-1">
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
                    <button @click="deleteCopyTarget = null" class="btn-secondary flex-1">Bekor qilish</button>
                    <button @click="submitDeleteCopy" class="btn-danger flex-1">O'chirish</button>
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
    available: 'bg-green-50 text-green-700',
    loaned:    'bg-amber-50 text-amber-700',
    damaged:   'bg-orange-50 text-orange-700',
    lost:      'bg-red-50 text-red-600',
}[v] || 'bg-gray-100 text-gray-500')

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
