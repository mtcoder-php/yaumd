<template>
    <section class="quick-section py-8 px-4 mt-10">
        <div class="container mx-auto">
            <div class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-9 gap-3">
                <a
                    v-for="link in quickLinks"
                    :key="link.title"
                    :href="link.url"
                    class="glass-card flex flex-col items-center justify-center gap-3 py-5 px-2 rounded-2xl transition-all duration-300 group h-28 sm:h-32"
                >
                    <div
                        class="icon-wrap flex items-center justify-center rounded-2xl transition-all duration-300 group-hover:scale-110"
                        style="width: 48px; height: 48px"
                        :style="{ background: link.color }"
                    >
                        <Icon :icon="link.icon" class="w-5 h-5 text-white drop-shadow" />
                    </div>
                    <span class="text-[11px] font-semibold text-white text-center leading-tight drop-shadow-sm">
                        {{ link.title }}
                    </span>
                </a>
            </div>
        </div>
    </section>
</template>

<script setup>
import { Icon } from '@iconify/vue'

const quickLinks = [
    { title: "Elektron hujjat",        icon: "mdi:file-document-edit-outline", color: "linear-gradient(135deg, #667eea, #764ba2)", url: "#" },
    { title: "Masofaviy ta'lim",       icon: "mdi:school-outline",             color: "linear-gradient(135deg, #f093fb, #f5576c)", url: "#" },
    { title: "Elektron kutubxona",     icon: "mdi:bookshelf",                  color: "linear-gradient(135deg, #4facfe, #00f2fe)", url: "/kutubxona" },
    { title: "Universitet jurnallari", icon: "mdi:newspaper-variant-outline",  color: "linear-gradient(135deg, #43e97b, #38f9d7)", url: "#" },
    { title: "Interaktiv xizmatlar",   icon: "mdi:layers-outline",             color: "linear-gradient(135deg, #fa709a, #fee140)", url: "#" },
    { title: "Korporativ pochta",      icon: "mdi:email-outline",              color: "linear-gradient(135deg, #fda085, #f6d365)", url: "#" },
    { title: "HEMIS OTM",              icon: "mdi:monitor-account",            color: "linear-gradient(135deg, #89f7fe, #66a6ff)", url: "#" },
    { title: "HEMIS Talaba",           icon: "mdi:account-school-outline",     color: "linear-gradient(135deg, #fddb92, #d1fdff)", url: "#" },
    { title: "Unilibrary",             icon: "mdi:book-open-outline",          color: "linear-gradient(135deg, #a1c4fd, #c2e9fb)", url: "#" },
]
</script>

<style scoped>
.quick-section {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 40%, #0f3460 70%, #533483 100%);
    position: relative;
    overflow: hidden;
}

.quick-section::before {
    content: '';
    position: absolute;
    top: -80px;
    left: -80px;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(102, 126, 234, 0.4) 0%, transparent 70%);
    border-radius: 50%;
}

.quick-section::after {
    content: '';
    position: absolute;
    bottom: -60px;
    right: -60px;
    width: 250px;
    height: 250px;
    background: radial-gradient(circle, rgba(240, 147, 251, 0.35) 0%, transparent 70%);
    border-radius: 50%;
}

.glass-card {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.1);
    position: relative;
    z-index: 1;
}

.glass-card:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.2);
    transform: translateY(-4px);
}

.icon-wrap {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}
</style>
