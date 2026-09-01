<template>
    <AppLayout :title="pageTitle">
        <div class="max-w-3xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.students.index')"
                    class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition"
                >
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <h1 class="text-xl font-bold text-gray-900">{{ pageTitle }}</h1>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <div class="space-y-6">

                    <!-- Akademik ma'lumotlar -->
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Akademik ma'lumotlar</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="field-label">O'quv yili <span class="req">*</span></label>
                                <select v-model="form.academic_year_id" class="field-input"
                                        :class="form.errors.academic_year_id ? 'field-error' : ''">
                                    <option value="">Tanlang</option>
                                    <option v-for="y in academicYears" :key="y.id" :value="y.id">
                                        {{ y.name }}{{ y.is_active ? ' (joriy)' : '' }}
                                    </option>
                                </select>
                                <p v-if="form.errors.academic_year_id" class="err">{{ form.errors.academic_year_id }}</p>
                            </div>
                            <div>
                                <label class="field-label">Kafedra</label>
                                <select v-model="form.department_id" class="field-input" @change="form.direction_id = ''">
                                    <option value="">Tanlang</option>
                                    <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name_uz }}</option>
                                </select>
                                <p class="hint">Avval kafedrani tanlang — yo'nalishlar shunga qarab chiqadi</p>
                            </div>
                            <div>
                                <label class="field-label">Yo'nalish <span class="req">*</span></label>
                                <select v-model="form.direction_id" class="field-input"
                                        :class="form.errors.direction_id ? 'field-error' : ''"
                                        :disabled="!form.department_id">
                                    <option value="">{{ form.department_id ? 'Tanlang' : 'Avval kafedrani tanlang' }}</option>
                                    <option v-for="d in filteredDirections" :key="d.id" :value="d.id">{{ d.name_uz }}</option>
                                </select>
                                <p v-if="form.department_id && !filteredDirections.length" class="hint text-amber-600">
                                    Bu kafedraga biriktirilgan yo'nalish topilmadi — Yo'nalishlar bo'limida kafedrani belgilang
                                </p>
                                <p v-if="form.errors.direction_id" class="err">{{ form.errors.direction_id }}</p>
                            </div>
                            <div>
                                <label class="field-label">Ta'lim darajasi <span class="req">*</span></label>
                                <select v-model="form.degree" class="field-input">
                                    <option value="bachelor">Bakalavr</option>
                                    <option value="master">Magistr</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Ta'lim shakli <span class="req">*</span></label>
                                <select v-model="form.study_form" class="field-input">
                                    <option value="full_time">Kunduzgi</option>
                                    <option value="evening">Kechki</option>
                                    <option value="distance">Sirtqi</option>
                                    <option value="online" disabled>🔒 Masofaviy (tez orada)</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Kurs <span class="req">*</span></label>
                                <select v-model.number="form.course_year" class="field-input">
                                    <option v-for="c in courseYearOptions" :key="c" :value="c">{{ c }}-kurs</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Holati <span class="req">*</span></label>
                                <select v-model="form.status" class="field-input">
                                    <option value="active">O'qimoqda</option>
                                    <option value="academic_leave">Akademik ta'til</option>
                                    <option value="expelled">Chetlashtirilgan</option>
                                    <option value="graduated">Bitirgan</option>
                                    <option value="transferred">Ko'chirilgan</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Moliyalashtirish turi <span class="req">*</span></label>
                                <select v-model="form.funding_type" class="field-input"
                                        :class="form.errors.funding_type ? 'field-error' : ''">
                                    <option value="contract">Kontrakt (pullik)</option>
                                    <option value="grant">Grant (bepul)</option>
                                </select>
                                <p class="hint" v-if="form.funding_type === 'contract' && !hasExistingContract">
                                    Saqlanganda ushbu talaba uchun avtomatik kontrakt shartnoma yaratiladi
                                </p>
                                <p class="hint" v-else-if="hasExistingContract">
                                    Bu talabaga allaqachon kontrakt tuzilgan — Kontraktlar bo'limida ko'ring
                                </p>
                                <p v-if="form.errors.funding_type" class="err">{{ form.errors.funding_type }}</p>
                            </div>
                            <div>
                                <label class="field-label">HEMIS ID</label>
                                <input v-model="form.hemis_id" type="text" class="field-input"
                                       :class="form.errors.hemis_id ? 'field-error' : ''">
                                <p class="hint">HEMIS tizimidagi ID (Excel import qilinganlarda avtomatik to'ladi)</p>
                                <p v-if="form.errors.hemis_id" class="err">{{ form.errors.hemis_id }}</p>
                            </div>
                            <div>
                                <label class="field-label">Talaba raqami</label>
                                <input v-model="form.student_number" type="text" class="field-input"
                                       :class="form.errors.student_number ? 'field-error' : ''">
                                <p class="hint">Universitetning ichki hisob raqami / ID karta raqami (ixtiyoriy, HEMIS ID'dan farqli)</p>
                                <p v-if="form.errors.student_number" class="err">{{ form.errors.student_number }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Shaxsiy ma'lumotlar -->
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Shaxsiy ma'lumotlar</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="field-label">Familiya <span class="req">*</span></label>
                                <input v-model="form.last_name" type="text" placeholder="PASSPORTDAGIDEK" class="field-input"
                                       :class="form.errors.last_name ? 'field-error' : ''"
                                       @input="form.last_name = toLatinUpper(form.last_name)">
                                <p v-if="form.errors.last_name" class="err">{{ form.errors.last_name }}</p>
                            </div>
                            <div>
                                <label class="field-label">Ism <span class="req">*</span></label>
                                <input v-model="form.first_name" type="text" placeholder="PASSPORTDAGIDEK" class="field-input"
                                       :class="form.errors.first_name ? 'field-error' : ''"
                                       @input="form.first_name = toLatinUpper(form.first_name)">
                                <p v-if="form.errors.first_name" class="err">{{ form.errors.first_name }}</p>
                            </div>
                            <div>
                                <label class="field-label">Sharifi</label>
                                <input v-model="form.middle_name" type="text" placeholder="PASSPORTDAGIDEK" class="field-input"
                                       @input="form.middle_name = toLatinUpper(form.middle_name)">
                            </div>
                            <div>
                                <label class="field-label">Jinsi</label>
                                <select v-model="form.gender" class="field-input">
                                    <option value="">Tanlang</option>
                                    <option value="male">Erkak</option>
                                    <option value="female">Ayol</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">JSHSHIR</label>
                                <input v-model="form.jshshir" type="text" maxlength="14" inputmode="numeric" class="field-input"
                                       :class="form.errors.jshshir ? 'field-error' : ''"
                                       @input="form.jshshir = form.jshshir.replace(/\D/g, '')">
                                <p v-if="form.errors.jshshir" class="err">{{ form.errors.jshshir }}</p>
                            </div>
                            <div>
                                <label class="field-label">Passport seriya va raqami</label>
                                <input v-model="form.passport_series" type="text" maxlength="9" placeholder="AB1234567"
                                       class="field-input" :class="form.errors.passport_series ? 'field-error' : ''"
                                       @input="form.passport_series = form.passport_series.toUpperCase().replace(/[^A-Z0-9]/g, '')">
                                <p v-if="form.errors.passport_series" class="err">{{ form.errors.passport_series }}</p>
                            </div>
                            <div>
                                <label class="field-label">Tug'ilgan sana</label>
                                <input v-model="birthDateInput" type="date" class="field-input" :max="maxBirthDate">
                                <p v-if="form.errors.birth_year" class="err">{{ form.errors.birth_year }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Aloqa -->
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Aloqa ma'lumotlari</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="field-label">Telefon</label>
                                <input ref="phoneRef" v-model="form.phone" type="tel" placeholder="+998 (XX) XXX-XX-XX"
                                       class="field-input" :class="form.errors.phone ? 'field-error' : ''">
                                <p v-if="form.errors.phone" class="err">{{ form.errors.phone }}</p>
                            </div>
                            <div>
                                <label class="field-label">Email</label>
                                <input v-model="form.email" type="email" class="field-input"
                                       :class="form.errors.email ? 'field-error' : ''">
                                <p v-if="form.errors.email" class="err">{{ form.errors.email }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="field-label">Manzil</label>
                                <textarea v-model="form.address" rows="2" class="field-input" style="resize:none"></textarea>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Tugmalar -->
                <div class="flex gap-3 mt-6">
                    <Link :href="route('admin.students.index')"
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

        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import IMask from 'imask'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    student:       { type: Object, default: null },
    academicYears: { type: Array,  default: () => [] },
    directions:    { type: Array,  default: () => [] },
    departments:   { type: Array,  default: () => [] },
})

const isEdit = computed(() => !!props.student)
const pageTitle = computed(() => isEdit.value ? 'Talaba ma\'lumotlarini tahrirlash' : 'Yangi talaba')

const activeAcademicYearId = props.academicYears.find(y => y.is_active)?.id ?? ''

// Tahrirlashda talabaning yo'nalishi bor-u, kafedrasi bo'sh bo'lishi mumkin
// (masalan, HEMIS import orqali qo'shilgan) — shu holatda yo'nalishning
// o'z kafedrasidan avtomatik aniqlab beramiz, aks holda Yo'nalish select'i
// "avval kafedrani tanlang" holatida qulflanib qolib, mavjud tanlov ko'rinmay qoladi.
const inferredDepartmentId = (() => {
    if (props.student?.department_id) return props.student.department_id
    if (props.student?.direction_id) {
        return props.directions.find(d => d.id === props.student.direction_id)?.department_id ?? ''
    }
    return ''
})()

const form = useForm({
    academic_year_id: props.student?.academic_year_id || activeAcademicYearId,
    direction_id:      props.student?.direction_id      || '',
    department_id:     inferredDepartmentId,
    hemis_id:          props.student?.hemis_id           || '',
    student_number:    props.student?.student_number     || '',
    first_name:        props.student?.first_name         || '',
    last_name:         props.student?.last_name           || '',
    middle_name:       props.student?.middle_name         || '',
    passport_series:   props.student?.passport_series     || '',
    jshshir:           props.student?.jshshir             || '',
    phone:             props.student?.phone               || '',
    email:             props.student?.email               || '',
    birth_day:         props.student?.birth_day           || '',
    birth_month:       props.student?.birth_month         || '',
    birth_year:        props.student?.birth_year          || '',
    gender:            props.student?.gender              || '',
    degree:            props.student?.degree              || 'bachelor',
    study_form:        props.student?.study_form          || 'full_time',
    course_year:       props.student?.course_year         || 1,
    status:            props.student?.status              || 'active',
    funding_type:      props.student?.funding_type        || 'contract',
    address:           props.student?.address             || '',
})

// Talabaga allaqachon kontrakt tuzilgan bo'lsa — "avtomatik yaratiladi"
// degan chalkash eslatma o'rniga buni ko'rsatamiz
const hasExistingContract = computed(() => !!props.student?.contract)

// Kafedra tanlanganda faqat o'sha kafedraga biriktirilgan yo'nalishlar ko'rsatiladi
const filteredDirections = computed(() => {
    if (!form.department_id) return []
    return props.directions.filter(d => d.department_id === form.department_id)
})

// Daraja + ta'lim shakliga qarab ruxsat etilgan kurslar:
// Magistr -> 1-2, Bakalavr+Kunduzgi -> 1-4, Bakalavr+Kechki/Sirtqi -> 1-5
const courseYearOptions = computed(() => {
    if (form.degree === 'master') return [1, 2]
    if (form.study_form === 'full_time') return [1, 2, 3, 4]
    return [1, 2, 3, 4, 5]
})

watch([() => form.degree, () => form.study_form], () => {
    const opts = courseYearOptions.value
    const max = opts[opts.length - 1]
    if (Number(form.course_year) > max) form.course_year = max
    if (!opts.includes(Number(form.course_year))) form.course_year = opts[0]
})

// Ism, familiya, sharifni passportdagidek — lotin katta harflarda kiritish
// (abituriyent ariza topshirish formasidagi bilan bir xil qoida, unda ham
// faqat lotin harflar + probel/defis/tutuq belgisi (o', g') qoldiriladi)
const toLatinUpper = (value) => value.toUpperCase().replace(/[^A-Z' -]/g, '')

// Tug'ilgan sana — bitta date input, DB'da esa alohida kun/oy/yil ustunlari
// bo'lgani uchun ular orasida ikki tomonlama aylantirib turamiz.
//
// MUHIM: bu oldin computed({get,set}) orqali qilingan edi, lekin ba'zi
// brauzerlarda (mas. Firefox) foydalanuvchi native date input'ning yil
// qismini raqamma-raqam kiritayotganda "input" hodisasi hali TO'LIQ
// bo'lmagan qiymat bilan otilardi (masalan yil uchun faqat "1" kiritilganda
// qiymat "1-10-27" bo'lib chiqardi — 4 xonali emas). getter esa buni qayta
// "yig'ib" xuddi shu formatsiz satrni brauzerga qaytarib yozardi va brauzer
// buni "yyyy-MM-dd" formatiga mos kelmaydi deb rad etardi — natijada input
// "qotib qolgan" holatga tushib, forma bilan ishlash imkonsiz bo'lib qolardi.
//
// Yechim: input qiymatini oddiy ref sifatida saqlaymiz (brauzer nima bersa,
// o'shani — hech qanday qayta formatlashsiz qabul qilamiz), va faqat TO'LIQ
// hamda TO'G'RI formatdagi (yyyy-MM-dd) qiymat kelganda uni form.birth_*
// maydonlariga o'tkazamiz. Noto'liq oraliq qiymatlar shunchaki e'tiborsiz
// qoldiriladi va foydalanuvchi kiritishni davom ettiraveradi.
const pad = (n) => String(n).padStart(2, '0')
const maxBirthDate = new Date().toISOString().slice(0, 10)

const initialBirthDate = (props.student?.birth_year && props.student?.birth_month && props.student?.birth_day)
    ? `${String(props.student.birth_year).padStart(4, '0')}-${pad(props.student.birth_month)}-${pad(props.student.birth_day)}`
    : ''

const birthDateInput = ref(initialBirthDate)

watch(birthDateInput, (value) => {
    if (!value) {
        form.birth_day = ''
        form.birth_month = ''
        form.birth_year = ''
        return
    }
    // Faqat to'liq va to'g'ri formatdagi qiymatni qabul qilamiz
    if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
        return
    }
    const [y, m, d] = value.split('-').map(Number)
    form.birth_year = y
    form.birth_month = m
    form.birth_day = d
})

// Telefon uchun IMask — Abituriyent ariza formasida ishlatilgan xuddi shu naqsh
const phoneRef = ref(null)
onMounted(() => {
    if (phoneRef.value) {
        const mask = IMask(phoneRef.value, { mask: '+{998} (00) 000-00-00' })
        if (form.phone) mask.value = form.phone
        mask.on('accept', () => { form.phone = mask.value })
    }
})

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.students.update', props.student.id))
    } else {
        form.post(route('admin.students.store'))
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
}
.field-input:focus { border-color: #0f3460; background: white; }
.field-input:disabled { opacity: 0.6; cursor: not-allowed; }
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
