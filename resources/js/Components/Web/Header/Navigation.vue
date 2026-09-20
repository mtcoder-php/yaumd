<template>
    <div>
        <!-- Mobile Drawer -->
        <Transition
            enter-active-class="transition duration-300"
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition duration-200"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
            <div
                v-if="mobileOpen"
                class="lg:hidden fixed inset-y-0 left-0 w-80 bg-white shadow-2xl z-50 flex flex-col"
            >
                <!-- Drawer header -->
                <div class="flex items-center justify-between px-4 py-4 border-b border-gray-100 bg-gradient-to-r from-navy-700 to-brand-700">
                    <div class="flex items-center gap-2 text-white">
                        <Icon icon="mdi:menu" class="w-5 h-5" />
                        <span class="font-semibold text-sm">Menyu</span>
                    </div>
                    <button
                        @click="$emit('closeMobile')"
                        class="text-white/80 hover:text-white transition"
                    >
                        <Icon icon="mdi:close" class="w-5 h-5" />
                    </button>
                </div>

                <!-- Qabul tugmasi -->
                <div class="px-4 py-3 bg-navy-50 border-b border-navy-100">
                    <Link
                        href="/qabul/ariza"
                        @click="$emit('closeMobile')"
                        class="flex items-center justify-center gap-2 w-full py-2.5 bg-gradient-to-r from-navy-700 to-brand-700 text-white text-sm font-medium rounded-xl"
                    >
                        <Icon icon="mdi:school-outline" class="w-4 h-4" />
                        Qabul 2026 — Ariza topshirish
                    </Link>
                </div>

                <!-- Menyu items -->
                <div class="flex-1 overflow-y-auto">
                    <div
                        v-for="item in menuItems"
                        :key="item.path"
                        class="border-b border-gray-50"
                    >
                        <!-- Submenusiz oddiy band (masalan "Aloqa") -->
                        <Link
                            v-if="!item.submenu"
                            :href="item.href || '#'"
                            @click="$emit('closeMobile')"
                            class="w-full flex items-center px-4 py-3.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:text-brand-600 transition"
                        >
                            {{ item.name }}
                        </Link>

                        <!-- Submenuli asosiy band -->
                        <template v-else>
                            <button
                                @click="toggleMobileItem(item.path)"
                                class="w-full flex items-center justify-between px-4 py-3.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:text-brand-600 transition"
                            >
                                <span>{{ item.name }}</span>
                                <Icon
                                    icon="mdi:chevron-down"
                                    class="w-4 h-4 text-gray-400 transition-transform"
                                    :class="mobileExpanded === item.path ? 'rotate-180' : ''"
                                />
                            </button>

                            <div v-if="mobileExpanded === item.path" class="bg-gray-50">
                                <div
                                    v-for="sub in item.submenu"
                                    :key="sub.path"
                                    class="border-t border-gray-100"
                                >
                                    <!-- Inner submenu bor -->
                                    <div v-if="sub.innersubmenu">
                                        <button
                                            @click="toggleMobileSub(sub.path)"
                                            class="w-full flex items-center justify-between px-6 py-3 text-sm font-medium text-gray-600 hover:text-brand-600 transition"
                                        >
                                            <span>{{ sub.title }}</span>
                                            <Icon
                                                icon="mdi:chevron-down"
                                                class="w-3.5 h-3.5 text-gray-400 transition-transform"
                                                :class="mobileSubExpanded === sub.path ? 'rotate-180' : ''"
                                            />
                                        </button>
                                        <div v-if="mobileSubExpanded === sub.path" class="bg-white">
                                            <Link
                                                v-for="inner in sub.innersubmenu"
                                                :key="inner.path"
                                                :href="inner.path"
                                                @click="$emit('closeMobile')"
                                                class="flex items-center gap-2 px-8 py-2.5 text-sm text-gray-500 hover:text-brand-600 hover:bg-navy-50 transition border-t border-gray-50"
                                            >
                                                <Icon icon="mdi:circle-small" class="w-4 h-4 text-gray-300" />
                                                {{ inner.title }}
                                            </Link>
                                        </div>
                                    </div>

                                    <!-- Oddiy link -->
                                    <Link
                                        v-else
                                        :href="sub.path"
                                        @click="$emit('closeMobile')"
                                        class="flex items-center gap-2 px-6 py-3 text-sm text-gray-600 hover:text-brand-600 hover:bg-navy-50 transition"
                                    >
                                        <Icon icon="mdi:chevron-right" class="w-3.5 h-3.5 text-gray-300" />
                                        {{ sub.title }}
                                    </Link>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                    <p class="text-xs text-gray-400 text-center">© {{ new Date().getFullYear() }} Yangi Asr Universiteti</p>
                </div>
            </div>
        </Transition>

        <!-- Overlay -->
        <Transition
            enter-active-class="transition duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileOpen"
                @click="$emit('closeMobile')"
                class="lg:hidden fixed inset-0 bg-black/40 z-40"
            />
        </Transition>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { menuItems } from '@/Data/menuItems.js'

defineProps({
    mobileOpen: { type: Boolean, default: false },
})

defineEmits(['closeMobile'])

const mobileExpanded    = ref(null)
const mobileSubExpanded = ref(null)

const toggleMobileItem = (path) => {
    mobileExpanded.value = mobileExpanded.value === path ? null : path
    mobileSubExpanded.value = null
}

const toggleMobileSub = (path) => {
    mobileSubExpanded.value = mobileSubExpanded.value === path ? null : path
}
</script>
