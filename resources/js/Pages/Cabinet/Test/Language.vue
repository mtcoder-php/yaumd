<template>
    <div class="min-h-screen flex items-center justify-center p-4"
         style="background: linear-gradient(135deg, #0f3460 0%, #16213e 50%, #533483 100%)">

        <div class="w-full max-w-md">

            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4"
                     style="background: rgba(255,255,255,0.15)">
                    <Icon icon="mdi:translate" class="w-7 h-7 text-white" />
                </div>
                <h1 class="text-xl font-bold text-white">Til tanlash</h1>
                <p class="text-sm text-white/60 mt-1">{{ applicant.name }}</p>
                <p class="text-xs text-white/40 mt-0.5">{{ applicant.direction }}</p>
            </div>

            <!-- Card -->
            <div class="rounded-2xl p-6 space-y-6"
                 style="background: rgba(255,255,255,0.1); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.2)">

                <!-- Test tili -->
                <div>
                    <p class="text-sm font-semibold text-white mb-3">Test tili</p>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            v-for="lang in languages"
                            :key="lang.value"
                            @click="form.language = lang.value"
                            class="flex flex-col items-center gap-2 py-4 rounded-xl border-2 transition-all"
                            :style="form.language === lang.value
                                ? 'border-color:white; background:rgba(255,255,255,0.2)'
                                : 'border-color:rgba(255,255,255,0.2); background:rgba(255,255,255,0.05)'"
                        >
                            <span class="text-2xl">{{ lang.flag }}</span>
                            <span class="text-sm font-semibold text-white">{{ lang.label }}</span>
                            <div v-if="form.language === lang.value"
                                 class="w-5 h-5 rounded-full flex items-center justify-center"
                                 style="background: white">
                                <Icon icon="mdi:check" class="w-3 h-3" style="color:#0f3460" />
                            </div>
                        </button>
                    </div>
                    <p v-if="errors.language" class="text-red-400 text-xs mt-2">{{ errors.language }}</p>
                </div>

                <!-- Xorijiy til -->
                <div>
                    <p class="text-sm font-semibold text-white mb-3">Xorijiy til</p>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            v-for="lang in foreignLangs"
                            :key="lang.value"
                            @click="form.foreign_lang = lang.value"
                            class="flex flex-col items-center gap-2 py-4 rounded-xl border-2 transition-all"
                            :style="form.foreign_lang === lang.value
                                ? 'border-color:white; background:rgba(255,255,255,0.2)'
                                : 'border-color:rgba(255,255,255,0.2); background:rgba(255,255,255,0.05)'"
                        >
                            <span class="text-2xl">{{ lang.flag }}</span>
                            <span class="text-sm font-semibold text-white">{{ lang.label }}</span>
                            <div v-if="form.foreign_lang === lang.value"
                                 class="w-5 h-5 rounded-full flex items-center justify-center"
                                 style="background: white">
                                <Icon icon="mdi:check" class="w-3 h-3" style="color:#0f3460" />
                            </div>
                        </button>
                    </div>
                    <p v-if="errors.foreign_lang" class="text-red-400 text-xs mt-2">{{ errors.foreign_lang }}</p>
                </div>

                <!-- Diqqat -->
                <div class="p-3 rounded-xl" style="background: rgba(255,165,0,0.15); border: 1px solid rgba(255,165,0,0.3)">
                    <p class="text-xs text-orange-300 flex items-start gap-2">
                        <Icon icon="mdi:alert-outline" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5" />
                        Til tanlagandan so'ng o'zgartirib bo'lmaydi. Diqqat bilan tanlang!
                    </p>
                </div>

                <!-- Submit -->
                <button
                    @click="submit"
                    :disabled="form.processing || !form.language || !form.foreign_lang"
                    class="w-full py-3 rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2"
                    style="background: white; color: #0f3460"
                    :class="(form.processing || !form.language || !form.foreign_lang)
                        ? 'opacity-50 cursor-not-allowed'
                        : 'hover:bg-white/90'"
                >
                    <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                    <Icon v-else icon="mdi:arrow-right" class="w-4 h-4" />
                    {{ form.processing ? 'Yuklanmoqda...' : 'Testni boshlash' }}
                </button>

            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const props = defineProps({
    session:   { type: Object, required: true },
    applicant: { type: Object, required: true },
})

const page   = usePage()
const errors = computed(() => page.props.errors || {})

const form = useForm({
    language:     props.session.language     || '',
    foreign_lang: props.session.foreign_lang || '',
})

const languages = [
    { value: 'uz', label: "O'zbek tili", flag: '🇺🇿' },
    { value: 'ru', label: 'Rus tili',    flag: '🇷🇺' },
]

const foreignLangs = [
    { value: 'en', label: 'Ingliz tili', flag: '🇬🇧' },
    { value: 'ar', label: 'Arab tili',   flag: '🇸🇦' },
]

const submit = () => {
    form.post(route('cabinet.test.language.set'))
}
</script>
