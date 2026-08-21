<template>
    <AppLayout :title="isEdit ? 'Savolni tahrirlash' : 'Yangi savol'">
        <div class="max-w-2xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.subjects.questions.index', subject.id)"
                    class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition"
                >
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">
                        {{ isEdit ? 'Savolni tahrirlash' : 'Yangi savol' }}
                    </h1>
                    <p class="text-sm text-gray-400">{{ subject.name_uz }}</p>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <div class="space-y-5">

                    <!-- Savol tili -->
                    <div>
                        <label class="field-label">Savol tili <span class="req">*</span></label>
                        <div class="flex gap-3">
                            <button
                                v-for="lang in languages"
                                :key="lang.value"
                                type="button"
                                @click="form.language = lang.value"
                                class="flex-1 py-2.5 rounded-xl border-2 text-sm font-semibold transition-all"
                                :style="form.language === lang.value
                                    ? 'border-color:#0f3460; background:linear-gradient(135deg,#eff6ff,#f5f3ff); color:#0f3460'
                                    : 'border-color:#e5e7eb; background:#fafafa; color:#6b7280'"
                            >
                                {{ lang.label }}
                            </button>
                        </div>
                        <p v-if="form.errors.language" class="err">{{ form.errors.language }}</p>
                    </div>

                    <!-- Savol matni -->
                    <div>
                        <label class="field-label">Savol matni <span class="req">*</span></label>
                        <textarea
                            v-model="form.question"
                            rows="3"
                            placeholder="Savol matnini kiriting..."
                            class="field-input"
                            :class="form.errors.question ? 'field-error' : ''"
                            style="resize: none"
                        ></textarea>
                        <p v-if="form.errors.question" class="err">{{ form.errors.question }}</p>
                    </div>

                    <!-- Variantlar -->
                    <div>
                        <label class="field-label">Javob variantlari <span class="req">*</span></label>
                        <p class="text-xs text-gray-400 mb-3">To'g'ri javobni chapдagi tugmacha bilan belgilang</p>

                        <div class="space-y-2.5">
                            <div v-for="opt in options" :key="opt.key" class="flex items-center gap-3">
                                <!-- To'g'ri javob tugmasi -->
                                <button
                                    type="button"
                                    @click="form.correct_answer = opt.key"
                                    class="w-8 h-8 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all font-bold text-sm"
                                    :style="form.correct_answer === opt.key
                                        ? 'border-color:#0f3460; background:linear-gradient(135deg,#0f3460,#533483); color:white'
                                        : 'border-color:#e5e7eb; background:#f9fafb; color:#9ca3af'"
                                >
                                    {{ opt.key.toUpperCase() }}
                                </button>

                                <!-- Input -->
                                <input
                                    v-model="form['option_' + opt.key]"
                                    type="text"
                                    :placeholder="opt.placeholder"
                                    class="field-input flex-1"
                                    :class="[
                                        form.errors['option_' + opt.key] ? 'field-error' : '',
                                        form.correct_answer === opt.key ? 'correct-option' : ''
                                    ]"
                                >

                                <Icon
                                    v-if="form.correct_answer === opt.key"
                                    icon="mdi:check-circle"
                                    class="w-5 h-5 flex-shrink-0"
                                    style="color:#22c55e"
                                />
                            </div>
                        </div>
                        <p v-if="form.errors.correct_answer" class="err mt-2">{{ form.errors.correct_answer }}</p>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 border border-gray-100">
                        <div>
                            <p class="text-sm font-semibold text-gray-700">Faol holat</p>
                            <p class="text-xs text-gray-400 mt-0.5">Savol testlarda ishlatiladi</p>
                        </div>
                        <button
                            type="button"
                            @click="form.is_active = !form.is_active"
                            class="relative w-11 h-6 rounded-full transition-all duration-300"
                            :style="form.is_active
                                ? 'background:linear-gradient(135deg,#0f3460,#533483)'
                                : 'background:#e5e7eb'"
                        >
                            <span
                                class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-all duration-300"
                                :class="form.is_active ? 'left-6' : 'left-1'"
                            ></span>
                        </button>
                    </div>

                </div>

                <!-- Tugmalar -->
                <div class="flex gap-3 mt-6">
                    <Link
                        :href="route('admin.subjects.questions.index', subject.id)"
                        class="btn-secondary flex-1 flex items-center justify-center gap-2"
                    >
                        <Icon icon="mdi:close" class="w-4 h-4" />
                        Bekor qilish
                    </Link>
                    <button
                        type="button"
                        @click="submit"
                        :disabled="form.processing"
                        class="btn-primary flex-1"
                    >
                        <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                        <Icon v-else icon="mdi:content-save-outline" class="w-4 h-4" />
                        {{ form.processing ? 'Saqlanmoqda...' : 'Saqlash' }}
                    </button>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    subject:  { type: Object, required: true },
    question: { type: Object, default: null },
})

const isEdit = computed(() => !!props.question)

const form = useForm({
    language:       props.question?.language       || 'uz',
    question:       props.question?.question       || '',
    option_a:       props.question?.option_a       || '',
    option_b:       props.question?.option_b       || '',
    option_c:       props.question?.option_c       || '',
    option_d:       props.question?.option_d       || '',
    correct_answer: props.question?.correct_answer || '',
    is_active:      props.question?.is_active      ?? true,
})

const languages = [
    { value: 'uz', label: "O'zbek" },
    { value: 'ru', label: 'Rus' },
]

const options = [
    { key: 'a', placeholder: 'A variant...' },
    { key: 'b', placeholder: 'B variant...' },
    { key: 'c', placeholder: 'C variant...' },
    { key: 'd', placeholder: 'D variant...' },
]

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.subjects.questions.update', [props.subject.id, props.question.id]))
    } else {
        form.post(route('admin.subjects.questions.store', props.subject.id))
    }
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
.req { color: #ef4444; }
.field-input {
    width: 100%;
    padding: 0.6rem 0.875rem;
    border-radius: 0.625rem;
    border: 1.5px solid #e5e7eb;
    font-size: 0.875rem;
    color: #111827;
    background: #fafafa;
    outline: none;
    transition: border-color 0.2s, background 0.2s;
}
.field-input:focus { border-color: #0f3460; background: white; }
.field-error { border-color: #f87171 !important; background: #fef2f2 !important; }
.correct-option { border-color: #22c55e !important; background: #f0fdf4 !important; }
.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }

.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.5rem;
    border-radius: 0.75rem;
    background: linear-gradient(135deg, #0f3460, #533483);
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-primary:hover { box-shadow: 0 6px 20px rgba(15,52,96,0.3); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    background: white;
    color: #374151;
    font-size: 0.875rem;
    font-weight: 600;
    border: 1.5px solid #e5e7eb;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}
.btn-secondary:hover { background: #f9fafb; }
</style>
