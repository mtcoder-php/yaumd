<template>
    <div class="min-h-screen flex items-center justify-center p-4" style="background: #f8fafc">

        <div class="w-full max-w-lg">

            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4"
                     style="background: linear-gradient(135deg, #0f3460, #533483)">
                    <Icon icon="mdi:translate" class="w-8 h-8 text-white" />
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Til tanlash</h1>
                <p class="text-gray-500 text-sm mt-1">{{ applicant.name }}</p>
                <p class="text-gray-400 text-xs mt-0.5">{{ applicant.direction }}</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-6"
                 style="box-shadow: 0 4px 24px rgba(0,0,0,0.08)">

                <!-- Test tili -->
                <div>
                    <p class="text-sm font-bold text-gray-700 mb-3">Test tili</p>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            v-for="lang in languages"
                            :key="lang.value"
                            @click="form.language = lang.value"
                            class="flex flex-col items-center gap-3 py-5 rounded-xl border-2 transition-all"
                            :style="form.language === lang.value
                                ? 'border-color:#0f3460; background:linear-gradient(135deg,#eff6ff,#f5f3ff)'
                                : 'border-color:#e5e7eb; background:#fafafa'"
                        >
                            <span class="text-3xl">{{ lang.flag }}</span>
                            <span class="text-sm font-semibold"
                                  :style="form.language === lang.value ? 'color:#0f3460' : 'color:#374151'">
                                {{ lang.label }}
                            </span>
                            <div v-if="form.language === lang.value"
                                 class="w-6 h-6 rounded-full flex items-center justify-center"
                                 style="background: linear-gradient(135deg, #0f3460, #533483)">
                                <Icon icon="mdi:check" class="w-3.5 h-3.5 text-white" />
                            </div>
                            <div v-else class="w-6 h-6 rounded-full border-2 border-gray-200"></div>
                        </button>
                    </div>
                    <p v-if="errors.language" class="text-red-500 text-xs mt-2">{{ errors.language }}</p>
                </div>

                <!-- Xorijiy til -->
                <div>
                    <p class="text-sm font-bold text-gray-700 mb-3">Xorijiy til</p>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            v-for="lang in foreignLangs"
                            :key="lang.value"
                            @click="form.foreign_lang = lang.value"
                            class="flex flex-col items-center gap-3 py-5 rounded-xl border-2 transition-all"
                            :style="form.foreign_lang === lang.value
                                ? 'border-color:#0f3460; background:linear-gradient(135deg,#eff6ff,#f5f3ff)'
                                : 'border-color:#e5e7eb; background:#fafafa'"
                        >
                            <span class="text-3xl">{{ lang.flag }}</span>
                            <span class="text-sm font-semibold"
                                  :style="form.foreign_lang === lang.value ? 'color:#0f3460' : 'color:#374151'">
                                {{ lang.label }}
                            </span>
                            <div v-if="form.foreign_lang === lang.value"
                                 class="w-6 h-6 rounded-full flex items-center justify-center"
                                 style="background: linear-gradient(135deg, #0f3460, #533483)">
                                <Icon icon="mdi:check" class="w-3.5 h-3.5 text-white" />
                            </div>
                            <div v-else class="w-6 h-6 rounded-full border-2 border-gray-200"></div>
                        </button>
                    </div>
                    <p v-if="errors.foreign_lang" class="text-red-500 text-xs mt-2">{{ errors.foreign_lang }}</p>
                </div>

                <!-- Diqqat -->
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-100 flex items-start gap-3">
                    <Icon icon="mdi:alert-outline" class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" />
                    <p class="text-xs text-amber-700 leading-relaxed">
                        <strong>Diqqat!</strong> Til tanlagandan so'ng o'zgartirib bo'lmaydi. Diqqat bilan tanlang!
                    </p>
                </div>

                <!-- Submit -->
                <button
                    @click="submit"
                    :disabled="form.processing || !form.language || !form.foreign_lang"
                    class="w-full py-3.5 rounded-xl font-semibold text-sm text-white transition-all flex items-center justify-center gap-2"
                    style="background: linear-gradient(135deg, #0f3460, #533483)"
                    :class="(form.processing || !form.language || !form.foreign_lang)
                        ? 'opacity-50 cursor-not-allowed'
                        : 'hover:shadow-lg hover:shadow-blue-200'"
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
