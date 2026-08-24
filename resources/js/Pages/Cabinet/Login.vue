<template>
    <div class="min-h-screen flex items-center justify-center p-4" style="background: #f8fafc">

        <div class="w-full max-w-sm">

            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4"
                     style="background: linear-gradient(135deg, #0f3460, #533483)">
                    <Icon icon="mdi:school" class="w-8 h-8 text-white" />
                </div>
                <h1 class="text-xl font-bold text-gray-900">Yangi Asr Universiteti</h1>
                <p class="text-sm text-gray-400 mt-1">Abituriyent kabineti</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-2xl border border-gray-100 p-7"
                 style="box-shadow: 0 4px 24px rgba(0,0,0,0.08)">

                <h2 class="text-lg font-bold text-gray-900 mb-1">Kirish</h2>
                <p class="text-sm text-gray-400 mb-6">Pasport seriyasi va tug'ilgan sanangizni kiriting</p>

                <div class="space-y-5">

                    <!-- Login -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Pasport seriyasi
                        </label>
                        <div class="relative">
                            <Icon icon="mdi:card-account-details-outline"
                                  class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                            <input
                                v-model="form.login"
                                type="text"
                                placeholder="AA1234567"
                                maxlength="9"
                                class="w-full pl-11 pr-4 py-3 rounded-xl border text-sm font-mono font-semibold outline-none transition-all"
                                :class="form.errors.login
                                    ? 'border-red-300 bg-red-50'
                                    : 'border-gray-200 bg-gray-50 focus:border-[#0f3460] focus:bg-white'"
                                @input="form.login = form.login.toUpperCase()"
                                @keyup.enter="submit"
                            >
                        </div>
                        <p v-if="form.errors.login" class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5" />
                            {{ form.errors.login }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Parol (tug'ilgan sana)
                        </label>
                        <div class="relative">
                            <Icon icon="mdi:calendar-outline"
                                  class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                            <input
                                type="date"
                                class="w-full pl-11 pr-4 py-3 rounded-xl border text-sm outline-none transition-all"
                                :class="form.errors.password
                                    ? 'border-red-300 bg-red-50'
                                    : 'border-gray-200 bg-gray-50 focus:border-[#0f3460] focus:bg-white'"
                                @change="onDateChange"
                                @keyup.enter="submit"
                            >
                        </div>
                        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5" />
                            {{ form.errors.password }}
                        </p>
                        <p class="text-gray-400 text-xs mt-1.5 flex items-center gap-1">
                            <Icon icon="mdi:information-outline" class="w-3.5 h-3.5" />
                            Taqvimdan tug'ilgan sanangizni tanlang
                        </p>
                    </div>

                    <!-- Submit -->
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="w-full py-3.5 rounded-xl font-semibold text-sm text-white transition-all flex items-center justify-center gap-2"
                        style="background: linear-gradient(135deg, #0f3460, #533483)"
                        :class="form.processing ? 'opacity-70 cursor-not-allowed' : 'hover:shadow-lg hover:shadow-blue-100'"
                    >
                        <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                        <Icon v-else icon="mdi:login" class="w-4 h-4" />
                        {{ form.processing ? 'Kirish...' : 'Kirish' }}
                    </button>

                </div>
            </div>

            <!-- Help -->
            <div class="mt-4 p-4 rounded-xl bg-amber-50 border border-amber-100 flex items-start gap-2">
                <Icon icon="mdi:help-circle-outline" class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" />
                <p class="text-xs text-amber-700">
                    Muammo bo'lsa qabul bo'limiga murojaat qiling
                </p>
            </div>

            <p class="text-center text-xs text-gray-400 mt-6">
                © {{ new Date().getFullYear() }} Yangi Asr Universiteti
            </p>

        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const form = useForm({
    login:    '',
    password: '',
})

const onDateChange = (e) => {
    const val = e.target.value
    if (val) {
        const [y, m, d] = val.split('-')
        form.password = `${d}.${m}.${y}`
    }
}

const submit = () => {
    form.post(route('cabinet.login.post'))
}
</script>
