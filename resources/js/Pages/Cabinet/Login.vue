<template>
    <div class="min-h-screen overflow-hidden relative flex items-center justify-center p-4"
         style="background: linear-gradient(135deg, #0f3460 0%, #16213e 50%, #533483 100%)">

        <!-- Background circles -->
        <div class="absolute top-[-100px] left-[-100px] w-80 h-80 rounded-full opacity-10"
             style="background: rgba(255,255,255,0.3)"></div>
        <div class="absolute bottom-[-80px] right-[-80px] w-96 h-96 rounded-full opacity-10"
             style="background: rgba(255,255,255,0.2)"></div>
        <div class="absolute top-1/2 left-[-50px] w-40 h-40 rounded-full opacity-5"
             style="background: rgba(255,255,255,0.4)"></div>

        <div class="relative w-full max-w-sm">

            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4"
                     style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px)">
                    <Icon icon="mdi:school" class="w-8 h-8 text-white" />
                </div>
                <h1 class="text-2xl font-bold text-white">Yangi Asr Universiteti</h1>
                <p class="text-sm mt-1" style="color: rgba(255,255,255,0.6)">Abituriyent kabineti</p>
            </div>

            <!-- Card -->
            <div class="rounded-2xl p-6"
                 style="background: rgba(255,255,255,0.1); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.2)">

                <h2 class="text-lg font-bold text-white mb-1">Kirish</h2>
                <p class="text-xs mb-6" style="color: rgba(255,255,255,0.6)">
                    Pasport seriyasi va tug'ilgan sanangizni kiriting
                </p>

                <div class="space-y-4">

                    <!-- Login -->
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color: rgba(255,255,255,0.8)">
                            Pasport seriyasi
                        </label>
                        <input
                            v-model="form.login"
                            type="text"
                            placeholder="AA1234567"
                            maxlength="9"
                            class="w-full px-4 py-3 rounded-xl text-sm font-mono font-semibold outline-none transition-all"
                            style="background: rgba(255,255,255,0.1); border: 1.5px solid rgba(255,255,255,0.2); color: white; placeholder-color: rgba(255,255,255,0.4)"
                            :style="form.errors.login ? 'border-color: #f87171' : ''"
                            @input="form.login = form.login.toUpperCase()"
                            @keyup.enter="submit"
                        >
                        <p v-if="form.errors.login" class="text-red-400 text-xs mt-1.5">{{ form.errors.login }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color: rgba(255,255,255,0.8)">
                            Parol (tug'ilgan sana)
                        </label>
                        <input
                            type="date"
                            class="w-full px-4 py-3 rounded-xl text-sm font-mono outline-none transition-all"
                            style="background: rgba(255,255,255,0.1); border: 1.5px solid rgba(255,255,255,0.2); color: white; color-scheme: dark"
                            :style="form.errors.password ? 'border-color: #f87171' : ''"
                            @change="onDateChange"
                            @keyup.enter="submit"
                        >
                        <p v-if="form.errors.password" class="text-red-400 text-xs mt-1.5">{{ form.errors.password }}</p>
                    </div>

                    <!-- Hint -->
                    <div class="p-3 rounded-xl" style="background: rgba(255,255,255,0.08)">
                        <p class="text-xs flex items-start gap-2" style="color: rgba(255,255,255,0.6)">
                            <Icon icon="mdi:information-outline" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5" />
                            Parol — tug'ilgan sanangiz. Taqvimdan tanlang!
                        </p>
                    </div>

                    <!-- Submit -->
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="w-full py-3 rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2"
                        style="background: white; color: #0f3460"
                    >
                        <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                        <Icon v-else icon="mdi:login" class="w-4 h-4" />
                        {{ form.processing ? 'Kirish...' : 'Kirish' }}
                    </button>

                </div>
            </div>

            <!-- Footer -->
            <p class="text-center text-xs mt-6" style="color: rgba(255,255,255,0.4)">
                © {{ new Date().getFullYear() }} Yangi Asr Universiteti
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const form = useForm({
    login:    '',
    password: '',
})

const onDateChange = (e) => {
    const val = e.target.value // YYYY-MM-DD
    if (val) {
        const [y, m, d] = val.split('-')
        form.password = `${d}.${m}.${y}`
    }
}

const submit = () => {
    form.post(route('cabinet.login.post'))
}
</script>
