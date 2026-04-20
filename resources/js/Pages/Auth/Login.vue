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
                <h2 class="text-lg font-medium text-gray-800 mb-6">Tizimga kirish</h2>

                <form @submit.prevent="submit">
                    <!-- Email -->
                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-1.5">
                            Email
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="admin@yaumd.uz"
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

                    <!-- Parol -->
                    <div class="mb-6">
                        <label class="block text-sm text-gray-600 mb-1.5">
                            Parol
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="••••••••"
                                class="w-full px-3.5 py-2.5 rounded-lg border text-sm outline-none transition pr-10"
                                :class="errors.password
                                    ? 'border-red-400 bg-red-50 focus:border-red-400'
                                    : 'border-gray-200 focus:border-gray-400'"
                                autocomplete="current-password"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <span class="text-xs">{{ showPassword ? 'Yashir' : 'Ko\'rsat' }}</span>
                            </button>
                        </div>
                        <p v-if="errors.password" class="text-red-500 text-xs mt-1.5">
                            {{ errors.password }}
                        </p>
                    </div>

                    <!-- Eslab qolish -->
                    <div class="flex items-center gap-2 mb-6">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            id="remember"
                            class="w-4 h-4 rounded border-gray-300"
                        />
                        <label for="remember" class="text-sm text-gray-600">
                            Eslab qolish
                        </label>
                    </div>

                    <!-- Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-2.5 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Kirish...</span>
                        <span v-else>Kirish</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'

const showPassword = ref(false)

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const errors = usePage().props.errors

const submit = () => {
    form.post('/login', {
        onError: () => {
            form.reset('password')
        },
    })
}
</script>
