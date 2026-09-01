<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="w-full max-w-md">

            <!-- Logo -->
            <div class="text-center mb-8">
                <h1 class="text-2xl font-semibold text-gray-900">
                    Yangi Asr Universiteti
                </h1>
                <p class="text-gray-500 text-sm mt-1">Integrallashgan platforma</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-xl border border-gray-200 p-8">
                <h2 class="text-lg font-medium text-gray-800 mb-2">Tasdiqlash kodi</h2>
                <p class="text-sm text-gray-500 mb-6">
                    <span v-if="maskedEmail">{{ maskedEmail }} manziliga 6 xonali kod yuborildi.</span>
                    <span v-else>Emailingizga 6 xonali kod yuborildi.</span>
                </p>

                <form @submit.prevent="submit">
                    <!-- Kod -->
                    <div class="mb-6">
                        <label class="block text-sm text-gray-600 mb-1.5">Kod</label>
                        <input
                            v-model="form.code"
                            type="text"
                            inputmode="numeric"
                            maxlength="6"
                            placeholder="000000"
                            autofocus
                            class="w-full px-3.5 py-3 rounded-lg border text-center text-2xl tracking-[0.5em] font-semibold outline-none transition"
                            :class="form.errors.code
                                ? 'border-red-400 bg-red-50 focus:border-red-400'
                                : 'border-gray-200 focus:border-gray-400'"
                            @input="form.code = form.code.replace(/\D/g, '').slice(0, 6)"
                        />
                        <p v-if="form.errors.code" class="text-red-500 text-xs mt-1.5 text-center">
                            {{ form.errors.code }}
                        </p>
                    </div>

                    <!-- Button -->
                    <button
                        type="submit"
                        :disabled="form.processing || form.code.length !== 6"
                        class="w-full py-2.5 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Tekshirilmoqda...</span>
                        <span v-else>Tasdiqlash</span>
                    </button>
                </form>

                <div class="flex items-center justify-between mt-5 text-sm">
                    <button
                        type="button"
                        @click="resend"
                        :disabled="resendForm.processing || cooldown > 0"
                        class="text-gray-500 hover:text-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ cooldown > 0 ? `Qayta yuborish (${cooldown}s)` : "Kodni qayta yuborish" }}
                    </button>
                    <button type="button" @click="cancel" class="text-gray-400 hover:text-gray-600">
                        Bekor qilish
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onBeforeUnmount } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'

defineProps({
    maskedEmail: { type: String, default: null },
})

const toast = useToast()
const form = useForm({ code: '' })
const resendForm = useForm({})

const cooldown = ref(0)
let timer = null
const startCooldown = () => {
    cooldown.value = 60
    clearInterval(timer)
    timer = setInterval(() => {
        cooldown.value -= 1
        if (cooldown.value <= 0) clearInterval(timer)
    }, 1000)
}
onBeforeUnmount(() => clearInterval(timer))

const submit = () => {
    form.post(route('login.verify.post'), {
        onError: (errors) => {
            form.reset('code')
            if (errors.code) toast.error(errors.code)
        },
    })
}

const resend = () => {
    if (cooldown.value > 0) return
    resendForm.post(route('login.verify.resend'), {
        onSuccess: (page) => {
            startCooldown()
            if (page.props.flash?.success) toast.success(page.props.flash.success)
        },
        onError: (errors) => {
            if (errors.code) toast.error(errors.code)
        },
    })
}

const cancel = () => {
    resendForm.post(route('login.verify.cancel'))
}
</script>
