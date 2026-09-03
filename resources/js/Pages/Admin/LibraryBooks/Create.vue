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
                            <button type="button" @click="$refs.coverInput.click()" class="btn-secondary">
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
                        <label class="field-label">Kitob nomi <span class="req">*</span></label>
                        <input v-model="form.title" type="text" placeholder="Masalan: Oʻtkan kunlar"
                               class="field-input" :class="form.errors.title ? 'field-error' : ''">
                        <p v-if="form.errors.title" class="err">{{ form.errors.title }}</p>
                    </div>
                    <div>
                        <label class="field-label">Muallif <span class="req">*</span></label>
                        <input v-model="form.author" type="text" placeholder="Masalan: Abdulla Qodiriy"
                               class="field-input" :class="form.errors.author ? 'field-error' : ''">
                        <p v-if="form.errors.author" class="err">{{ form.errors.author }}</p>
                    </div>
                    <div>
                        <label class="field-label">Kategoriya <span class="req">*</span></label>
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
                            :style="form.is_active ? 'background:linear-gradient(135deg,#0f3460,#533483)' : 'background:#e5e7eb'">
                        <span class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-all duration-300"
                              :class="form.is_active ? 'left-6' : 'left-1'"></span>
                    </button>
                </div>
            </div>

            <!-- Tugmalar -->
            <div class="flex gap-3">
                <Link :href="route('admin.library.index')"
                      class="btn-secondary flex-1 flex items-center justify-center gap-2">
                    <Icon icon="mdi:close" class="w-4 h-4" />
                    Bekor qilish
                </Link>
                <button type="button" @click="submit" :disabled="form.processing" class="btn-primary flex-1">
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
.req { color: #ef4444; }
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
.field-input:focus { border-color: #0f3460; background: white; }
.field-error { border-color: #f87171 !important; background: #fef2f2 !important; }
.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }
.hint { color: #9ca3af; font-size: 0.7rem; margin-top: 0.25rem; display: block; }

.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.5rem;
    border-radius: 0.75rem;
    background: linear-gradient(135deg, #0f3460, #533483);
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-primary:hover { box-shadow: 0 6px 20px rgba(15,52,96,0.3); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    background: white;
    color: #374151;
    font-size: 0.875rem;
    font-weight: 600;
    border: 1.5px solid #e5e7eb;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}
.btn-secondary:hover { background: #f9fafb; }
</style>
