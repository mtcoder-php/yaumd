<template>
    <AppLayout :title="book.title">
        <div class="max-w-3xl mx-auto space-y-5">

            <div class="flex items-center gap-4">
                <Link :href="route('admin.my-library.index')"
                      class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <h1 class="text-xl font-bold text-gray-900">{{ book.title }}</h1>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6 flex flex-col sm:flex-row gap-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="w-32 h-44 rounded-xl bg-gray-100 overflow-hidden flex items-center justify-center flex-shrink-0 mx-auto sm:mx-0">
                    <img v-if="book.cover_image_url" :src="book.cover_image_url" class="w-full h-full object-cover" alt="">
                    <Icon v-else icon="mdi:book-outline" class="w-10 h-10 text-gray-300" />
                </div>

                <div class="flex-1 space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">{{ book.author }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ book.category?.name_uz || 'Kategoriyasiz' }}</p>
                    </div>

                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold"
                          :class="book.available_copies_count > 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600'">
                        <Icon :icon="book.available_copies_count > 0 ? 'mdi:check-circle-outline' : 'mdi:close-circle-outline'" class="w-4 h-4 mr-1" />
                        {{ book.available_copies_count > 0
                        ? `${book.available_copies_count} / ${book.copies_count} nusxa bo'sh`
                        : "Hozircha barcha nusxalar band" }}
                    </span>

                    <div class="grid grid-cols-2 gap-4">
                        <div v-if="book.publisher">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Nashriyot</p>
                            <p class="text-sm font-semibold text-gray-900">{{ book.publisher }}</p>
                        </div>
                        <div v-if="book.published_year">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Nashr yili</p>
                            <p class="text-sm font-semibold text-gray-900">{{ book.published_year }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Til</p>
                            <p class="text-sm font-semibold text-gray-900">{{ languageLabel(book.language) }}</p>
                        </div>
                        <div v-if="book.page_count">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Sahifalar</p>
                            <p class="text-sm font-semibold text-gray-900">{{ book.page_count }}</p>
                        </div>
                        <div v-if="book.shelf_location">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Joylashuvi</p>
                            <p class="text-sm font-semibold text-gray-900">{{ book.shelf_location }}</p>
                        </div>
                        <div v-if="book.isbn">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">ISBN</p>
                            <p class="text-sm font-semibold text-gray-900">{{ book.isbn }}</p>
                        </div>
                    </div>

                    <div v-if="book.description">
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Tavsif</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ book.description }}</p>
                    </div>

                    <p class="text-xs text-gray-400 pt-2 border-t border-gray-100">
                        Kitobni o'qish uchun kutubxonaga tashrif buyuring va kutubxonachidan shu nusxani so'rang.
                    </p>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
    book: { type: Object, required: true },
})

const languageLabel = (v) => ({ uz: "O'zbek", ru: 'Rus', en: 'Ingliz' }[v] || v || '—')
</script>
