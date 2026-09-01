<template>
    <WebLayout :settings="settings" :faculties="faculties">

        <!-- Hero -->
        <div style="background: linear-gradient(135deg, #0f3460, #16213e, #533483)" class="py-12">
            <div class="container mx-auto px-4 text-center">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-medium text-white/80 mb-4"
                     style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2)">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400" style="animation: pulse 2s infinite"></span>
                    Qabul 2025–2026 ochiq!
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Talabalikka ilk qadam</h1>
                <p class="text-sm text-white/60">Quyidagi formani to'ldirib ariza topshiring</p>
            </div>
        </div>

        <div class="py-10" style="background: #f8fafc; min-height: 70vh">
            <div class="container mx-auto px-4">
                <div class="max-w-xl mx-auto">

                    <!-- Step indicator -->
                    <div class="flex items-center justify-center mb-8">
                        <template v-for="(step, i) in steps" :key="i">
                            <div class="flex flex-col items-center gap-1">
                                <div
                                    class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300"
                                    :style="currentStep > i + 1
                                        ? 'background:#22c55e; color:white'
                                        : currentStep === i + 1
                                            ? 'background:linear-gradient(135deg,#0f3460,#533483); color:white'
                                            : 'background:#e5e7eb; color:#9ca3af'"
                                >
                                    <Icon v-if="currentStep > i + 1" icon="mdi:check" class="w-4 h-4" />
                                    <span v-else>{{ i + 1 }}</span>
                                </div>
                                <span class="text-xs font-medium hidden sm:block"
                                      :style="currentStep === i + 1 ? 'color:#0f3460' : 'color:#9ca3af'">
                                    {{ step }}
                                </span>
                            </div>
                            <div v-if="i < steps.length - 1"
                                 class="w-12 sm:w-16 h-0.5 mx-1 mb-4 transition-all duration-500"
                                 :style="currentStep > i + 1 ? 'background:#22c55e' : 'background:#e5e7eb'"
                            />
                        </template>
                    </div>

                    <!-- Card -->
                    <div style="background:white; border-radius:1rem; border:1px solid #e5e7eb; box-shadow:0 2px 12px rgba(0,0,0,0.06)">

                        <!-- STEP 1: Ta'lim turi -->
                        <div v-if="currentStep === 1" class="p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-2">Qaysi turdagi ariza topshirmoqchisiz?</h2>
                            <p class="text-sm text-gray-500 mb-6">O'zingizga mos ta'lim turini tanlang</p>

                            <div class="flex flex-col gap-3">
                                <button
                                    v-for="type in educationTypes"
                                    :key="type.value"
                                    type="button"
                                    @click="selectEducationType(type.value)"
                                    class="flex items-center justify-between w-full px-4 py-3.5 rounded-xl text-left transition-all duration-200"
                                    :style="form.education_type === type.value
                                        ? 'border:2px solid #0f3460; background:linear-gradient(135deg,#eff6ff,#f5f3ff)'
                                        : 'border:1.5px solid #e5e7eb; background:#fafafa'"
                                >
                                    <div>
                                        <span class="font-bold text-gray-800 text-sm">{{ type.label }}</span>
                                        <span class="text-xs ml-2"
                                              :style="form.education_type === type.value ? 'color:#0f3460' : 'color:#9ca3af'">
                                            ({{ type.desc }})
                                        </span>
                                    </div>
                                    <div v-if="form.education_type === type.value"
                                         class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0"
                                         style="background:linear-gradient(135deg,#0f3460,#533483)"
                                    >
                                        <Icon icon="mdi:check" class="w-3 h-3 text-white" />
                                    </div>
                                </button>
                            </div>
                            <p v-if="errors.education_type" class="err mt-3">{{ errors.education_type }}</p>
                        </div>

                        <!-- STEP 2: Yo'nalish va ta'lim shakli -->
                        <div v-if="currentStep === 2" class="p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-6">Yo'nalish va ta'lim shakli</h2>

                            <!-- Yo'nalish -->
                            <div class="mb-5">
                                <label class="field-label">Yo'nalish <span class="req">*</span></label>
                                <select v-model="form.direction_id" class="field-input" :class="errors.direction_id ? 'field-error' : ''">
                                    <option value="">Tanlang</option>
                                    <option v-for="d in filteredDirectionsByType" :key="d.id" :value="d.id">
                                        {{ d.name_uz }}
                                    </option>
                                </select>
                                <p v-if="errors.direction_id" class="err">{{ errors.direction_id }}</p>
                            </div>

                            <!-- Ta'lim shakli -->
                            <div>
                                <label class="field-label">Ta'lim shakli <span class="req">*</span></label>
                                <div class="flex flex-col gap-2.5 mt-1">
                                    <button
                                        v-for="sf in availableStudyForms"
                                        :key="sf.value"
                                        type="button"
                                        :disabled="sf.disabled"
                                        @click="!sf.disabled && (form.study_form = sf.value)"
                                        class="flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200"
                                        :style="sf.disabled
                                            ? 'border:1.5px solid #e5e7eb; background:#f9fafb; opacity:0.5; cursor:not-allowed'
                                            : form.study_form === sf.value
                                                ? 'border:2px solid #0f3460; background:linear-gradient(135deg,#eff6ff,#f5f3ff)'
                                                : 'border:1.5px solid #e5e7eb; background:#fafafa; cursor:pointer'"
                                    >
                                        <div class="flex items-center gap-3">
                                            <Icon :icon="sf.icon" class="w-5 h-5"
                                                  :style="form.study_form === sf.value ? 'color:#0f3460' : 'color:#9ca3af'" />
                                            <div class="text-left">
                                                <p class="text-sm font-semibold"
                                                   :style="form.study_form === sf.value ? 'color:#0f3460' : 'color:#374151'">
                                                    {{ sf.label }}
                                                </p>
                                                <p class="text-xs" :style="sf.disabled ? 'color:#ef4444' : 'color:#9ca3af'">
                                                    {{ sf.disabled ? 'Hozircha mavjud emas' : sf.desc }}
                                                </p>
                                            </div>
                                        </div>
                                        <div v-if="form.study_form === sf.value && !sf.disabled"
                                             class="w-5 h-5 rounded-full flex items-center justify-center"
                                             style="background:linear-gradient(135deg,#0f3460,#533483)"
                                        >
                                            <Icon icon="mdi:check" class="w-3 h-3 text-white" />
                                        </div>
                                        <Icon v-if="sf.disabled" icon="mdi:lock-outline" class="w-4 h-4" style="color:#d1d5db" />
                                    </button>
                                </div>
                                <p v-if="errors.study_form" class="err">{{ errors.study_form }}</p>
                            </div>
                        </div>

                        <!-- STEP 3: Shaxsiy ma'lumotlar -->
                        <div v-if="currentStep === 3" class="p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-6">Shaxsiy ma'lumotlar</h2>

                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="field-label">Familiyangiz (lotin alifbosida) <span class="req">*</span></label>
                                    <input v-model="form.last_name" type="text" placeholder="PASSPORTDAGIDEK"
                                           class="field-input" :class="errors.last_name ? 'field-error' : ''"
                                           @input="form.last_name = form.last_name.toUpperCase()">
                                    <p v-if="errors.last_name" class="err">{{ errors.last_name }}</p>
                                </div>

                                <div>
                                    <label class="field-label">Ismingiz (lotin alifbosida) <span class="req">*</span></label>
                                    <input v-model="form.first_name" type="text" placeholder="PASSPORTDAGIDEK"
                                           class="field-input" :class="errors.first_name ? 'field-error' : ''"
                                           @input="form.first_name = form.first_name.toUpperCase()">
                                    <p v-if="errors.first_name" class="err">{{ errors.first_name }}</p>
                                </div>

                                <div>
                                    <label class="field-label">Otangizning ismi (lotin alifbosida) <span class="req">*</span></label>
                                    <input v-model="form.middle_name" type="text" placeholder="PASSPORTDAGIDEK"
                                           class="field-input" :class="errors.middle_name ? 'field-error' : ''"
                                           @input="form.middle_name = form.middle_name.toUpperCase()">
                                    <p v-if="errors.middle_name" class="err">{{ errors.middle_name }}</p>
                                </div>

                                <!-- Tug'ilgan sana: kun / oy / yil -->
                                <div>
                                    <label class="field-label">Tug'ilgan sana <span class="req">*</span></label>
                                    <div class="grid grid-cols-3 gap-3">
                                        <div>
                                            <input v-model="form.birth_day" type="number" placeholder="Kun"
                                                   min="1" max="31"
                                                   class="field-input" :class="errors.birth_day ? 'field-error' : ''">
                                        </div>
                                        <div>
                                            <select v-model="form.birth_month" class="field-input" :class="errors.birth_month ? 'field-error' : ''">
                                                <option value="">Oy</option>
                                                <option v-for="(m, i) in months" :key="i" :value="i + 1">{{ m }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <input v-model="form.birth_year" type="number" placeholder="Yil"
                                                   min="1950" :max="new Date().getFullYear() - 14"
                                                   class="field-input" :class="errors.birth_year ? 'field-error' : ''">
                                        </div>
                                    </div>
                                    <p v-if="errors.birth_year || errors.birth_month || errors.birth_day" class="err">
                                        Tug'ilgan sanani to'liq kiriting
                                    </p>
                                </div>

                                <!-- Jins -->
                                <div>
                                    <label class="field-label">Jins <span class="req">*</span></label>
                                    <div class="flex gap-6 mt-2">
                                        <label v-for="g in genders" :key="g.value" class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" v-model="form.gender" :value="g.value" class="hidden">
                                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                                                 :style="form.gender === g.value ? 'border-color:#0f3460' : 'border-color:#d1d5db'">
                                                <div v-if="form.gender === g.value"
                                                     class="w-2.5 h-2.5 rounded-full" style="background:#0f3460"></div>
                                            </div>
                                            <span class="text-sm text-gray-700">{{ g.label }}</span>
                                        </label>
                                    </div>
                                    <p v-if="errors.gender" class="err">{{ errors.gender }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 4: Hujjat va aloqa -->
                        <div v-if="currentStep === 4" class="p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-6">Hujjat va aloqa</h2>

                            <div class="grid grid-cols-1 gap-4">

                                <!-- Pasport -->
                                <div>
                                    <label class="field-label">Passport seriyasi hamda raqami <span class="req">*</span></label>
                                    <input v-model="form.passport_series" type="text" placeholder="AA1234567"
                                           maxlength="9" class="field-input" :class="errors.passport_series ? 'field-error' : ''"
                                           @input="form.passport_series = form.passport_series.toUpperCase()">
                                    <p v-if="errors.passport_series" class="err">{{ errors.passport_series }}</p>
                                </div>

                                <!-- Magistr uchun fayllar -->
                                <template v-if="form.education_type === 'master'">
                                    <div>
                                        <label class="field-label">Pasport nusxasi (rasm yoki PDF) <span class="req">*</span></label>
                                        <div class="file-upload-area" :class="errors.passport_file ? 'file-error' : ''"
                                             @click="$refs.passportFileRef.click()"
                                             @dragover.prevent @drop.prevent="handleDrop($event, 'passport_file')">
                                            <input ref="passportFileRef" type="file" accept="image/*,.pdf" class="hidden"
                                                   @change="handleFile($event, 'passport_file')">
                                            <div v-if="!fileNames.passport_file" class="flex flex-col items-center gap-2">
                                                <Icon icon="mdi:cloud-upload-outline" class="w-8 h-8" style="color:#9ca3af" />
                                                <p class="text-sm text-gray-500">Fayl yuklash uchun bosing yoki sudrab tashlang</p>
                                                <p class="text-xs text-gray-400">JPG, PNG, PDF — max 5MB</p>
                                            </div>
                                            <div v-else class="flex items-center gap-3">
                                                <Icon icon="mdi:file-check-outline" class="w-6 h-6" style="color:#22c55e" />
                                                <span class="text-sm text-gray-700 truncate">{{ fileNames.passport_file }}</span>
                                                <button type="button" @click.stop="clearFile('passport_file')"
                                                        class="ml-auto flex-shrink-0">
                                                    <Icon icon="mdi:close-circle" class="w-5 h-5" style="color:#ef4444" />
                                                </button>
                                            </div>
                                        </div>
                                        <p v-if="errors.passport_file" class="err">{{ errors.passport_file }}</p>
                                    </div>

                                    <div>
                                        <label class="field-label">Diplom nusxasi (rasm yoki PDF) <span class="req">*</span></label>
                                        <div class="file-upload-area" :class="errors.diploma_file ? 'file-error' : ''"
                                             @click="$refs.diplomaFileRef.click()"
                                             @dragover.prevent @drop.prevent="handleDrop($event, 'diploma_file')">
                                            <input ref="diplomaFileRef" type="file" accept="image/*,.pdf" class="hidden"
                                                   @change="handleFile($event, 'diploma_file')">
                                            <div v-if="!fileNames.diploma_file" class="flex flex-col items-center gap-2">
                                                <Icon icon="mdi:cloud-upload-outline" class="w-8 h-8" style="color:#9ca3af" />
                                                <p class="text-sm text-gray-500">Fayl yuklash uchun bosing yoki sudrab tashlang</p>
                                                <p class="text-xs text-gray-400">JPG, PNG, PDF — max 5MB</p>
                                            </div>
                                            <div v-else class="flex items-center gap-3">
                                                <Icon icon="mdi:file-check-outline" class="w-6 h-6" style="color:#22c55e" />
                                                <span class="text-sm text-gray-700 truncate">{{ fileNames.diploma_file }}</span>
                                                <button type="button" @click.stop="clearFile('diploma_file')" class="ml-auto flex-shrink-0">
                                                    <Icon icon="mdi:close-circle" class="w-5 h-5" style="color:#ef4444" />
                                                </button>
                                            </div>
                                        </div>
                                        <p v-if="errors.diploma_file" class="err">{{ errors.diploma_file }}</p>
                                    </div>

                                    <div>
                                        <label class="field-label">Diplom ilovasi nusxasi (rasm yoki PDF) <span class="req">*</span></label>
                                        <div class="file-upload-area" :class="errors.diploma_appendix_file ? 'file-error' : ''"
                                             @click="$refs.diplomaAppendixRef.click()"
                                             @dragover.prevent @drop.prevent="handleDrop($event, 'diploma_appendix_file')">
                                            <input ref="diplomaAppendixRef" type="file" accept="image/*,.pdf" class="hidden"
                                                   @change="handleFile($event, 'diploma_appendix_file')">
                                            <div v-if="!fileNames.diploma_appendix_file" class="flex flex-col items-center gap-2">
                                                <Icon icon="mdi:cloud-upload-outline" class="w-8 h-8" style="color:#9ca3af" />
                                                <p class="text-sm text-gray-500">Fayl yuklash uchun bosing yoki sudrab tashlang</p>
                                                <p class="text-xs text-gray-400">JPG, PNG, PDF — max 5MB</p>
                                            </div>
                                            <div v-else class="flex items-center gap-3">
                                                <Icon icon="mdi:file-check-outline" class="w-6 h-6" style="color:#22c55e" />
                                                <span class="text-sm text-gray-700 truncate">{{ fileNames.diploma_appendix_file }}</span>
                                                <button type="button" @click.stop="clearFile('diploma_appendix_file')" class="ml-auto flex-shrink-0">
                                                    <Icon icon="mdi:close-circle" class="w-5 h-5" style="color:#ef4444" />
                                                </button>
                                            </div>
                                        </div>
                                        <p v-if="errors.diploma_appendix_file" class="err">{{ errors.diploma_appendix_file }}</p>
                                    </div>
                                </template>

                                <!-- Viloyat / Tuman -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="field-label">Qaysi viloyatdansiz? <span class="req">*</span></label>
                                        <select v-model="form.region_id" class="field-input"
                                                :class="errors.region_id ? 'field-error' : ''"
                                                @change="form.district_id = ''">
                                            <option value="">Tanlang</option>
                                            <option v-for="r in regions" :key="r.id" :value="r.id">{{ r.name_uz }}</option>
                                        </select>
                                        <p v-if="errors.region_id" class="err">{{ errors.region_id }}</p>
                                    </div>
                                    <div>
                                        <label class="field-label">Tuman yoki shahar <span class="req">*</span></label>
                                        <select v-model="form.district_id" class="field-input"
                                                :class="errors.district_id ? 'field-error' : ''"
                                                :disabled="!form.region_id">
                                            <option value="">Tanlang</option>
                                            <option v-for="d in selectedDistricts" :key="d.id" :value="d.id">{{ d.name_uz }}</option>
                                        </select>
                                        <p v-if="errors.district_id" class="err">{{ errors.district_id }}</p>
                                    </div>
                                </div>

                                <!-- Telefon -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="field-label">Telefon raqamingiz <span class="req">*</span></label>
                                        <input
                                            ref="phoneRef"
                                            v-model="form.phone"
                                            type="tel"
                                            placeholder="+998 (XX) XXX-XX-XX"
                                            class="field-input"
                                            :class="errors.phone ? 'field-error' : ''"
                                        >
                                        <p v-if="errors.phone" class="err">{{ errors.phone }}</p>
                                    </div>
                                    <div>
                                        <label class="field-label">Qo'shimcha raqam</label>
                                        <input
                                            ref="extraPhoneRef"
                                            v-model="form.extra_phone"
                                            type="tel"
                                            placeholder="+998 (XX) XXX-XX-XX"
                                            class="field-input"
                                        >
                                    </div>
                                </div>

                            </div>

                            <!-- Diqqat -->
                            <div class="mt-4 p-3.5 rounded-xl flex items-start gap-2.5"
                                 style="background:#fff7ed; border:1px solid #fed7aa">
                                <Icon icon="mdi:information-outline" class="w-4 h-4 flex-shrink-0 mt-0.5" style="color:#f97316" />
                                <p class="text-xs leading-relaxed" style="color:#c2410c">
                                    <strong>DIQQAT!!!</strong> Doimiy aloqada bo'lgan telefon raqamingizni kiriting va
                                    qabul bo'yicha yuborilgan SMS kod va xabarnomalarni saqlab qo'ying
                                </p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-6 py-4 flex items-center justify-between"
                             style="border-top:1px solid #f3f4f6; background:#fafafa; border-radius:0 0 1rem 1rem">
                            <button v-if="currentStep > 1" type="button" @click="currentStep--" class="btn-secondary">
                                <Icon icon="mdi:arrow-left" class="w-4 h-4" />
                                Orqaga
                            </button>
                            <div v-else></div>

                            <button v-if="currentStep < 4" type="button" @click="nextStep" class="btn-primary">
                                Keyingi
                                <Icon icon="mdi:arrow-right" class="w-4 h-4" />
                            </button>
                            <button v-else type="button" @click="submit" :disabled="form.processing" class="btn-primary">
                                <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                                <Icon v-else icon="mdi:send" class="w-4 h-4" />
                                {{ form.processing ? 'Yuborilmoqda...' : 'Arizani yuborish' }}
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </WebLayout>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { useToast } from 'vue-toastification'
import IMask from 'imask'
import WebLayout from '@/Layouts/WebLayout.vue'

const props = defineProps({
    faculties: { type: Array, default: () => [] },
    regions:   { type: Array, default: () => [] },
    settings:  { type: Object, default: () => ({}) },
})

const toast = useToast()
const currentStep   = ref(1)
const phoneRef      = ref(null)
const extraPhoneRef = ref(null)

const phoneMaskOptions = { mask: '+{998} (00) 000-00-00' }

// Step 4 ga o'tganda mask qo'llanadi
watch(currentStep, async (val) => {
    if (val === 4) {
        await nextTick()
        if (phoneRef.value) {
            const m = IMask(phoneRef.value, phoneMaskOptions)
            m.on('accept', () => { form.phone = m.value })
        }
        if (extraPhoneRef.value) {
            const m = IMask(extraPhoneRef.value, phoneMaskOptions)
            m.on('accept', () => { form.extra_phone = m.value })
        }
    }
})

const steps = ["Ta'lim turi", "Yo'nalish", "Shaxsiy", "Hujjat"]

const educationTypes = [
    { value: 'bachelor', label: 'Bakalavr',       desc: '1-kursga kirish arizasi' },
    { value: 'master',   label: 'Magistratura',    desc: '2 yillik magistr dasturi' },
    { value: 'transfer', label: 'Transfer',        desc: "Boshqa universitetdan YAU ga o'qishni ko'chirish" },
    { value: 'second',   label: 'Ikkinchi mutaxassislik', desc: 'Parallel diplom dasturi' },
]

const allStudyForms = [
    { value: 'full_time', label: 'Kunduzgi',  desc: "Kunduzi o'qish",   icon: 'mdi:weather-sunny',  disabled: false },
    { value: 'evening',   label: 'Kechki',    desc: "Kechqurun o'qish", icon: 'mdi:weather-night',  disabled: false },
    { value: 'distance',  label: 'Masofaviy', desc: "Online ta'lim",    icon: 'mdi:laptop',         disabled: true  },
]

// Magistrda faqat Kunduzgi
const availableStudyForms = computed(() => {
    if (form.education_type === 'master') {
        return allStudyForms.filter(sf => sf.value === 'full_time')
    }
    return allStudyForms
})

const months = ['Yanvar','Fevral','Mart','Aprel','May','Iyun','Iyul','Avgust','Sentabr','Oktabr','Noyabr','Dekabr']
const genders = [
    { value: 'male',   label: 'Erkak' },
    { value: 'female', label: 'Ayol' },
]

const form = useForm({
    education_type:        '',
    direction_id:          '',
    study_form:            '',
    first_name:            '',
    last_name:             '',
    middle_name:           '',
    birth_year:            '',
    birth_month:           '',
    birth_day:             '',
    gender:                '',
    passport_series:       '',
    passport_file:         null,
    diploma_file:          null,
    diploma_appendix_file: null,
    region_id:             '',
    district_id:           '',
    phone:                 '',
    extra_phone:           '',
})

// Fayl nomlari ko'rsatish uchun
const fileNames = ref({
    passport_file:         '',
    diploma_file:          '',
    diploma_appendix_file: '',
})

const errors = computed(() => form.errors)

// Magistr uchun faqat magistr yo'nalishlari
const filteredDirectionsByType = computed(() => {
    const all = props.faculties.flatMap(f => (f.directions || []))
    if (form.education_type === 'master') {
        return all.filter(d => d.degree === 'master')
    }
    return all.filter(d => d.degree === 'bachelor')
})

const selectedDistricts = computed(() => {
    if (!form.region_id) return []
    return props.regions.find(r => r.id == form.region_id)?.districts || []
})

// Ta'lim turi tanlaganda study_form va direction reset
const selectEducationType = (value) => {
    form.education_type = value
    form.study_form = ''
    form.direction_id = ''
    // Magistrda avtomatik Kunduzgi
    if (value === 'master') {
        form.study_form = 'full_time'
    }
}

// Fayl yuklash
const handleFile = (event, field) => {
    const file = event.target.files[0]
    if (!file) return
    if (file.size > 5 * 1024 * 1024) {
        toast.error("Fayl hajmi 5MB dan oshmasligi kerak!")
        return
    }
    form[field] = file
    fileNames.value[field] = file.name
}

const handleDrop = (event, field) => {
    const file = event.dataTransfer.files[0]
    if (!file) return
    if (file.size > 5 * 1024 * 1024) {
        toast.error("Fayl hajmi 5MB dan oshmasligi kerak!")
        return
    }
    form[field] = file
    fileNames.value[field] = file.name
}

const clearFile = (field) => {
    form[field] = null
    fileNames.value[field] = ''
}

// Step validatsiya
const nextStep = () => {
    form.clearErrors()

    if (currentStep.value === 1) {
        if (!form.education_type) {
            form.setError('education_type', "Ta'lim turini tanlang")
            return
        }
        currentStep.value++
        return
    }

    if (currentStep.value === 2) {
        let ok = true
        if (!form.direction_id) { form.setError('direction_id', "Yo'nalishni tanlang"); ok = false }
        if (!form.study_form)   { form.setError('study_form', "Ta'lim shaklini tanlang"); ok = false }
        if (ok) currentStep.value++
        return
    }

    if (currentStep.value === 3) {
        let ok = true
        if (!form.last_name)   { form.setError('last_name', "Familiyangizni kiriting"); ok = false }
        if (!form.first_name)  { form.setError('first_name', "Ismingizni kiriting"); ok = false }
        if (!form.middle_name) { form.setError('middle_name', "Otangizning ismini kiriting"); ok = false }
        if (!form.birth_year)  { form.setError('birth_year', "Yilni kiriting"); ok = false }
        if (!form.birth_month) { form.setError('birth_month', "Oyni tanlang"); ok = false }
        if (!form.birth_day)   { form.setError('birth_day', "Kunni kiriting"); ok = false }
        if (!form.gender)      { form.setError('gender', "Jinsingizni tanlang"); ok = false }
        if (ok) currentStep.value++
        return
    }
}

const submit = () => {
    form.clearErrors()
    let ok = true
    if (!form.passport_series) { form.setError('passport_series', "Pasport seriyasini kiriting"); ok = false }
    if (!form.region_id)       { form.setError('region_id', "Viloyatni tanlang"); ok = false }
    if (!form.district_id)     { form.setError('district_id', "Tumanni tanlang"); ok = false }
    if (!form.phone)           { form.setError('phone', "Telefon raqamingizni kiriting"); ok = false }

    if (form.education_type === 'master') {
        if (!form.passport_file)         { form.setError('passport_file', "Pasport nusxasini yuklang"); ok = false }
        if (!form.diploma_file)          { form.setError('diploma_file', "Diplom nusxasini yuklang"); ok = false }
        if (!form.diploma_appendix_file) { form.setError('diploma_appendix_file', "Diplom ilovasini yuklang"); ok = false }
    }

    if (!ok) {
        toast.error("Iltimos, barcha majburiy maydonlarni to'ldiring!")
        return
    }

    form.post(route('qabul.ariza.store'), {
        forceFormData: true,
        onSuccess: () => toast.success("Ariza muvaffaqiyatli yuborildi!"),
        onError: () => toast.error("Xatolik yuz berdi. Qayta urinib ko'ring!"),
    })
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
    transition: border-color 0.2s, background 0.2s;
    appearance: auto;
}
.field-input:focus { border-color: #0f3460; background: white; }
.field-input:disabled { opacity: 0.5; cursor: not-allowed; }
.field-error { border-color: #f87171 !important; background: #fef2f2 !important; }

.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }

.file-upload-area {
    width: 100%;
    padding: 1.25rem;
    border-radius: 0.75rem;
    border: 2px dashed #e5e7eb;
    background: #fafafa;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}
.file-upload-area:hover {
    border-color: #0f3460;
    background: #eff6ff;
}
.file-error { border-color: #f87171 !important; background: #fef2f2 !important; }

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
.btn-primary:hover { box-shadow: 0 6px 20px rgba(15,52,96,0.3); transform: translateY(-1px); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

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
}
.btn-secondary:hover { background: #f9fafb; }
</style>
