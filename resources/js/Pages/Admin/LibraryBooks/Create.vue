<template>
    <AppLayout title="Yangi kitob">
        <div class="max-w-2xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="route('admin.library.index')"
                      class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <h1 class="text-xl font-bold text-gray-900">Yangi kitob</h1>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <!-- Muqova -->
                <div>
                    <label class="field-label">Muqova rasm</label>
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-28 rounded-xl bg-gray-100 overflow-hidden flex items-center justify-center flex-shrink-0 border border-gray-200">
                            <img v-if="coverPreview" :src="coverPreview" class="w-full h-full object-cover" alt="">
                            <Icon v-else icon="mdi:image-outline" class="w-6 h-6 text-gray-400" />
                        </div>
                        <div>
                            <input ref="coverInput" type="file" accept="image/*" class="hidden" @change="onCoverChange">
                            <button type="button" @click="$refs.coverInput.click()" class="btn-neutral">
                                <Icon icon="mdi:upload-outline" class="w-4 h-4" />
                                Rasm tanlash
                            </button>
                            <p class="hint mt-1">JPG/PNG, maksimum 4 MB</p>
                        </div>
                    </div>
                    <p v-if="form.errors.cover_image" class="err">{{ form.errors.cover_image }}</p>
                </div>

                <!-- Nomi / Muallif -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="field-label"><span class="req">*</span> Kitob nomi</label>
                        <input v-model="form.title" type="text" placeholder="Masalan: Oʻtkan kunlar"
                               class="field-input" :class="form.errors.title ? 'field-error' : ''">
                        <p v-if="form.errors.title" class="err">{{ form.errors.title }}</p>
                    </div>
                    <div>
                        <label class="field-label"><span class="req">*</span> Muallif</label>
                        <input v-model="form.author" type="text" placeholder="Masalan: Abdulla Qodiriy"
                               class="field-input" :class="form.errors.author ? 'field-error' : ''">
                        <p v-if="form.errors.author" class="err">{{ form.errors.author }}</p>
                    </div>
                    <div>
                        <label class="field-label"><span class="req">*</span> Kategoriya</label>
                        <select v-model="form.category_id" class="field-input" :class="form.errors.category_id ? 'field-error' : ''">
                            <option value="">Tanlang</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name_uz }}</option>
                        </select>
                        <p v-if="form.errors.category_id" class="err">{{ form.errors.category_id }}</p>
                    </div>
                </div>

                <!-- Nashriyot / ISBN / Yil / Til -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Nashriyot</label>
                        <input v-model="form.publisher" type="text" placeholder="Ixtiyoriy" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">ISBN</label>
                        <input v-model="form.isbn" type="text" placeholder="Ixtiyoriy"
                               class="field-input" :class="form.errors.isbn ? 'field-error' : ''">
                        <p v-if="form.errors.isbn" class="err">{{ form.errors.isbn }}</p>
                    </div>
                    <div>
                        <label class="field-label">Nashr yili</label>
                        <input v-model.number="form.published_year" type="number" placeholder="Masalan: 2020"
                               class="field-input" :class="form.errors.published_year ? 'field-error' : ''">
                        <p v-if="form.errors.published_year" class="err">{{ form.errors.published_year }}</p>
                    </div>
                    <div>
                        <label class="field-label">Til</label>
                        <select v-model="form.language" class="field-input">
                            <option value="uz">O'zbek</option>
                            <option value="ru">Rus</option>
                            <option value="en">Ingliz</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Sahifalar soni</label>
                        <input v-model.number="form.page_count" type="number" min="1" placeholder="Ixtiyoriy" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Javon/qator manzili</label>
                        <input v-model="form.shelf_location" type="text" placeholder="Masalan: 3-javon, B-qator" class="field-input">
                        <p class="hint">Kitobni kutubxona ichida topish uchun</p>
                    </div>
                </div>

                <!-- Tavsif -->
                <div>
                    <label class="field-label">Tavsif</label>
                    <textarea v-model="form.description" rows="4" placeholder="Kitob haqida qisqacha ma'lumot"
                              class="field-input"></textarea>
                </div>

                <!-- Status -->
                <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 border border-gray-100">
                    <div>
                        <p class="text-sm font-semibold text-gray-700">Kitob faol</p>
                        <p class="text-xs text-gray-400 mt-0.5">Nofaol kitoblar talabalar katalogida ko'rinmaydi</p>
                    </div>
                    <button type="button" @click="form.is_active = !form.is_active"
                            class="relative w-11 h-6 rounded-full transition-all duration-300 flex-shrink-0"
                            :class="form.is_active ? 'bg-brand-600' : 'bg-gray-200'">
                        <span class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-all duration-300"
                              :class="form.is_active ? 'left-6' : 'left-1'"></span>
                    </button>
                </div>
            </div>

            <!-- Elektron (raqamli) kitob -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div>
                    <p class="text-sm font-bold text-gray-700">Elektron kitob</p>
                    <p class="text-xs text-gray-400 mt-0.5">Fayl yuklasangiz, talaba uni saytdan yuklab olishi mumkin bo'ladi</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label"><span class="req">*</span> Kitob turi</label>
                        <select v-model="form.access_type" class="field-input">
                            <option value="free">Bepul</option>
                            <option value="paid">Pullik</option>
                        </select>
                        <p v-if="form.errors.access_type" class="err">{{ form.errors.access_type }}</p>
                    </div>
                    <div v-if="form.access_type === 'paid'">
                        <label class="field-label"><span class="req">*</span> Narxi (so'm)</label>
                        <input v-model.number="form.price" type="number" min="0" step="1000" placeholder="Masalan: 25000"
                               class="field-input" :class="form.errors.price ? 'field-error' : ''">
                        <p v-if="form.errors.price" class="err">{{ form.errors.price }}</p>
                    </div>
                </div>

                <div>
                    <label class="field-label">Elektron fayl (PDF / EPUB / Word)</label>
                    <div class="flex items-center gap-3">
                        <input ref="fileInput" type="file" accept=".pdf,.epub,.doc,.docx" class="hidden" @change="onFileChange">
                        <button type="button" @click="$refs.fileInput.click()" class="btn-neutral">
                            <Icon icon="mdi:file-upload-outline" class="w-4 h-4" />
                            Fayl tanlash
                        </button>
                        <span v-if="fileName" class="text-sm text-gray-600 flex items-center gap-1">
                            <Icon icon="mdi:file-check-outline" class="w-4 h-4 text-green-600" />
                            {{ fileName }}
                        </span>
                    </div>
                    <p class="hint mt-1">Maksimum 50 MB. Fayl himoyalangan joyda saqlanadi — faqat ruxsati bor talaba yuklab oladi.</p>
                    <p v-if="form.errors.digital_file" class="err">{{ form.errors.digital_file }}</p>
                </div>
            </div>

            <!-- Tugmalar -->
            <div class="flex gap-3">
                <Link :href="route('admin.library.index')"
                      class="btn-neutral flex-1 justify-center">
                    <Icon icon="mdi:close" class="w-4 h-4" />
                    Bekor qilish
                </Link>
                <button type="button" @click="submit" :disabled="form.processing" class="btn-brand flex-1 justify-center">
                    <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                    <Icon v-else icon="mdi:content-save-outline" class="w-4 h-4" />
                    {{ form.processing ? 'Saqlanmoqda...' : 'Saqlash' }}
                </button>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
    categories: { type: Array, default: () => [] },
})

