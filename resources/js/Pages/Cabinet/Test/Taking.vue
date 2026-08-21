<template>
    <div class="min-h-screen" style="background: #f8fafc">

        <!-- Header -->
        <header class="sticky top-0 z-50 bg-white border-b border-gray-200"
                style="box-shadow: 0 2px 8px rgba(0,0,0,0.08)">
            <div class="max-w-4xl mx-auto px-4 h-14 flex items-center justify-between">

                <!-- Logo + Info -->
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                         style="background: linear-gradient(135deg, #0f3460, #533483)">
                        <Icon icon="mdi:school" class="w-4 h-4 text-white" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-900">Yangi Asr Universiteti</p>
                        <p class="text-xs text-gray-400">
                            {{ answeredCount }}/{{ questions.length }} ta javob berildi
                        </p>
                    </div>
                </div>

                <!-- Timer -->
                <div
                    class="flex items-center gap-2 px-4 py-2 rounded-xl font-mono font-bold text-lg"
                    :class="timeLeft < 300
                        ? 'bg-red-50 text-red-600 animate-pulse'
                        : 'bg-blue-50 text-[#0f3460]'"
                >
                    <Icon icon="mdi:timer-outline" class="w-5 h-5" />
                    {{ formattedTime }}
                </div>

                <!-- Yakunlash -->
                <button
                    @click="confirmFinish = true"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition"
                    style="background: linear-gradient(135deg, #0f3460, #533483)"
                >
                    <Icon icon="mdi:flag-checkered" class="w-4 h-4" />
                    Yakunlash
                </button>
            </div>
        </header>

        <div class="max-w-4xl mx-auto px-4 py-6 grid grid-cols-1 lg:grid-cols-4 gap-5">

            <!-- Savollar navigatsiyasi -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl border border-gray-100 p-4 sticky top-20"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Savollar</p>

                    <!-- Fanlar bo'yicha gruppa -->
                    <div v-for="(group, subjectName) in questionsBySubject" :key="subjectName" class="mb-4">
                        <p class="text-xs font-medium text-gray-400 mb-2">{{ subjectName }}</p>
                        <div class="flex flex-wrap gap-1.5">
                            <button
                                v-for="(q, i) in group"
                                :key="q.id"
                                @click="goToQuestion(q.globalIndex)"
                                class="w-8 h-8 rounded-lg text-xs font-semibold transition-all"
                                :class="currentIndex === q.globalIndex ? 'ring-2 ring-[#0f3460]' : ''"
                                :style="answers[q.id]
                                    ? 'background:#22c55e; color:white'
                                    : currentIndex === q.globalIndex
                                        ? 'background:linear-gradient(135deg,#0f3460,#533483); color:white'
                                        : 'background:#f3f4f6; color:#6b7280'"
                            >
                                {{ q.globalIndex + 1 }}
                            </button>
                        </div>
                    </div>

                    <!-- Progress -->
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                            <span>Bajarildi</span>
                            <span>{{ answeredCount }}/{{ questions.length }}</span>
                        </div>
                        <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all duration-300"
                                style="background: linear-gradient(135deg, #22c55e, #16a34a)"
                                :style="{ width: (answeredCount / questions.length * 100) + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Savol -->
            <div class="lg:col-span-3">
                <div v-if="currentQuestion" class="bg-white rounded-2xl border border-gray-100 p-6"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                    <!-- Savol header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold"
                                  :class="blockBadge(currentQuestion.block_type)">
                                {{ currentQuestion.subject_name }}
                            </span>
                            <span class="text-xs text-gray-400">
                                {{ currentIndex + 1 }}-savol / {{ questions.length }}
                            </span>
                        </div>
                        <span class="text-xs font-semibold text-green-600">
                            {{ currentQuestion.score_per_question }} ball
                        </span>
                    </div>

                    <!-- Savol matni -->
                    <p class="text-base font-medium text-gray-900 mb-6 leading-relaxed">
                        {{ currentQuestion.question }}
                    </p>

                    <!-- Variantlar -->
                    <div class="space-y-3">
                        <button
                            v-for="opt in options"
                            :key="opt.key"
                            @click="selectAnswer(opt.key)"
                            class="w-full flex items-center gap-4 px-5 py-4 rounded-xl border-2 text-left transition-all"
                            :style="answers[currentQuestion.id] === opt.key
                                ? 'border-color:#0f3460; background:linear-gradient(135deg,#eff6ff,#f5f3ff)'
                                : 'border-color:#e5e7eb; background:#fafafa'"
                        >
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0 transition-all"
                                :style="answers[currentQuestion.id] === opt.key
                                    ? 'background:linear-gradient(135deg,#0f3460,#533483); color:white'
                                    : 'background:#e5e7eb; color:#6b7280'"
                            >
                                {{ opt.key.toUpperCase() }}
                            </div>
                            <span class="text-sm"
                                  :style="answers[currentQuestion.id] === opt.key
                                    ? 'color:#0f3460; font-weight:600'
                                    : 'color:#374151'">
                                {{ currentQuestion['option_' + opt.key] }}
                            </span>
                        </button>
                    </div>

                    <!-- Navigatsiya -->
                    <div class="flex items-center justify-between mt-6 pt-5 border-t border-gray-100">
                        <button
                            v-if="currentIndex > 0"
                            @click="currentIndex--"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50 transition"
                        >
                            <Icon icon="mdi:arrow-left" class="w-4 h-4" />
                            Oldingi
                        </button>
                        <div v-else></div>

                        <button
                            v-if="currentIndex < questions.length - 1"
                            @click="currentIndex++"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition"
                            style="background: linear-gradient(135deg, #0f3460, #533483)"
                        >
                            Keyingi
                            <Icon icon="mdi:arrow-right" class="w-4 h-4" />
                        </button>
                        <button
                            v-else
                            @click="confirmFinish = true"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition"
                            style="background: linear-gradient(135deg, #22c55e, #16a34a)"
                        >
                            <Icon icon="mdi:flag-checkered" class="w-4 h-4" />
                            Yakunlash
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Yakunlash modal -->
        <div
            v-if="confirmFinish"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background: rgba(0,0,0,0.6)"
        >
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4"
                     style="background: linear-gradient(135deg, #0f3460, #533483)">
                    <Icon icon="mdi:flag-checkered" class="w-7 h-7 text-white" />
                </div>
                <h3 class="text-lg font-bold text-gray-900 text-center mb-2">Testni yakunlash</h3>
                <p class="text-sm text-gray-500 text-center mb-2">
                    {{ answeredCount }} ta savolga javob berdingiz
                </p>
                <p v-if="answeredCount < questions.length" class="text-xs text-orange-600 text-center mb-4">
                    ⚠️ {{ questions.length - answeredCount }} ta savol javobsiz qoldi!
                </p>
                <div class="flex gap-3 mt-5">
                    <button @click="confirmFinish = false"
                            class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">
                        Davom etish
                    </button>
                    <button @click="finish"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white"
                            style="background: linear-gradient(135deg, #0f3460, #533483)">
                        Yakunlash
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import axios from 'axios'

