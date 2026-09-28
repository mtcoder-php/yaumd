<template>
    <AppLayout :title="book.title">
        <div class="max-w-3xl mx-auto space-y-5">

            <div class="flex items-center gap-4">
                <Link :href="route('admin.my-library.index')"
                      class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <h1 class="text-xl font-bold text-gray-900">{{ book.title }}</h1>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6 flex flex-col sm:flex-row gap-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="w-32 h-44 rounded-xl bg-gray-100 overflow-hidden flex items-center justify-center flex-shrink-0 mx-auto sm:mx-0">
                    <img v-if="book.cover_image_url" :src="book.cover_image_url" class="w-full h-full object-cover" alt="">
                    <Icon v-else icon="mdi:book-outline" class="w-10 h-10 text-gray-300" />
                </div>

                <div class="flex-1 space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">{{ book.author }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ book.category?.name_uz || 'Kategoriyasiz' }}</p>
                    </div>

                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold"
                          :class="book.available_copies_count > 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600'">
                        <Icon :icon="book.available_copies_count > 0 ? 'mdi:check-circle-outline' : 'mdi:close-circle-outline'" class="w-4 h-4 mr-1" />
                        {{ book.available_copies_count > 0
                        ? `${book.available_copies_count} / ${book.copies_count} nusxa bo'sh`
                        : "Hozircha barcha nusxalar band" }}
                    </span>

                    <!-- Band qilish (rezervatsiya) — faqat bo'sh nusxa qolmaganda ko'rinadi -->
                    <div v-if="book.available_copies_count === 0" class="rounded-xl border p-3"
                         :class="myReservation ? 'border-brand-200 bg-brand-50' : 'border-gray-100 bg-gray-50'">
                        <template v-if="!myReservation">
                            <p class="text-sm text-gray-600 mb-2">Hozir bo'sh nusxa yo'q. Navbatga tursangiz, bo'shashi bilan Telegram orqali xabar beramiz.</p>
                            <button @click="reserve" :disabled="reserving" class="btn-primary !py-2">
                                <Icon v-if="reserving" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                                <Icon v-else icon="mdi:bookmark-plus-outline" class="w-4 h-4" />
                                {{ reserving ? 'Yuborilmoqda...' : 'Navbatga turish' }}
                            </button>
                        </template>
                        <template v-else-if="myReservation.status === 'waiting'">
                            <p class="text-sm font-semibold text-brand-700 flex items-center gap-1.5">
                                <Icon icon="mdi:clock-outline" class="w-4 h-4" />
                                Siz navbatdasiz — {{ myReservation.queue_position }}-o'rin
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Kitob bo'shashi bilan Telegram orqali xabar beramiz.</p>
                            <button @click="cancelReservation" :disabled="cancelling" class="text-xs text-red-500 hover:text-red-600 font-semibold mt-2">
                                {{ cancelling ? 'Bekor qilinmoqda...' : 'Navbatni bekor qilish' }}
                            </button>
                        </template>
                        <template v-else-if="myReservation.status === 'ready'">
                            <p class="text-sm font-semibold text-green-700 flex items-center gap-1.5">
                                <Icon icon="mdi:check-circle-outline" class="w-4 h-4" />
                                Sizga tayyor!
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                <strong>{{ formatDateTime(myReservation.expires_at) }}</strong>gacha kutubxonaga kelib oling, aks holda band bekor bo'ladi.
                            </p>
                            <button @click="cancelReservation" :disabled="cancelling" class="text-xs text-red-500 hover:text-red-600 font-semibold mt-2">
                                {{ cancelling ? 'Bekor qilinmoqda...' : 'Kerak emas, bekor qilish' }}
                            </button>
                        </template>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div v-if="book.publisher">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Nashriyot</p>
                            <p class="text-sm font-semibold text-gray-900">{{ book.publisher }}</p>
                        </div>
                        <div v-if="book.published_year">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Nashr yili</p>
                            <p class="text-sm font-semibold text-gray-900">{{ book.published_year }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Til</p>
                            <p class="text-sm font-semibold text-gray-900">{{ languageLabel(book.language) }}</p>
                        </div>
                        <div v-if="book.page_count">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Sahifalar</p>
                            <p class="text-sm font-semibold text-gray-900">{{ book.page_count }}</p>
                        </div>
                        <div v-if="book.shelf_location">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Joylashuvi</p>
                            <p class="text-sm font-semibold text-gray-900">{{ book.shelf_location }}</p>
                        </div>
                        <div v-if="book.isbn">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">ISBN</p>
                            <p class="text-sm font-semibold text-gray-900">{{ book.isbn }}</p>
                        </div>
                    </div>

                    <div v-if="book.description">
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Tavsif</p>
                        <div class="text-sm text-gray-700 prose prose-sm max-w-none" v-html="book.description"></div>
                    </div>

                    <p class="text-xs text-gray-400 pt-2 border-t border-gray-100">
                        Fizik nusxani o'qish uchun kutubxonaga tashrif buyuring va kutubxonachidan shu nusxani so'rang.
                    </p>
                </div>
            </div>

            <!-- Elektron nusxa -->
            <div v-if="book.has_digital_file" class="bg-white rounded-2xl border border-gray-100 p-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="flex items-center gap-2 mb-4">
                    <Icon icon="mdi:file-download-outline" class="w-5 h-5 text-gray-500" />
                    <p class="text-sm font-bold text-gray-900">Elektron nusxa</p>
                </div>

                <!-- Yuklab olish tugmasi: bepul kitob YOKI allaqachon sotib olingan -->
                <div v-if="hasDigitalAccess" class="flex items-center justify-between gap-4 flex-wrap">
                    <p class="text-sm text-gray-600">
                        {{ book.access_type === 'paid' ? "Bu kitobni sotib olgansiz — istalgancha yuklab olishingiz mumkin." : "Bu kitob bepul — hoziroq yuklab oling." }}
                    </p>
                    <a :href="route('admin.my-library.download', book.id)" class="btn-primary">
                        <Icon icon="mdi:download-outline" class="w-4 h-4" />
                        Yuklab olish
                    </a>
                </div>

                <!-- Sotib olish: pullik va hali sotib olinmagan -->
                <div v-else class="space-y-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600">Bu kitobning elektron nusxasini yuklab olish uchun sotib oling</p>
                        <span class="text-lg font-bold" style="color:#0f3460">{{ formatPrice(book.price) }}</span>
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
                    <p class="hint">To'lov tizimining o'z sahifasiga yo'naltirilasiz. To'lov tasdiqlangach, sahifaga qaytib yuklab olishingiz mumkin bo'ladi.</p>
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
    book:             { type: Object, required: true },
    hasDigitalAccess: { type: Boolean, default: false },
    myReservation:    { type: Object, default: null },
})

