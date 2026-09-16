<template>
    <AppLayout title="Mening shartnomam">
        <div class="max-w-3xl mx-auto space-y-5">

            <div>
                <h1 class="text-xl font-bold text-gray-900">Mening shartnomam</h1>
                <p class="text-sm text-gray-500 mt-0.5">Shartnoma va to'lovlar haqida ma'lumot</p>
            </div>

            <!-- Telegram bot -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-3"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="flex items-center gap-2">
                    <Icon icon="mdi:send-circle-outline" class="w-5 h-5" style="color:#0f3460" />
                    <p class="text-sm font-bold text-gray-900">Telegram bot</p>
                </div>

                <template v-if="telegram.linked">
                    <p class="text-xs text-gray-500">
                        ✅ Hisobingiz Telegram botga ulangan — to'lov muddati va holatingiz haqida avtomatik xabar olasiz.
                    </p>
                    <button @click="unlinkTelegram" :disabled="unlinking" class="btn-outline-danger">
                        <Icon v-if="unlinking" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                        <span v-else>Bog'lanishni uzish</span>
                    </button>
                </template>
                <template v-else>
                    <p class="text-xs text-gray-500">
                        Shartnoma to'lovi holatini (qarzi bormi, keyingi muddat qachon) Telegram orqali kuzatib borish uchun botga ulaning.
                    </p>

                    <div v-if="!telegramCode" class="flex flex-wrap gap-3">
                        <button @click="requestCode" :disabled="requestingCode" class="btn-outline">
                            <Icon v-if="requestingCode" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                            <span v-else>Ulash kodini olish</span>
                        </button>
                    </div>

                    <div v-else class="bg-gray-50 rounded-xl p-4 space-y-2">
                        <p class="text-xs text-gray-500">
                            Botga quyidagi tugma orqali o'ting (yoki kodni <code>/kod {{ telegramCode.code }}</code> ko'rinishida yuboring) — kod {{ telegramCode.expires_in }} daqiqa amal qiladi.
                        </p>
                        <p class="text-2xl font-bold tracking-widest text-center py-2" style="color:#0f3460">{{ telegramCode.code }}</p>
                        <a v-if="telegramCode.deep_link" :href="telegramCode.deep_link" target="_blank" class="btn-outline w-full justify-center">
                            <Icon icon="mdi:send" class="w-4 h-4" />
                            Botni ochish
                        </a>
                        <button @click="router.reload({ only: ['telegram'] })" class="text-xs text-gray-400 hover:text-gray-600 underline w-full text-center">
                            Ulangandan so'ng shu yerni yangilash
                        </button>
                    </div>
                </template>
            </div>

            <!-- To'lov muddati (turniket) -->
            <div v-if="schedule && schedule.known" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-2"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <p class="text-sm font-bold text-gray-900">Turniket / oylik to'lov muddati</p>
                <p v-if="schedule.is_compliant" class="text-xs text-green-700 bg-green-50 rounded-lg px-3 py-2">
                    ✅ Bu oy uchun to'lov talabi qondirilgan — turniketda muammo bo'lmaydi.
                </p>
                <p v-else class="text-xs text-red-700 bg-red-50 rounded-lg px-3 py-2">
                    ❌ Qarz: <strong>{{ formatPrice(schedule.debt_amount) }}</strong> — muddatida to'lanmasa turniketdan o'tishda muammo bo'lishi mumkin.
                </p>
                <!-- "is_compliant" faqat O'TGAN muddat bo'yicha; masalan
                     1-muddatdan OLDIN u har doim true bo'ladi, lekin talaba
                     KELAYOTGAN muddatgacha hali yetarli to'lamagan bo'lishi
                     mumkin — shu holatni alohida ko'rsatamiz. -->
                <p v-if="schedule.is_compliant && schedule.is_compliant_for_next === false"
                   class="text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2">
                    ⏳ Keyingi muddatgacha yana <strong>{{ formatPrice(Math.max(0, schedule.next_required_amount - schedule.paid_amount)) }}</strong> to'lashingiz kerak.
                </p>
                <p v-if="schedule.next_deadline" class="text-xs text-gray-400">
                    Keyingi muddat: {{ formatDate(schedule.next_deadline) }} (har oyning 20-sanasi)
                </p>
            </div>

            <!-- Shartnoma yo'q -->
            <div v-if="!contract"
                 class="bg-white rounded-2xl border border-gray-100 p-16 text-center text-gray-400"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <Icon icon="mdi:file-document-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                <p class="text-sm">Sizga biriktirilgan shartnoma topilmadi</p>
                <p class="text-xs text-gray-400 mt-1">Agar siz grant asosida o'qiyotgan bo'lsangiz, shartnoma talab qilinmaydi</p>
            </div>

            <template v-else>
                <!-- Shartnoma ma'lumotlari -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="flex items-start justify-between flex-wrap gap-3">
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Shartnoma raqami</p>
                            <p class="text-lg font-bold text-gray-900">{{ contract.contract_number }}</p>
                        </div>
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold" :class="statusClass(contract.status)">
                            {{ statusLabel(contract.status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                        <div v-if="contract.direction">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Yo'nalish</p>
                            <p class="text-sm font-semibold text-gray-900">{{ contract.direction.name_uz }}</p>
                            <p v-if="contract.direction.faculty" class="text-xs text-gray-400 mt-0.5">{{ contract.direction.faculty }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">To'lov turi</p>
                            <p class="text-sm font-semibold text-gray-900">{{ paymentTypeLabel(contract.payment_type) }}</p>
                        </div>
                        <div v-if="contract.signed_at">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Imzolangan sana</p>
                            <p class="text-sm font-semibold text-gray-900">{{ formatDate(contract.signed_at) }}</p>
                        </div>
                    </div>

                    <a :href="route('admin.my-contract.pdf', contract.id)" class="btn-outline">
                        <Icon icon="mdi:file-pdf-box" class="w-4 h-4" />
                        Shartnomani yuklab olish (PDF)
                    </a>
                </div>

                <!-- To'lov holati -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <p class="text-sm font-bold text-gray-900">To'lov holati</p>

                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-400 mb-1">Jami summa</p>
                            <p class="text-sm font-bold text-gray-900">{{ formatPrice(contract.amount) }}</p>
                        </div>
                        <div class="bg-green-50 rounded-xl p-3">
                            <p class="text-xs text-green-600 mb-1">To'langan</p>
                            <p class="text-sm font-bold text-green-700">{{ formatPrice(contract.paid_amount) }}</p>
                        </div>
                        <div class="bg-amber-50 rounded-xl p-3">
                            <p class="text-xs text-amber-600 mb-1">Qolgan</p>
                            <p class="text-sm font-bold text-amber-700">{{ formatPrice(contract.remaining_amount) }}</p>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>To'langan qismi</span>
                            <span class="font-semibold">{{ contract.paid_percent }}%</span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full" :style="`width:${contract.paid_percent}%; background: linear-gradient(135deg,#0f3460,#533483)`" />
                        </div>
                    </div>
                </div>

                <!-- To'lov qilish -->
                <div v-if="canPay" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <p class="text-sm font-bold text-gray-900">To'lov qilish</p>
                    <p class="text-xs text-gray-500">Click yoki Payme orqali qarzingizni to'liq yoki qisman to'lashingiz mumkin.</p>

                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1 block">To'lov summasi</label>
                        <input v-model.number="payForm.amount" type="number" min="1000" :max="contract.remaining_amount" step="1000"
                               class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none" />
                        <p v-if="payForm.errors.amount" class="text-xs text-red-600 mt-1">{{ payForm.errors.amount }}</p>
                        <p class="text-xs text-gray-400 mt-1">Maksimal: {{ formatPrice(contract.remaining_amount) }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button @click="buy('click')" :disabled="buying" class="btn-pay" style="background:#00aaff">
                            <Icon v-if="buying === 'click'" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                            <span v-else>Click orqali to'lash</span>
                        </button>
                        <button @click="buy('payme')" :disabled="buying" class="btn-pay" style="background:#00cdba">
                            <Icon v-if="buying === 'payme'" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                            <span v-else>Payme orqali to'lash</span>
                        </button>
                    </div>
                    <p class="hint">To'lov tizimining o'z sahifasiga yo'naltirilasiz. To'lov tasdiqlangach, sahifaga qaytib holatni ko'rishingiz mumkin bo'ladi.</p>
                </div>

                <!-- To'lovlar tarixi -->
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="p-6 pb-0">
                        <p class="text-sm font-bold text-gray-900">To'lovlar tarixi</p>
                    </div>

                    <div v-if="!contract.payments.length" class="p-10 text-center text-gray-400">
                        <Icon icon="mdi:cash-remove" class="w-8 h-8 mx-auto mb-2 opacity-40" />
                        <p class="text-sm">Hozircha to'lov qayd etilmagan</p>
                    </div>

                    <div v-else class="divide-y divide-gray-100 mt-4">
                        <div v-for="p in contract.payments" :key="p.id" class="flex items-center justify-between gap-3 px-6 py-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900">{{ formatPrice(p.amount) }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ formatDate(p.paid_at || p.created_at) }} · {{ providerLabel(p.provider) }}
                                </p>
                            </div>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold flex-shrink-0" :class="paymentStatusClass(p.status)">
                                {{ paymentStatusLabel(p.status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </template>

        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    contract: { type: Object, default: null },
    telegram: { type: Object, default: () => ({ linked: false, bot_username: null }) },
    schedule: { type: Object, default: null },
})

// Telegram ulash kodi — generateTelegramCode() flash orqali qaytaradi
// (Inertia'ning odatiy 'success'/'error' xabarlaridan farqli, o'z alohida
// flash kaliti — chunki kodni ekranda ko'rsatib turish kerak, oddiy toastr
// xabari kabi darhol g'oyib bo'lmasligi kerak).
const page = usePage()
const telegramCode = ref(page.props.flash?.telegramCode || null)

const requestingCode = ref(false)
const requestCode = () => {
    requestingCode.value = true
    router.post(route('admin.my-contract.telegram.code'), {}, {
        preserveScroll: true,
        onSuccess: () => { telegramCode.value = page.props.flash?.telegramCode || null },
        onFinish: () => { requestingCode.value = false },
    })
}

const unlinking = ref(false)
const unlinkTelegram = () => {
    unlinking.value = true
    router.post(route('admin.my-contract.telegram.unlink'), {}, {
        preserveScroll: true,
        onFinish: () => { unlinking.value = false },
    })
}

const formatPrice = (v) => new Intl.NumberFormat('uz-UZ').format(v || 0) + " so'm"
const formatDate = (v) => v ? new Date(v).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—'

const statusLabel = (v) => ({ draft: 'Qoralama', signed: 'Imzolangan', paid: "To'langan", cancelled: 'Bekor qilingan' }[v] || v)
const statusClass = (v) => ({
    draft:     'bg-gray-100 text-gray-500',
    signed:    'bg-blue-50 text-blue-700',
    paid:      'bg-green-50 text-green-700',
    cancelled: 'bg-red-50 text-red-600',
}[v] || 'bg-gray-100 text-gray-500')

const paymentTypeLabel = (v) => ({ grant: 'Grant', contract: 'Kontrakt' }[v] || v)
const providerLabel = (v) => ({ click: 'Click', payme: 'Payme', cash: 'Naqd' }[v] || v)

const paymentStatusLabel = (v) => ({ pending: 'Kutilmoqda', paid: "To'landi", failed: 'Muvaffaqiyatsiz', refunded: 'Qaytarildi' }[v] || v)
const paymentStatusClass = (v) => ({
    pending:  'bg-amber-50 text-amber-700',
    paid:     'bg-green-50 text-green-700',
    failed:   'bg-red-50 text-red-600',
    refunded: 'bg-gray-100 text-gray-500',
}[v] || 'bg-gray-100 text-gray-500')

// Kontraktga onlayn to'lov — Kitob/Kurs sotib olishdagi bilan bir xil
// naqsh (router orqali checkout -> Inertia::location() -> gateway).
const canPay = computed(() => !!props.contract && props.contract.status !== 'cancelled' && props.contract.remaining_amount > 0)

const payForm = useForm({
    amount: props.contract?.remaining_amount || 0,
})

const buying = ref(null)
const buy = (provider) => {
    if (!payForm.amount || payForm.amount <= 0) return
    buying.value = provider
    payForm.post(route('admin.my-contract.payment.checkout', provider), {
        onFinish: () => { buying.value = null },
    })
}
</script>

<style scoped>
.btn-outline {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.55rem 1.1rem;
    border-radius: 0.75rem;
    border: 1px solid #e5e7eb;
    color: #0f3460;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-outline:hover { background: #f9fafb; border-color: #0f3460; }
.btn-outline-danger {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.55rem 1.1rem;
    border-radius: 0.75rem;
    border: 1px solid #fecaca;
    color: #dc2626;
    font-size: 0.8rem;
    font-weight: 600;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-outline-danger:hover { background: #fef2f2; }
.btn-outline-danger:disabled { opacity: 0.6; cursor: not-allowed; }
.hint { color: #9ca3af; font-size: 0.7rem; }
.btn-pay {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.7rem 1.25rem;
    border-radius: 0.75rem;
    color: white;
    font-size: 0.875rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: opacity 0.2s;
}
.btn-pay:hover { opacity: 0.9; }
.btn-pay:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
