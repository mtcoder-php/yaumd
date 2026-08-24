<template>
    <div class="min-h-screen flex items-center justify-center p-4" style="background: #f8fafc">

        <div class="w-full max-w-lg">

            <!-- Result card -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 4px 24px rgba(0,0,0,0.08)">

                <!-- Top banner -->
                <div class="p-8 text-center"
                     :style="isPassed
                        ? 'background: linear-gradient(135deg, #f0fdf4, #dcfce7)'
                        : 'background: linear-gradient(135deg, #fef2f2, #fee2e2)'">

                    <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4"
                         :style="isPassed
                            ? 'background: linear-gradient(135deg, #22c55e, #16a34a)'
                            : 'background: linear-gradient(135deg, #ef4444, #dc2626)'">
                        <Icon
                            :icon="isPassed ? 'mdi:trophy-outline' : 'mdi:close-circle-outline'"
                            class="w-10 h-10 text-white"
                        />
                    </div>

                    <h1 class="text-2xl font-bold mb-1"
                        :style="isPassed ? 'color:#16a34a' : 'color:#dc2626'">
                        {{ isPassed ? 'Tabriklaymiz!' : 'Afsuski...' }}
                    </h1>
                    <p class="text-sm"
                       :style="isPassed ? 'color:#15803d' : 'color:#b91c1c'">
                        {{ isPassed ? 'Test muvaffaqiyatli topshirildi!' : 'Test muvaffaqiyatsiz yakunlandi' }}
                    </p>
                </div>

                <!-- Ball -->
                <div class="px-8 py-6 border-b border-gray-100 text-center">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Jami ball</p>
                    <p class="text-5xl font-bold"
                       :style="isPassed ? 'color:#16a34a' : 'color:#dc2626'">
                        {{ session.score }}
                    </p>
                    <p class="text-gray-400 text-sm mt-1">/ 189 ball</p>

                    <!-- Progress bar -->
                    <div class="mt-4 w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div
                            class="h-full rounded-full transition-all duration-1000"
                            :style="{
                                width: ((session.score / 189) * 100) + '%',
                                background: isPassed
                                    ? 'linear-gradient(135deg, #22c55e, #16a34a)'
                                    : 'linear-gradient(135deg, #ef4444, #dc2626)'
                            }"
                        ></div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 divide-x divide-gray-100 border-b border-gray-100">
                    <div class="text-center py-5">
                        <p class="text-2xl font-bold text-green-600">{{ session.correct_answers }}</p>
                        <p class="text-xs text-gray-400 mt-1">To'g'ri</p>
                    </div>
                    <div class="text-center py-5">
                        <p class="text-2xl font-bold text-red-500">{{ wrongAnswers }}</p>
                        <p class="text-xs text-gray-400 mt-1">Noto'g'ri</p>
                    </div>
                    <div class="text-center py-5">
                        <p class="text-2xl font-bold text-gray-400">{{ skippedAnswers }}</p>
                        <p class="text-xs text-gray-400 mt-1">O'tkazilgan</p>
                    </div>
                </div>

                <!-- Info -->
                <div class="px-6 py-5 space-y-3">
                    <div class="flex items-center justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500 flex items-center gap-2">
                            <Icon icon="mdi:account-outline" class="w-4 h-4 text-gray-400" />
                            Abituriyent
                        </span>
                        <span class="text-sm font-semibold text-gray-800">
                            {{ session.applicant?.last_name }} {{ session.applicant?.first_name }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500 flex items-center gap-2">
                            <Icon icon="mdi:school-outline" class="w-4 h-4 text-gray-400" />
                            Yo'nalish
                        </span>
                        <span class="text-sm font-semibold text-gray-800">{{ session.direction?.name_uz }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500 flex items-center gap-2">
                            <Icon icon="mdi:translate" class="w-4 h-4 text-gray-400" />
                            Test tili
                        </span>
                        <span class="text-sm font-semibold text-gray-800">
                            {{ session.language === 'uz' ? "O'zbek tili" : 'Rus tili' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-500 flex items-center gap-2">
                            <Icon icon="mdi:clock-check-outline" class="w-4 h-4 text-gray-400" />
                            Yakunlangan
                        </span>
                        <span class="text-sm font-semibold text-gray-800">{{ formatDate(session.finished_at) }}</span>
                    </div>
                </div>

                <!-- Logout -->
                <div class="px-6 pb-6">
                    <button
                        @click="logout"
                        class="w-full py-3 rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2 border border-gray-200 text-gray-600 hover:bg-gray-50"
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

const isPassed = computed(() => (props.session.score || 0) >= 56)

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
