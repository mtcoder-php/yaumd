<template>
    <div class="min-h-screen flex flex-col">
        <TopBar
            :settings="settings"
            :current-lang="lang"
            @change-lang="setLang"
        />
        <MainHeader
            :settings="settings"
            :current-lang="lang"
            :mobile-open="mobileOpen"
            @toggle-mobile="mobileOpen = !mobileOpen"
            @change-lang="setLang"
        />
        <Navigation
            :mobile-open="mobileOpen"
            @close-mobile="mobileOpen = false"
        />
        <main class="flex-1">
            <slot />
        </main>
        <Footer />
    </div>
</template>

<script setup>
import { ref } from 'vue'
import TopBar from '@/Components/Web/Header/TopBar.vue'
import MainHeader from '@/Components/Web/Header/MainHeader.vue'
import Navigation from '@/Components/Web/Header/Navigation.vue'
import Footer from '@/Components/Web/Footer/Footer.vue'
defineProps({
    settings: { type: Object, default: () => ({}) },
    faculties: { type: Array, default: () => [] },
})

const lang = ref(localStorage.getItem('lang') || 'uz')
const mobileOpen = ref(false)

const setLang = (code) => {
    lang.value = code
    localStorage.setItem('lang', code)
}
</script>
