<template>
    <WebLayout>
        <div class="min-h-[70vh] flex items-center justify-center py-16 px-4">
            <div class="max-w-md w-full text-center">

                <!-- Animatsiyali check -->
                <div class="w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 success-circle">
                    <Icon icon="mdi:check" class="w-12 h-12 text-white" />
                </div>

                <h1 class="text-2xl font-bold text-gray-900 mb-3">
                    Ariza muvaffaqiyatli yuborildi!
                </h1>
                <p class="text-gray-500 text-sm mb-6 leading-relaxed">
                    Arizangiz qabul qilindi. Qabul komissiyasi siz bilan
                    tez orada bog'lanadi.
                </p>

                <!-- Ariza raqami -->
                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 mb-8">
                    <p class="text-xs text-gray-400 mb-1">Ariza raqami</p>
                    <p class="text-2xl font-bold" style="color: #0f3460">{{ number }}</p>
                    <p class="text-xs text-gray-400 mt-2">
                        Ushbu raqamni saqlang — holatingizni kuzatish uchun kerak bo'ladi
                    </p>
                </div>

                <!-- Keyingi qadamlar -->
                <div class="text-left space-y-3 mb-8">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Keyingi qadamlar:</p>
                    <div v-for="(step, i) in nextSteps" :key="i" class="flex items-start gap-3">
                        <div
                            class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 text-white text-xs font-bold mt-0.5"
                            style="background: linear-gradient(135deg, #0f3460, #533483)"
                        >
                            {{ i + 1 }}
                        </div>
                        <p class="text-sm text-gray-600">{{ step }}</p>
                    </div>
                </div>

                <!-- Tugmalar -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <Link
                        href="/"
                        class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition text-center"
                    >
                        Bosh sahifaga
                    </Link>
                    <Link
                        href="/qabul/ariza"
                        class="flex-1 py-2.5 rounded-xl text-white text-sm font-semibold transition hover:shadow-lg text-center"
                        style="background: linear-gradient(135deg, #0f3460, #533483)"
                    >
                        Yangi ariza
                    </Link>
                </div>

            </div>
        </div>
    </WebLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import WebLayout from '@/Layouts/WebLayout.vue'

defineProps({
    number: { type: String, default: '' },
})

const nextSteps = [
    "Telefon raqamingizga SMS xabarnoma yuboriladi",
    "Qabul komissiyasi 1-2 ish kuni ichida siz bilan bog'lanadi",
    "Suhbat sanasi va vaqti belgilanadi",
    "Suhbat natijasiga ko'ra test va kontrakt bosqichi",
]
</script>

<style scoped>
.success-circle {
    background: linear-gradient(135deg, #0f3460, #533483);
    animation: scaleIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 8px 32px rgba(15, 52, 96, 0.35);
}

@keyframes scaleIn {
    from { transform: scale(0); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
}
</style>
