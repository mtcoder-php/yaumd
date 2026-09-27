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
                        <div class="text-sm text-gray-700 prose prose-sm max-w-none" v-html="book.description"></div>
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
                        <th>Kim oldi / muddati</th>
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
                        <td class="text-sm">
                            <template v-if="c.active_loan">
                                <p class="font-semibold text-gray-900">{{ c.active_loan.borrower_name || '—' }}</p>
                                <p class="text-xs mt-0.5" :class="isOverdue(c.active_loan) ? 'text-red-600 font-semibold' : 'text-gray-400'">
                                    {{ formatDate(c.active_loan.due_date) }} gacha
                                    <span v-if="isOverdue(c.active_loan)">— muddati o'tgan</span>
                                </p>
                            </template>
                            <span v-else class="text-gray-300">—</span>
                        </td>
                        <td class="text-sm text-gray-500">{{ c.condition_notes || '—' }}</td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <button v-if="c.status === 'available'" @click="openLoanModal(c)"
                                        class="btn-brand !py-1.5 !px-3 !text-xs">
                                    <Icon icon="mdi:book-arrow-right-outline" class="w-4 h-4" />
                                    Berish
                                </button>
                                <button v-else-if="c.status === 'loaned' && c.active_loan" @click="confirmReturnLoan(c)"
                                        class="btn-neutral !py-1.5 !px-3 !text-xs">
                                    <Icon icon="mdi:book-arrow-left-outline" class="w-4 h-4" />
                                    Qaytarildi
                                </button>
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
        <div v-if="copyModalOpen" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 py-8"
             style="background: rgba(0,0,0,0.5)" @click.self="closeCopyModal">
            <div class="bg-white rounded-2xl w-full max-w-xl p-6 my-auto">
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
                            <option v-if="editingCopy?.status === 'loaned'" value="loaned">Berilgan</option>
                        </select>
                        <p v-if="copyForm.errors.status" class="err">{{ copyForm.errors.status }}</p>
                    </div>
                    <div>
                        <label class="field-label">Izoh</label>
                        <RichTextEditor v-model="copyForm.condition_notes" placeholder="Ixtiyoriy" :height="160" />
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

        <!-- Kitob berish modali -->
        <div v-if="loanModalOpen" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 py-8"
             style="background: rgba(0,0,0,0.5)" @click.self="closeLoanModal">
            <div class="bg-white rounded-2xl w-full max-w-md p-6 my-auto">
                <h3 class="text-base font-bold text-gray-900 mb-1">Kitob berish</h3>
                <p class="text-xs text-gray-400 mb-4">Nusxa: <strong>{{ loanTargetCopy?.inventory_code }}</strong></p>

                <div class="space-y-4">
                    <div>
                        <label class="field-label">Kimga beriladi</label>
                        <div class="flex gap-2 mb-2">
                            <button type="button" @click="switchBorrowerType('student')"
                                    class="flex-1 py-1.5 rounded-lg text-xs font-semibold border"
                                    :class="loanBorrowerType === 'student' ? 'bg-brand-50 border-brand-300 text-brand-700' : 'border-gray-200 text-gray-500'">
                                Talaba
                            </button>
                            <button type="button" @click="switchBorrowerType('staff')"
                                    class="flex-1 py-1.5 rounded-lg text-xs font-semibold border"
                                    :class="loanBorrowerType === 'staff' ? 'bg-brand-50 border-brand-300 text-brand-700' : 'border-gray-200 text-gray-500'">
                                Xodim
                            </button>
                        </div>

                        <!-- Filtrlar — ko'p sonli talaba/xodim orasidan tezroq topish uchun -->
                        <div v-if="loanBorrowerType === 'student'" class="grid grid-cols-3 gap-2 mb-2">
                            <select v-model="loanDirectionId" @change="onDirectionOrCourseChange" class="field-input !py-1.5 !text-xs">
                                <option :value="null">Yo'nalish</option>
                                <option v-for="d in directions" :key="d.id" :value="d.id">{{ d.name_uz }}</option>
                            </select>
                            <select v-model="loanCourseYear" @change="onDirectionOrCourseChange" class="field-input !py-1.5 !text-xs">
                                <option :value="null">Kurs</option>
                                <option v-for="c in 6" :key="c" :value="c">{{ c }}-kurs</option>
                            </select>
                            <select v-model="loanGroupId" @change="runBorrowerSearch" class="field-input !py-1.5 !text-xs"
                                    :disabled="!loanGroups.length">
                                <option :value="null">Guruh</option>
                                <option v-for="g in loanGroups" :key="g.id" :value="g.id">{{ g.name }}</option>
                            </select>
                        </div>
                        <div v-else class="mb-2">
                            <select v-model="loanStaffRole" @change="runBorrowerSearch" class="field-input !py-1.5 !text-xs">
                                <option :value="null">Barcha lavozimlar</option>
                                <option v-for="r in staffRoles" :key="r.id" :value="r.name">{{ r.name }}</option>
                            </select>
                        </div>

                        <input v-model="loanSearchQuery" @input="runBorrowerSearch" type="text"
                               placeholder="Ism bo'yicha qidiring..." class="field-input">

                        <div v-if="loanSelectedBorrower" class="mt-2 flex items-center justify-between bg-brand-50 border border-brand-200 rounded-lg px-3 py-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <img v-if="loanSelectedBorrower.photo_url" :src="loanSelectedBorrower.photo_url"
                                     class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
                                <div v-else class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0 text-xs font-semibold text-brand-600">
                                    {{ initials(loanSelectedBorrower.name) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">
                                        {{ loanSelectedBorrower.name }}
                                        <span v-if="!loanSelectedBorrower.is_active" class="badge-pill badge-neutral !text-[10px] !py-0 ml-1">Nofaol</span>
                                    </p>
                                    <p class="text-xs text-gray-400 truncate">
                                        {{ loanSelectedBorrower.extra || '—' }}<span v-if="loanSelectedBorrower.meta"> · {{ loanSelectedBorrower.meta }}</span>
                                    </p>
                                </div>
                            </div>
                            <button @click="clearSelectedBorrower" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                                <Icon icon="mdi:close" class="w-4 h-4" />
                            </button>
                        </div>
                        <div v-else-if="loanSearchResults.length" class="mt-2 border border-gray-100 rounded-lg max-h-48 overflow-y-auto">
                            <button v-for="r in loanSearchResults" :key="`${r.type}-${r.id}`" type="button"
                                    @click="selectBorrower(r)"
                                    class="w-full flex items-center gap-2 text-left px-3 py-2 text-sm hover:bg-gray-50 border-b border-gray-50 last:border-0">
                                <img v-if="r.photo_url" :src="r.photo_url" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
                                <div v-else class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0 text-xs font-semibold text-gray-500">
                                    {{ initials(r.name) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-gray-900 truncate">
                                        {{ r.name }}
                                        <span v-if="!r.is_active" class="badge-pill badge-neutral !text-[10px] !py-0 ml-1">Nofaol</span>
                                    </p>
                                    <p class="text-xs text-gray-400 truncate">
                                        {{ r.extra || '—' }}<span v-if="r.meta"> · {{ r.meta }}</span>
                                    </p>
                                </div>
                            </button>
                        </div>

                        <!-- Tanlangan shaxsning kutubxona tarixi — kutubxonachi yangi
                             kitob berishdan oldin oldingi holatni ko'rib qaror qiladi -->
                        <div v-if="loanSelectedBorrower" class="mt-2 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-xs">
                            <p v-if="loanBorrowerHistoryLoading" class="text-gray-400">Kutubxona tarixi yuklanmoqda...</p>
                            <template v-else-if="loanBorrowerHistory">
                                <p class="text-gray-600">
                                    Jami olingan: <strong>{{ loanBorrowerHistory.total_count }}</strong> ta ·
                                    Hozir qo'lida:
                                    <strong :class="loanBorrowerHistory.active_count >= maxActiveLoansPerBorrower ? 'text-red-600' : ''">
                                        {{ loanBorrowerHistory.active_count }}/{{ maxActiveLoansPerBorrower }}
                                    </strong>
                                    <span v-if="loanBorrowerHistory.overdue_count > 0" class="text-red-600 font-semibold">
                                        · {{ loanBorrowerHistory.overdue_count }} tasi muddati o'tgan!
                                    </span>
                                </p>
                                <ul v-if="loanBorrowerHistory.active_loans.length" class="mt-1 space-y-0.5">
                                    <li v-for="l in loanBorrowerHistory.active_loans" :key="l.id"
                                        :class="l.is_overdue ? 'text-red-600 font-medium' : 'text-gray-500'">
                                        📕 {{ l.title }} — {{ formatDate(l.due_date) }} gacha
                                    </li>
                                </ul>
                            </template>
                        </div>

                        <p v-if="loanForm.errors.borrower_id" class="err">{{ loanForm.errors.borrower_id }}</p>
                    </div>
                    <div>
                        <label class="field-label">Qaytarish muddati</label>
                        <input v-model="loanForm.due_date" type="date" class="field-input"
                               :class="loanForm.errors.due_date ? 'field-error' : ''">
                        <p v-if="loanForm.errors.due_date" class="err">{{ loanForm.errors.due_date }}</p>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button @click="closeLoanModal" class="btn-neutral flex-1 justify-center">Bekor qilish</button>
                    <button @click="submitLoan" :disabled="loanForm.processing || !loanSelectedBorrower"
                            class="btn-brand flex-1 justify-center">
                        {{ loanForm.processing ? 'Berilmoqda...' : 'Berish' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Kitob qaytarishni tasdiqlash modali -->
        <div v-if="returnLoanTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="returnLoanTarget = null">
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <div class="w-12 h-12 rounded-full bg-brand-50 flex items-center justify-center mx-auto mb-4">
                    <Icon icon="mdi:book-arrow-left-outline" class="w-6 h-6 text-brand-600" />
                </div>
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Kitobni qaytarish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ returnLoanTarget?.inventory_code }}</strong> nusxasi
                    <strong>{{ returnLoanTarget?.active_loan?.borrower_name || '—' }}</strong>dan
                    qaytarib olindimi?
                </p>
                <div class="flex gap-3">
                    <button @click="returnLoanTarget = null" class="btn-neutral flex-1 justify-center">Bekor qilish</button>
                    <button @click="submitReturnLoan" :disabled="returnLoanProcessing" class="btn-brand flex-1 justify-center">
                        {{ returnLoanProcessing ? 'Saqlanmoqda...' : 'Ha, qaytarildi' }}
                    </button>
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
import RichTextEditor from '@/Components/RichTextEditor.vue'

const props = defineProps({
    book: { type: Object, required: true },
    directions: { type: Array, default: () => [] },
    staffRoles: { type: Array, default: () => [] },
    maxActiveLoansPerBorrower: { type: Number, default: 3 },
})

const languageLabel = (v) => ({ uz: "O'zbek", ru: 'Rus', en: 'Ingliz' }[v] || v || '—')

// 'due_date' backend'dan ISO satr sifatida keladi (masalan
// "2026-10-11T00:00:00.000000Z") — bu yerda faqat sana qismini
// o'zbekcha kunlik ko'rinishga o'giramiz.
const formatDate = (iso) => {
    if (!iso) return '—'
    const d = new Date(iso)
    return d.toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const isOverdue = (loan) => {
    if (!loan?.due_date) return false
    const due = new Date(loan.due_date)
    due.setHours(0, 0, 0, 0)
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    return due < today
}

// Rasmi bo'lmagan talaba/xodim uchun doira ichida ism bosh harflari
// (masalan "Turdiyev Mukhtor" -> "TM") — qidiruv natijalarida vizual
// identifikatsiya uchun.
const initials = (name) => (name || '').trim().split(/\s+/).slice(0, 2).map((w) => w[0]).join('').toUpperCase()

const availableCount = computed(() => (props.book.copies || []).filter(c => c.status === 'available').length)

// "loaned" holati talaba yoki xodimga bir xilda tegishli bo'lishi mumkin
// (BookLoan.borrower_type) — shuning uchun statik "Talaba qo'lida" o'rniga
// neytral matn ishlatiladi; kimga berilgani jadvaldagi "Kim oldi" ustunida
// (borrower_name) allaqachon aniq ko'rsatiladi.
const statusLabel = (v) => ({
    available: 'Mavjud',
    loaned:    'Berilgan',
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

// Kitob berish
const loanModalOpen = ref(false)
const loanTargetCopy = ref(null)
const loanBorrowerType = ref('student')
const loanSearchQuery = ref('')
const loanSearchResults = ref([])
const loanSelectedBorrower = ref(null)

// Talaba filtrlari (yo'nalish -> kurs -> guruh, kaskadli) va xodim filtri
// (rol) — ko'p sonli talaba/xodim orasidan tezroq topish uchun.
const loanDirectionId = ref(null)
const loanCourseYear = ref(null)
const loanGroupId = ref(null)
const loanGroups = ref([])
const loanStaffRole = ref(null)

// Tanlangan shaxsning kutubxona tarixi (jami/faol/muddati o'tgan kitoblar).
const loanBorrowerHistory = ref(null)
const loanBorrowerHistoryLoading = ref(false)

const loanForm = useForm({
    book_copy_id: null,
    borrower_type: 'student',
    borrower_id: null,
    due_date: '',
})

const defaultDueDate = () => {
    const d = new Date()
    d.setDate(d.getDate() + 14)
    return d.toISOString().slice(0, 10)
}

const resetLoanFilters = () => {
    loanDirectionId.value = null
    loanCourseYear.value = null
    loanGroupId.value = null
    loanGroups.value = []
    loanStaffRole.value = null
}

const openLoanModal = (copy) => {
    loanTargetCopy.value = copy
    loanBorrowerType.value = 'student'
    loanSearchQuery.value = ''
    loanSearchResults.value = []
    loanSelectedBorrower.value = null
    loanBorrowerHistory.value = null
    resetLoanFilters()
    loanForm.clearErrors()
    loanForm.book_copy_id = copy.id
    loanForm.borrower_type = 'student'
    loanForm.borrower_id = null
    loanForm.due_date = defaultDueDate()
    loanModalOpen.value = true
}

const closeLoanModal = () => {
    loanModalOpen.value = false
    loanTargetCopy.value = null
    loanBorrowerHistory.value = null
}

const switchBorrowerType = (type) => {
    loanBorrowerType.value = type
    loanForm.borrower_type = type
    loanSelectedBorrower.value = null
    loanForm.borrower_id = null
    loanSearchResults.value = []
    loanBorrowerHistory.value = null
    resetLoanFilters()
    runBorrowerSearch()
}

// Yo'nalish yoki kurs o'zgarsa — avval tanlangan guruh endi mos
// kelmasligi mumkin, shuning uchun guruh tanlovi bekor qilinadi va
// guruhlar ro'yxati shu ikkoviga mos ravishda qayta so'raladi.
const onDirectionOrCourseChange = () => {
    loanGroupId.value = null
    fetchLoanGroups()
    runBorrowerSearch()
}

const fetchLoanGroups = async () => {
    if (!loanDirectionId.value && !loanCourseYear.value) {
        loanGroups.value = []
        return
    }
    try {
        const params = new URLSearchParams()
        if (loanDirectionId.value) params.set('direction_id', loanDirectionId.value)
        if (loanCourseYear.value) params.set('course_year', loanCourseYear.value)
        const response = await fetch(
            route('admin.library.loans.borrower-groups') + '?' + params.toString(),
            { headers: { Accept: 'application/json' } }
        )
        loanGroups.value = await response.json()
    } catch (e) {
        loanGroups.value = []
    }
}

let borrowerSearchTimer = null
const runBorrowerSearch = () => {
    clearTimeout(borrowerSearchTimer)
    borrowerSearchTimer = setTimeout(async () => {
        const hasQuery = loanSearchQuery.value.trim() !== ''
        const hasFilters = loanBorrowerType.value === 'student'
            ? !!(loanDirectionId.value || loanCourseYear.value || loanGroupId.value)
            : !!loanStaffRole.value

        if (!hasQuery && !hasFilters) {
            loanSearchResults.value = []
            return
        }

        try {
            const params = new URLSearchParams({ type: loanBorrowerType.value })
            if (hasQuery) params.set('q', loanSearchQuery.value.trim())

            if (loanBorrowerType.value === 'student') {
                if (loanDirectionId.value) params.set('direction_id', loanDirectionId.value)
                if (loanCourseYear.value) params.set('course_year', loanCourseYear.value)
                if (loanGroupId.value) params.set('group_id', loanGroupId.value)
            } else if (loanStaffRole.value) {
                params.set('role', loanStaffRole.value)
            }

            const response = await fetch(
                route('admin.library.loans.search-borrowers') + '?' + params.toString(),
                { headers: { Accept: 'application/json' } }
            )
            loanSearchResults.value = await response.json()
        } catch (e) {
            loanSearchResults.value = []
        }
    }, 300)
}

const selectBorrower = async (result) => {
    loanSelectedBorrower.value = result
    loanForm.borrower_id = result.id
    loanSearchResults.value = []
    loanBorrowerHistory.value = null
    loanBorrowerHistoryLoading.value = true

    try {
        const response = await fetch(
            route('admin.library.loans.borrower-history', [result.type, result.id]),
            { headers: { Accept: 'application/json' } }
        )
        loanBorrowerHistory.value = await response.json()
    } catch (e) {
        loanBorrowerHistory.value = null
    } finally {
        loanBorrowerHistoryLoading.value = false
    }
}

const clearSelectedBorrower = () => {
    loanSelectedBorrower.value = null
    loanForm.borrower_id = null
    loanBorrowerHistory.value = null
}

const submitLoan = () => {
    // Tostr xabarini bu yerda alohida chaqirmaymiz — backend 'success'
    // flash xabarini qaytaradi, uni esa AppLayout'dagi umumiy watcher
    // (page.props.flash) allaqachon tostr qilib ko'rsatadi. Ikkalasini
    // ham chaqirish ikkita bir xil tostr chiqishiga sabab bo'lgan edi.
    loanForm.post(route('admin.library.loans.store', props.book.id), {
        preserveScroll: true,
        onSuccess: () => closeLoanModal(),
    })
}

// Kitob qaytarish
const returnLoanTarget = ref(null)
const returnLoanProcessing = ref(false)

const confirmReturnLoan = (copy) => {
    returnLoanTarget.value = copy
}

const submitReturnLoan = () => {
    // Xuddi submitLoan'dagi kabi — backend'ning 'success' flash xabari
    // AppLayout orqali avtomatik tostr bo'lib chiqadi, shuning uchun
    // bu yerda qo'shimcha toast.success() chaqirilmaydi.
    returnLoanProcessing.value = true
    router.post(route('admin.library.loans.return', returnLoanTarget.value.active_loan.id), {}, {
        preserveScroll: true,
        onFinish: () => {
            returnLoanProcessing.value = false
            returnLoanTarget.value = null
        },
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
