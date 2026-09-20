<template>
    <section class="py-8 md:py-10 px-4">
        <div class="container mx-auto">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:flex gap-3 md:gap-4 lg:gap-0 bg-white rounded-2xl shadow-lg shadow-navy-900/5 border border-gray-200 lg:divide-x lg:divide-gray-200 p-4 md:p-5 lg:py-5 lg:px-2">
                <Link
                    v-for="link in quickLinks"
                    :key="link.title"
                    :href="link.url"
                    class="quick-card flex flex-col items-center text-center gap-2.5 py-3 px-2 lg:flex-1 lg:px-5 rounded-xl transition-all duration-300 group"
                >
                    <div class="icon-wrap flex items-center justify-center w-11 h-11 rounded-xl bg-navy-50 transition-all duration-300 group-hover:bg-brand-600">
                        <Icon :icon="link.icon" class="w-5 h-5 text-brand-600 group-hover:text-white transition-colors duration-300" />
                    </div>
                    <span>
                        <span class="block text-[13px] font-semibold text-navy-900 leading-tight">{{ link.title }}</span>
                        <span class="hidden sm:block text-[11px] text-gray-400 leading-tight mt-0.5">{{ link.subtitle }}</span>
                    </span>
                </Link>
            </div>
        </div>
    </section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const quickLinks = [
    { title: "Ta'lim yo'nalishlari", subtitle: 'Barcha yo\'nalishlar',        icon: 'mdi:book-open-page-variant-outline', url: '/qabul/yonalishlar' },
    { title: 'Qabul jarayoni',       subtitle: 'Onlayn ariza topshirish',     icon: 'mdi:file-document-edit-outline',     url: '/qabul/ariza' },
    { title: 'Talabalar uchun',      subtitle: 'Imkoniyatlar va imtiyozlar',  icon: 'mdi:account-school-outline',         url: '/talabalar/bakalavr/yoriqnoma' },
    { title: 'Kampus hayoti',        subtitle: 'Tadbirlar va loyihalar',      icon: 'mdi:image-multiple-outline',         url: '/galereya/foto' },
    { title: 'Ilmiy faoliyat',       subtitle: 'Maqolalar va tadqiqotlar',    icon: 'mdi:flask-outline',                  url: '/faoliyat/ilmiy/tadqiqot' },
    { title: 'Xalqaro aloqalar',     subtitle: 'Hamkorlik dasturlari',        icon: 'mdi:earth',                          url: '/faoliyat/xalqaro/hamkorlik' },
]
</script>

<style scoped>
.quick-card:hover {
    background: var(--color-navy-50);
}
</style>
