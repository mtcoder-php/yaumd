<template>
    <section class="py-12">
        <div class="container mx-auto px-4">

            <!-- Sarlavha -->
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Statistika</h2>
                <p class="text-gray-500 text-sm">Universitetimiz haqida umumiy ma'lumotlar</p>
            </div>

            <!-- Stats grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4" ref="statsRef">
                <div
                    v-for="stat in stats"
                    :key="stat.label"
                    class="stat-card bg-white rounded-2xl p-6 border border-gray-100 flex flex-col items-center text-center transition-all duration-300 hover:-translate-y-1 h-44"
                >
                    <!-- Icon -->
                    <div
                        class="w-14 h-14 rounded-2xl flex items-center justify-center mb-4"
                        :style="{ background: stat.bgColor }"
                    >
                        <Icon :icon="stat.icon" class="w-7 h-7" :style="{ color: stat.color }" />
                    </div>

                    <!-- Label -->
                    <p class="text-gray-500 text-sm mb-2">{{ stat.label }}</p>

                    <!-- Number -->
                    <p class="text-3xl font-bold" :style="{ color: stat.color }">
                        {{ formatNumber(stat.current) }}{{ stat.suffix }}
                    </p>
                </div>
            </div>

        </div>
    </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Icon } from '@iconify/vue'

const statsRef = ref(null)
let observer = null

const stats = ref([
    {
        label: "Talabalar",
        target: 4000,
        current: 0,
        suffix: '+',
        icon: 'mdi:account-group-outline',
        color: '#3b82f6',
        bgColor: '#eff6ff',
    },
    {
        label: "Professor o'qituvchilar",
        target: 150,
        current: 0,
        suffix: '+',
        icon: 'mdi:school-outline',
        color: '#22c55e',
        bgColor: '#f0fdf4',
    },
    {
        label: "Yo'nalishlar",
        target: 25,
        current: 0,
        suffix: '',
        icon: 'mdi:book-open-outline',
        color: '#a855f7',
        bgColor: '#faf5ff',
    },
    {
        label: "Hamkorlar",
        target: 15,
        current: 0,
        suffix: '+',
        icon: 'mdi:handshake-outline',
        color: '#f97316',
        bgColor: '#fff7ed',
    },
])

const formatNumber = (num) => {
    return new Intl.NumberFormat('uz-UZ').format(Math.floor(num))
}

// Counter animatsiya
const animateCounters = () => {
    stats.value.forEach((stat) => {
        const duration = 2000
        const start = performance.now()

        const update = (time) => {
            const elapsed = time - start
            const progress = Math.min(elapsed / duration, 1)
            const eased = 1 - Math.pow(1 - progress, 3) // ease out cubic
            stat.current = stat.target * eased

            if (progress < 1) requestAnimationFrame(update)
            else stat.current = stat.target
        }

        requestAnimationFrame(update)
    })
}

// Viewport ga kirganda animatsiya boshlanadi
onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            if (entries[0].isIntersecting) {
                animateCounters()
                observer.disconnect()
            }
        },
        { threshold: 0.3 }
    )
    if (statsRef.value) observer.observe(statsRef.value)
})

onUnmounted(() => observer?.disconnect())
</script>

<style scoped>
.stat-card {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}
.stat-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}
</style>
