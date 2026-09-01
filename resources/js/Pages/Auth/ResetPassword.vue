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
                <h2 class="text-lg font-medium text-gray-800 mb-2">Yangi parol o'rnatish</h2>
                <p class="text-sm text-gray-500 mb-6">
                    Hisobingiz uchun yangi parol kiriting.
                </p>

                <form @submit.prevent="submit">
                    <!-- Email (faqat ko'rsatish uchun) -->
                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-1.5">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            readonly
                            class="w-full px-3.5 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-sm text-gray-500 outline-none"
                        />
                        <p v-if="errors.email" class="text-red-500 text-xs mt-1.5">
                            {{ errors.email }}
                        </p>
                    </div>

                    <!-- Yangi parol -->
                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-1.5">Yangi parol</label>
                        <input
                            v-model="form.password"
                            type="password"
                            placeholder="••••••••"
                            autofocus
                            class="w-full px-3.5 py-2.5 rounded-lg border text-sm outline-none transition"
                            :class="errors.password
                                ? 'border-red-400 bg-red-50 focus:border-red-400'
                                : 'border-gray-200 focus:border-gray-400'"
                            autocomplete="new-password"
                        />
                        <p v-if="errors.password" class="text-red-500 text-xs mt-1.5">
                            {{ errors.password }}
                        </p>
                    </div>

                    <!-- Yangi parolni tasdiqlash -->
                    <div class="mb-6">
                        <label class="block text-sm text-gray-600 mb-1.5">Yangi parolni tasdiqlang</label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="••••••••"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-gray-200 text-sm outline-none transition focus:border-gray-400"
                            autocomplete="new-password"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-2.5 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Saqlanmoqda...</span>
                        <span v-else>Parolni yangilash</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3'

const props = defineProps({
    token: { type: String, required: true },
    email: { type: String, default: '' },
})

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
})

const errors = usePage().props.errors

const submit = () => {
    form.post(route('password.update'), {
        onError: () => form.reset('password', 'password_confirmation'),
    })
}
</script>