const languageLabel = (v) => ({ uz: "O'zbek", ru: 'Rus', en: 'Ingliz' }[v] || v || '—')
const formatPrice = (v) => new Intl.NumberFormat('uz-UZ').format(v) + " so'm"

// Band qilish "tayyor" xabaridagi muddat — aniq kun VA soat kerak
// bo'lgani uchun (oddiy DD.MM.YYYY yetarli emas), LibraryBooks/Show.vue'dagi
// bilan bir xil sababdan 'toLocaleDateString' ATAYLAB ishlatilmaydi —
// brauzerning "uz-UZ" lokali ko'pincha YYYY-MM-DD tartibini qaytaradi.
// Bu yerda 'getUTCDate()' emas, oddiy 'getDate()' ishlatiladi — chunki
// bu haqiqiy vaqt tamg'asi (soat bilan), backend uni serverning mahalliy
// vaqt zonasida saqlaydi (calendar-only sana emas).
const formatDateTime = (iso) => {
    if (!iso) return '—'
    const d = new Date(iso)
    const day = String(d.getDate()).padStart(2, '0')
    const month = String(d.getMonth() + 1).padStart(2, '0')
    const year = d.getFullYear()
    const hour = String(d.getHours()).padStart(2, '0')
    const minute = String(d.getMinutes()).padStart(2, '0')
    return `${day}.${month}.${year}, ${hour}:${minute}`
}

const reserving = ref(false)
const reserve = () => {
    reserving.value = true
    router.post(route('admin.my-library.reserve', props.book.id), {}, {
        preserveScroll: true,
        onFinish: () => { reserving.value = false },
    })
}

const cancelling = ref(false)
const cancelReservation = () => {
    cancelling.value = true
    router.post(route('admin.my-library.reservations.cancel', props.myReservation.id), {}, {
        preserveScroll: true,
        onFinish: () => { cancelling.value = false },
    })
}

const buying = ref(null)
const buy = (provider) => {
    buying.value = provider
    router.post(route('admin.my-library.purchase', [props.book.id, provider]), {}, {
        onFinish: () => { buying.value = null },
    })
}
</script>

<style scoped>
.hint { color: #9ca3af; font-size: 0.7rem; }
.btn-primary {
    display: inline-flex;
    align-items: center;
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
