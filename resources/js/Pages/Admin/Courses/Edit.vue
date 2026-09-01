<template>
    <AppLayout :title="pageTitle">
        <div class="max-w-3xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="isEdit ? route('admin.courses.show', course.id) : route('admin.courses.index')"
                      class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <h1 class="text-xl font-bold text-gray-900">{{ pageTitle }}</h1>
            </div>

            <!-- Asosiy ma'lumot -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <p class="text-sm font-bold text-gray-700">Asosiy ma'lumot</p>

                <!-- Muqova -->
                <div>
                    <label class="field-label">Muqova rasm</label>
                    <div class="flex items-center gap-4">
                        <div class="w-28 h-20 rounded-xl bg-gray-100 overflow-hidden flex items-center justify-center flex-shrink-0 border border-gray-200">
                            <img v-if="thumbnailPreview" :src="thumbnailPreview" class="w-full h-full object-cover" alt="">
                            <Icon v-else icon="mdi:image-outline" class="w-6 h-6 text-gray-400" />
                        </div>
                        <div>
                            <input ref="thumbnailInput" type="file" accept="image/*" class="hidden" @change="onThumbnailChange">
                            <button type="button" @click="$refs.thumbnailInput.click()" class="btn-secondary">
                                <Icon icon="mdi:upload-outline" class="w-4 h-4" />
                                Rasm tanlash
                            </button>
                            <p class="hint mt-1">JPG/PNG, maksimum 4 MB</p>
                        </div>
                    </div>
                    <p v-if="form.errors.thumbnail" class="err">{{ form.errors.thumbnail }}</p>
                </div>

                <!-- Nomlar -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="field-label">Kurs nomi (O'zbek) <span class="req">*</span></label>
                        <input v-model="form.title_uz" type="text" placeholder="Masalan: Python dasturlash asoslari"
                               class="field-input" :class="form.errors.title_uz ? 'field-error' : ''">
                        <p v-if="form.errors.title_uz" class="err">{{ form.errors.title_uz }}</p>
                    </div>
                    <div>
                        <label class="field-label">Nomi (Rus)</label>
                        <input v-model="form.title_ru" type="text" placeholder="Ixtiyoriy" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Nomi (Ingliz)</label>
                        <input v-model="form.title_en" type="text" placeholder="Ixtiyoriy" class="field-input">
                    </div>
                </div>

                <!-- Kategoriya -->
                <div>
                    <label class="field-label">Kategoriya</label>
                    <select v-model="form.category_id" class="field-input">
                        <option value="">Tanlang</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name_uz }}</option>
                    </select>
                </div>

                <!-- Tavsif -->
                <div>
                    <label class="field-label">Tavsif (O'zbek)</label>
                    <textarea v-model="form.description_uz" rows="4" placeholder="Kurs haqida to'liq ma'lumot"
                              class="field-input"></textarea>
                </div>

                <!-- Nimalarni o'rganasiz / Talablar -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Nimalarni o'rganasiz</label>
                        <textarea v-model="form.what_you_learn" rows="4" placeholder="Har bir qatorga bitta band yozing"
                                  class="field-input"></textarea>
                        <p class="hint">Har bir qator alohida band sifatida saqlanadi</p>
                    </div>
                    <div>
                        <label class="field-label">Talablar (prerekvizit)</label>
                        <textarea v-model="form.requirements" rows="4" placeholder="Har bir qatorga bitta talab yozing"
                                  class="field-input"></textarea>
                        <p class="hint">Har bir qator alohida band sifatida saqlanadi</p>
                    </div>
                </div>
            </div>

            <!-- Parametrlar -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <p class="text-sm font-bold text-gray-700">Parametrlar</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Kurs turi <span class="req">*</span></label>
                        <select v-model="form.type" class="field-input">
                            <option value="open">Ochiq (hamma ko'radi)</option>
                            <option value="free">Bepul (ro'yxatdan o'tib ko'radi)</option>
                            <option value="paid">Pullik</option>
                            <option value="students_only">Faqat talabalar uchun</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Daraja (level) <span class="req">*</span></label>
                        <select v-model="form.level" class="field-input">
                            <option value="beginner">Boshlang'ich</option>
                            <option value="intermediate">O'rta</option>
                            <option value="advanced">Yuqori</option>
                            <option value="expert">Ekspert</option>
                        </select>
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
                        <label class="field-label">Ta'lim darajasi (kimlar uchun)</label>
                        <select v-model="form.degree" class="field-input">
                            <option value="both">Bakalavr va Magistr</option>
                            <option value="bachelor">Faqat Bakalavr</option>
                            <option value="master">Faqat Magistr</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Narxi (so'm) <span class="req">*</span></label>
                        <input v-model.number="form.price" type="number" min="0" step="1000" class="field-input"
                               :class="form.errors.price ? 'field-error' : ''">
                        <p v-if="form.errors.price" class="err">{{ form.errors.price }}</p>
                    </div>
                    <div>
                        <label class="field-label">Chegirmali narx</label>
                        <input v-model.number="form.discount_price" type="number" min="0" step="1000" class="field-input"
                               :class="form.errors.discount_price ? 'field-error' : ''">
                        <p v-if="form.errors.discount_price" class="err">{{ form.errors.discount_price }}</p>
                    </div>
                    <div>
                        <label class="field-label">Davomiyligi (soat)</label>
                        <input v-model.number="form.duration_hours" type="number" min="0" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Holati <span class="req">*</span></label>
                        <select v-model="form.status" class="field-input">
                            <option value="draft">Qoralama</option>
                            <option value="published">Nashr qilingan</option>
                            <option value="archived">Arxivlangan</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 border border-gray-100">
                        <div>
                            <p class="text-sm font-semibold text-gray-700">Sertifikat beriladi</p>
                            <p class="text-xs text-gray-400 mt-0.5">Kursni tugatgan talabaga sertifikat chiqariladi</p>
                        </div>
                        <button type="button" @click="form.has_certificate = !form.has_certificate"
                                class="relative w-11 h-6 rounded-full transition-all duration-300 flex-shrink-0"
                                :style="form.has_certificate ? 'background:linear-gradient(135deg,#0f3460,#533483)' : 'background:#e5e7eb'">
                            <span class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-all duration-300"
                                  :class="form.has_certificate ? 'left-6' : 'left-1'"></span>
                        </button>
                    </div>
                    <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 border border-gray-100">
                        <div>
                            <p class="text-sm font-semibold text-gray-700">Ketma-ket o'tish</p>
                            <p class="text-xs text-gray-400 mt-0.5">Keyingi darsga o'tish uchun avvalgisi tugallanishi shart</p>
                        </div>
                        <button type="button" @click="form.is_sequential = !form.is_sequential"
                                class="relative w-11 h-6 rounded-full transition-all duration-300 flex-shrink-0"
                                :style="form.is_sequential ? 'background:linear-gradient(135deg,#0f3460,#533483)' : 'background:#e5e7eb'">
                            <span class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-all duration-300"
                                  :class="form.is_sequential ? 'left-6' : 'left-1'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- O'qituvchilar -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-3"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <p class="text-sm font-bold text-gray-700">O'qituvchilar</p>
                <div v-if="!teachers.length" class="text-sm text-gray-400">O'qituvchi roli bilan foydalanuvchi topilmadi</div>
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <label v-for="t in teachers" :key="t.id"
                           class="flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-100 hover:bg-gray-50 cursor-pointer text-sm">
                        <input type="checkbox" :value="t.id" v-model="form.instructor_ids" class="rounded">
                        {{ t.full_name }}
                    </label>
                </div>
            </div>

            <!-- Yo'nalishlar va guruhlar (target auditoriya) -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div>
                    <p class="text-sm font-bold text-gray-700 mb-1">Yo'nalishlar</p>
                    <p class="text-xs text-gray-400 mb-3">Kursni ko'rish/topshirish belgilangan yo'nalishlar bilan cheklanadi (bo'sh bo'lsa — cheklovsiz)</p>
                    <div v-if="!directions.length" class="text-sm text-gray-400">Yo'nalish topilmadi</div>
                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-52 overflow-y-auto">
                        <label v-for="d in directions" :key="d.id"
                               class="flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-100 hover:bg-gray-50 cursor-pointer text-sm">
                            <input type="checkbox" :value="d.id" v-model="form.direction_ids" class="rounded">
                            {{ d.name_uz }}
                        </label>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-700 mb-1">Guruhlar</p>
                    <p class="text-xs text-gray-400 mb-3">Kursni faqat belgilangan guruhlarga tayinlash uchun</p>
                    <div v-if="!groups.length" class="text-sm text-gray-400">Guruh topilmadi</div>
                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-52 overflow-y-auto">
                        <label v-for="g in groups" :key="g.id"
                               class="flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-100 hover:bg-gray-50 cursor-pointer text-sm">
                            <input type="checkbox" :value="g.id" v-model="form.group_ids" class="rounded">
                            {{ g.name }}
                        </label>
                    </div>
                </div>
            </div>

            <!-- Tugmalar -->
            <div class="flex gap-3">
                <Link :href="isEdit ? route('admin.courses.show', course.id) : route('admin.courses.index')"
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
import { computed, ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    course:     { type: Object, default: null },
    categories: { type: Array, default: () => [] },
    directions: { type: Array, default: () => [] },
    groups:     { type: Array, default: () => [] },
    teachers:   { type: Array, default: () => [] },
})

const isEdit = computed(() => !!props.course)
const pageTitle = computed(() => isEdit.value ? 'Kursni tahrirlash' : 'Yangi kurs')

const arrayToLines = (arr) => Array.isArray(arr) ? arr.join('\n') : ''

const form = useForm({
    category_id:      props.course?.category_id      || '',
    title_uz:         props.course?.title_uz         || '',
    title_ru:         props.course?.title_ru         || '',
    title_en:         props.course?.title_en         || '',
    description_uz:   props.course?.description_uz   || '',
    what_you_learn:   arrayToLines(props.course?.what_you_learn),
    requirements:     arrayToLines(props.course?.requirements),
    thumbnail:        null,
    type:             props.course?.type             || 'open',
    level:             props.course?.level            || 'beginner',
    language:         props.course?.language         || 'uz',
    degree:           props.course?.degree           || 'both',
    price:            props.course?.price            ?? 0,
    discount_price:   props.course?.discount_price    ?? null,
    duration_hours:   props.course?.duration_hours    ?? 0,
    has_certificate:  props.course?.has_certificate   ?? true,
    is_sequential:    props.course?.is_sequential     ?? true,
    status:           props.course?.status           || 'draft',
    instructor_ids:   (props.course?.instructors ?? []).map(i => i.id),
    direction_ids:    (props.course?.directions ?? []).map(d => d.id),
    group_ids:        (props.course?.groups ?? []).map(g => g.id),
})

const thumbnailInput = ref(null)
const thumbnailPreview = ref(props.course?.thumbnail_url || null)

const onThumbnailChange = (e) => {
    const file = e.target.files[0]
    if (!file) return
    form.thumbnail = file
    thumbnailPreview.value = URL.createObjectURL(file)
}

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.courses.update', props.course.id))
    } else {
        form.post(route('admin.courses.store'))
    }
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
    appearance: auto;
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
