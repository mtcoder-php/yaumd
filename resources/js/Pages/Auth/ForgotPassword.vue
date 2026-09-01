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
                <h2 class="text-lg font-medium text-gray-800 mb-2">Parolni unutdingizmi?</h2>
                <p class="text-sm text-gray-500 mb-6">
                    Ro'yxatdan o'tgan email manzilingizni kiriting — sizga parolni tiklash havolasi yuboriladi.
                </p>

                <div v-if="status" class="mb-5 p-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-700">
                    {{ status }}
                </div>

                <form @submit.prevent="submit">
                    <div class="mb-6">
                        <label class="block text-sm text-gray-600 mb-1.5">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="email@yaumd.uz"
                            autofocus
                            class="w-full px-3.5 py-2.5 rounded-lg border text-sm outline-none transition"
                            :class="errors.email
                                ? 'border-red-400 bg-red-50 focus:border-red-400'
                                : 'border-gray-200 focus:border-gray-400'"
                            autocomplete="email"
                        />
                        <p v-if="errors.email" class="text-red-500 text-xs mt-1.5">
                            {{ errors.email }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-2.5 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Yuborilmoqda...</span>
                        <span v-else>Havola yuborish</span>
                    </button>
                </form>

                <div class="text-center mt-5">
                    <Link :href="route('login')" class="text-sm text-gray-500 hover:text-gray-800">
                        &larr; Kirish sahifasiga qaytish
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, usePage, Link } from '@inertiajs/vue3'

const form = useForm({ email: '' })
const errors = usePage().props.errors
const status = computed(() => usePage().props.flash?.success)

const submit = () => {
    form.post(route('password.email'))
}
</script>