const form = useForm({
    category_id:     '',
    isbn:            '',
    title:           '',
    author:          '',
    publisher:       '',
    published_year:  null,
    language:        'uz',
    description:     '',
    cover_image:     null,
    page_count:      null,
    shelf_location:  '',
    access_type:     'free',
    price:           null,
    digital_file:    null,
    is_active:       true,
})

const coverInput = ref(null)
const coverPreview = ref(null)

const onCoverChange = (e) => {
    const file = e.target.files[0]
    if (!file) return
    form.cover_image = file
    coverPreview.value = URL.createObjectURL(file)
}

const fileInput = ref(null)
const fileName = ref('')

const onFileChange = (e) => {
    const file = e.target.files[0]
    if (!file) return
    form.digital_file = file
    fileName.value = file.name
}

const submit = () => {
    form.post(route('admin.library.store'))
}
</script>

<style scoped>
.field-label {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.375rem;
}
.req { color: #ef4444; margin-right: 0.15rem; }
.field-input {
    width: 100%;
    padding: 0.6rem 0.875rem;
    border-radius: 0.625rem;
    border: 1.5px solid #e5e7eb;
    font-size: 0.875rem;
    color: #111827;
    background: #fafafa;
    outline: none;
    transition: border-color 0.2s;
    font-family: inherit;
}
.field-input:focus { border-color: var(--color-brand-600); background: white; }
.field-error { border-color: #f87171 !important; background: #fef2f2 !important; }
.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }
.hint { color: #9ca3af; font-size: 0.7rem; margin-top: 0.25rem; display: block; }
</style>
