<template>
    <AppLayout :title="course.title_uz">
        <div class="max-w-3xl mx-auto space-y-5">

            <div class="flex items-center gap-4">
                <Link :href="route('admin.course-catalog.index')"
                      class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <h1 class="text-xl font-bold text-gray-900">{{ course.title_uz }}</h1>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="aspect-video bg-gray-100 overflow-hidden flex items-center justify-center">
                    <img v-if="course.thumbnail_url" :src="course.thumbnail_url" class="w-full h-full object-cover" alt="">
                    <Icon v-else icon="mdi:book-open-page-variant-outline" class="w-12 h-12 text-gray-300" />
                </div>

                <div class="p-6 space-y-4">
                    <p class="text-xs text-gray-400">{{ course.category?.name_uz || 'Kategoriyasiz' }}</p>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Daraja</p>
                            <p class="text-sm font-semibold text-gray-900">{{ levelLabel(course.level) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Til</p>
                            <p class="text-sm font-semibold text-gray-900">{{ languageLabel(course.language) }}</p>
                        </div>
                        <div v-if="course.duration_hours">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Davomiyligi</p>
                            <p class="text-sm font-semibold text-gray-900">{{ course.duration_hours }} soat</p>
                        </div>
                        <div v-if="course.has_certificate">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Sertifikat</p>
                            <p class="text-sm font-semibold text-gray-900">Kurs oxirida beriladi</p>
                        </div>
                    </div>

                    <div v-if="course.description_uz">
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Tavsif</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ course.description_uz }}</p>
                    </div>

                    <div v-if="course.what_you_learn?.length">
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-2">Nimalarni o'rganasiz</p>
                        <ul class="space-y-1.5">
                            <li v-for="(item, i) in course.what_you_learn" :key="i" class="flex items-start gap-2 text-sm text-gray-700">
                                <Icon icon="mdi:check-circle" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" />
                                {{ item }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Yozilish / sotib olish -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <div v-if="isEnrolled" class="flex items-center justify-between gap-4 flex-wrap">
                    <p class="text-sm text-gray-600">Siz bu kursga allaqachon yozilgansiz.</p>
                    <Link :href="route('admin.my-courses.show', course.id)" class="btn-primary">
                        <Icon icon="mdi:play-circle-outline" class="w-4 h-4" />
                        Kurslarimga o'tish
                    </Link>
                </div>

                <div v-else-if="course.type === 'paid'" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600">Bu kursga yozilish uchun sotib oling</p>
                        <span class="text-lg font-bold" style="color:#0f3460">{{ formatPrice(effectivePrice) }}</span>
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
                    <p class="hint">To'lov tizimining o'z sahifasiga yo'naltirilasiz. To'lov tasdiqlangach, kursga avtomatik yozilasiz.</p>
                </div>

                <div v-else class="flex items-center justify-between gap-4 flex-wrap">
                    <p class="text-sm text-gray-600">Bu kurs bepul — hoziroq yozilishingiz mumkin.</p>
                    <button @click="enrollFree" :disabled="enrolling" class="btn-primary">
                        <Icon v-if="enrolling" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                        <span v-else>Bepul yozilish</span>
                    </button>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    course:     { type: Object, required: true },
    isEnrolled: { type: Boolean, default: false },
})

const levelLabel = (v) => ({ beginner: 'Boshlang\'ich', intermediate: "O'rta", advanced: 'Yuqori', expert: 'Ekspert' }[v] || v || '—')
const languageLabel = (v) => ({ uz: "O'zbek", ru: 'Rus', en: 'Ingliz' }[v] || v || '—')
const formatPrice = (v) => new Intl.NumberFormat('uz-UZ').format(v) + " so'm"

const effectivePrice = computed(() => {
    return props.course.discount_price > 0 ? props.course.discount_price : props.course.price
})

const buying = ref(null)
const buy = (provider) => {
    buying.value = provider
    router.post(route('admin.course-catalog.purchase', [props.course.id, provider]), {}, {
        onFinish: () => { buying.value = null },
    })
}

const enrolling = ref(false)
const enrollFree = () => {
    enrolling.value = true
    router.post(route('admin.course-catalog.enroll-free', props.course.id), {}, {
        onFinish: () => { enrolling.value = false },
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
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
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