const props = defineProps({
    session:   { type: Object, required: true },
    questions: { type: Array,  default: () => [] },
    answers:   { type: Object, default: () => ({}) },
})

const currentIndex  = ref(0)
const answers       = ref({ ...props.answers })
const confirmFinish = ref(false)
const timeLeft      = ref(0)
let timer           = null

// Vaqtni hisoblash
const calculateTimeLeft = () => {
    const expires = new Date(props.session.expires_at).getTime()
    const now     = Date.now()
    return Math.max(0, Math.floor((expires - now) / 1000))
}

const formattedTime = computed(() => {
    const h = Math.floor(timeLeft.value / 3600)
    const m = Math.floor((timeLeft.value % 3600) / 60)
    const s = timeLeft.value % 60
    if (h > 0) {
        return `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
    }
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
})

// Savollarni fan bo'yicha guruhlash
const questionsWithIndex = computed(() =>
    props.questions.map((q, i) => ({ ...q, globalIndex: i }))
)

const questionsBySubject = computed(() => {
    const groups = {}
    questionsWithIndex.value.forEach(q => {
        if (!groups[q.subject_name]) groups[q.subject_name] = []
        groups[q.subject_name].push(q)
    })
    return groups
})

const currentQuestion = computed(() => props.questions[currentIndex.value] || null)
const answeredCount   = computed(() => Object.keys(answers.value).length)

const options = [
    { key: 'a' }, { key: 'b' }, { key: 'c' }, { key: 'd' },
]

const blockBadge = (type) => {
    const badges = {
        mandatory:   'bg-blue-50 text-blue-700',
        specialty_1: 'bg-green-50 text-green-700',
        specialty_2: 'bg-orange-50 text-orange-700',
    }
    return badges[type] || 'bg-gray-100 text-gray-600'
}

const goToQuestion = (index) => {
    currentIndex.value = index
}

const selectAnswer = async (key) => {
    const qId = currentQuestion.value.id
    answers.value[qId] = key

    // AJAX orqali saqlash
    try {
        await axios.post(route('cabinet.test.answer'), {
            question_id: qId,
            answer:      key,
        })
    } catch (e) {
        if (e.response?.data?.expired) {
            finish()
        }
    }

    // Keyingi savolga o'tish
    if (currentIndex.value < props.questions.length - 1) {
        setTimeout(() => { currentIndex.value++ }, 300)
    }
}

const finish = () => {
    clearInterval(timer)
    router.post(route('cabinet.test.finish'))
}

onMounted(() => {
    timeLeft.value = calculateTimeLeft()

    timer = setInterval(() => {
        timeLeft.value = calculateTimeLeft()
        if (timeLeft.value <= 0) {
            clearInterval(timer)
            finish()
        }
    }, 1000)
})

onUnmounted(() => {
    clearInterval(timer)
})
</script>
