<template>
    <div class="min-h-screen bg-gray-50">
        <nav class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h1 class="font-semibold text-gray-800">Yangi Asr Universiteti</h1>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">{{ $page.props.auth.user.full_name }}</span>
                <button
                    @click="logout"
                    class="text-sm text-gray-500 hover:text-gray-800 transition"
                >
                    Chiqish
                </button>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-6 py-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-2">Dashboard</h2>
            <p class="text-gray-500 text-sm">
                Xush kelibsiz, {{ $page.props.auth.user.full_name }}!
                Sizning rolingiz:
                <span class="font-medium text-gray-700">
                    {{ $page.props.auth.user.roles[0] ?? 'Noma\'lum' }}
                </span>
            </p>
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

const logout = () => {
    router.post('/logout')
}
</script>
