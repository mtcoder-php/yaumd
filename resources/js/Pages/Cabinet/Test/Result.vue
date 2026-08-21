<template>
    <div class="min-h-screen flex items-center justify-center p-4"
         style="background: linear-gradient(135deg, #0f3460 0%, #16213e 50%, #533483 100%)">

        <div class="w-full max-w-lg">

            <!-- Natija card -->
            <div class="rounded-2xl overflow-hidden"
                 style="background: rgba(255,255,255,0.1); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.2)">

                <!-- Top -->
                <div class="p-8 text-center border-b" style="border-color: rgba(255,255,255,0.15)">
                    <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4"
                         :style="isPassed
                            ? 'background: linear-gradient(135deg, #22c55e, #16a34a)'
                            : 'background: linear-gradient(135deg, #ef4444, #dc2626)'">
                        <Icon
                            :icon="isPassed ? 'mdi:trophy' : 'mdi:close-circle'"
                            class="w-10 h-10 text-white"
                        />
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-1">
                        {{ isPassed ? 'Tabriklaymiz!' : 'Afsuski...' }}
                    </h1>
                    <p class="text-white/60 text-sm">
                        {{ isPassed ? "Test muvaffaqiyatli topshirildi!" : "Test muvaffaqiyatsiz yakunlandi" }}
                    </p>
                </div>

                <!-- Ball -->
                <div class="p-6">
                    <div class="text-center mb-6">
                        <p class="text-white/60 text-xs uppercase tracking-wider mb-1">Jami ball</p>
                        <p class="text-5xl font-bold text-white">{{ session.score }}</p>
                        <p class="text-white/40 text-sm mt-1">/ 189 ball</p>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-3 mb-6">
                        <div class="text-center p-3 rounded-xl" style="background: rgba(255,255,255,0.08)">
                            <p class="text-2xl font-bold text-green-400">{{ session.correct_answers }}</p>
                            <p class="text-xs text-white/50 mt-1">To'g'ri</p>
                        </div>
                        <div class="text-center p-3 rounded-xl" style="background: rgba(255,255,255,0.08)">
                            <p class="text-2xl font-bold text-red-400">{{ wrongAnswers }}</p>
                            <p class="text-xs text-white/50 mt-1">Noto'g'ri</p>
                        </div>
                        <div class="text-center p-3 rounded-xl" style="background: rgba(255,255,255,0.08)">
                            <p class="text-2xl font-bold text-gray-400">{{ skippedAnswers }}</p>
                            <p class="text-xs text-white/50 mt-1">O'tkazilgan</p>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between py-2.5 border-b"
                             style="border-color: rgba(255,255,255,0.1)">
                            <span class="text-sm text-white/60">Abituriyent</span>
                            <span class="text-sm font-semibold text-white">
                                {{ session.applicant?.last_name }} {{ session.applicant?.first_name }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-2.5 border-b"
                             style="border-color: rgba(255,255,255,0.1)">
                            <span class="text-sm text-white/60">Yo'nalish</span>
                            <span class="text-sm font-semibold text-white">{{ session.direction?.name_uz }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2.5 border-b"
                             style="border-color: rgba(255,255,255,0.1)">
                            <span class="text-sm text-white/60">Test tili</span>
                            <span class="text-sm font-semibold text-white">
                                {{ session.language === 'uz' ? "O'zbek tili" : 'Rus tili' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-2.5">
                            <span class="text-sm text-white/60">Yakunlangan vaqt</span>
                            <span class="text-sm font-semibold text-white">{{ formatDate(session.finished_at) }}</span>
                        </div>
                    </div>

                    <!-- Logout -->
                    <button
                        @click="logout"
                        class="w-full mt-6 py-3 rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2"
                        style="background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.2)"
                    >
                        <Icon icon="mdi:logout" class="w-4 h-4" />
                        Chiqish
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const props = defineProps({
    session:   { type: Object, required: true },
    questions: { type: Array,  default: () => [] },
    answers:   { type: Object, default: () => ({}) },
})

const isPassed = computed(() => (props.session.score || 0) >= 56) // 56 ball — minimal

const wrongAnswers = computed(() => {
    let wrong = 0
    props.questions.forEach(q => {
        const userAnswer = props.answers[q.id]
        if (userAnswer && userAnswer !== q.correct_answer) wrong++
    })
    return wrong
})

const skippedAnswers = computed(() =>
    props.questions.length - Object.keys(props.answers).length
)

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleString('uz-UZ', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}

const logout = () => {
    router.post(route('cabinet.logout'))
}
</script>
