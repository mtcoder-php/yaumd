<template>
    <AppLayout>
        <div class="p-6">

            <!-- Header -->
            <div class="flex items-center gap-4 mb-6">
                <Link
                    :href="route('admin.applicants.show', applicant.id)"
                    class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition"
                >
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Arizani tahrirlash</h1>
                    <p class="text-sm text-gray-500 font-mono">{{ applicant.application_number }}</p>
                </div>
            </div>

            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                    <!-- Chap ustun -->
                    <div class="lg:col-span-2 space-y-5">

                        <!-- Ta'lim ma'lumotlari -->
                        <div class="bg-white rounded-2xl border border-gray-100 p-5">
                            <h2 class="section-title">
                                <Icon icon="mdi:school-outline" class="w-4 h-4 text-[#0f3460]" />
                                Ta'lim ma'lumotlari
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                <div>
                                    <label class="field-label">Ta'lim turi <span class="req">*</span></label>
                                    <select v-model="form.education_type" class="field-input" :class="errors.education_type ? 'field-error' : ''">
                                        <option value="bachelor">Bakalavr</option>
                                        <option value="master">Magistr</option>
                                        <option value="transfer">Transfer</option>
                                        <option value="second">2-mutaxassislik</option>
                                    </select>
                                    <p v-if="errors.education_type" class="err">{{ errors.education_type }}</p>
                                </div>

                                <div>
                                    <label class="field-label">Ta'lim shakli <span class="req">*</span></label>
                                    <select v-model="form.study_form" class="field-input" :class="errors.study_form ? 'field-error' : ''">
                                        <option value="full_time">Kunduzgi</option>
                                        <option value="evening" :disabled="form.education_type === 'master'">Kechki</option>
                                        <option value="distance" disabled>Masofaviy (mavjud emas)</option>
                                    </select>
                                    <p v-if="errors.study_form" class="err">{{ errors.study_form }}</p>
                                </div>

                                <div>
                                    <label class="field-label">Fakultet</label>
                                    <select v-model="selectedFacultyId" class="field-input" @change="form.direction_id = ''">
                                        <option value="">Tanlang</option>
                                        <option v-for="f in faculties" :key="f.id" :value="f.id">{{ f.name_uz }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="field-label">Yo'nalish <span class="req">*</span></label>
                                    <select v-model="form.direction_id" class="field-input" :class="errors.direction_id ? 'field-error' : ''">
                                        <option value="">Tanlang</option>
                                        <option v-for="d in filteredDirections" :key="d.id" :value="d.id">
                                            {{ d.name_uz }}
                                        </option>
                                    </select>
                                    <p v-if="errors.direction_id" class="err">{{ errors.direction_id }}</p>
                                </div>

                            </div>
                        </div>

                        <!-- Shaxsiy ma'lumotlar -->
                        <div class="bg-white rounded-2xl border border-gray-100 p-5">
                            <h2 class="section-title">
                                <Icon icon="mdi:account-outline" class="w-4 h-4 text-[#0f3460]" />
                                Shaxsiy ma'lumotlar
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                <!-- Familiya = last_name -->
                                <div>
                                    <label class="field-label">Familiya <span class="req">*</span></label>
                                    <input v-model="form.last_name" type="text" class="field-input"
                                           :class="errors.last_name ? 'field-error' : ''"
                                           @input="form.last_name = form.last_name.toUpperCase()">
                                    <p v-if="errors.last_name" class="err">{{ errors.last_name }}</p>
                                </div>

                                <!-- Ism = first_name -->
                                <div>
                                    <label class="field-label">Ism <span class="req">*</span></label>
                                    <input v-model="form.first_name" type="text" class="field-input"
                                           :class="errors.first_name ? 'field-error' : ''"
                                           @input="form.first_name = form.first_name.toUpperCase()">
                                    <p v-if="errors.first_name" class="err">{{ errors.first_name }}</p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="field-label">Otasining ismi <span class="req">*</span></label>
                                    <input v-model="form.middle_name" type="text" class="field-input"
                                           :class="errors.middle_name ? 'field-error' : ''"
                                           @input="form.middle_name = form.middle_name.toUpperCase()">
                                    <p v-if="errors.middle_name" class="err">{{ errors.middle_name }}</p>
                                </div>

                                <!-- Tug'ilgan sana: kun/oy/yil -->
                                <div class="sm:col-span-2">
                                    <label class="field-label">Tug'ilgan sana <span class="req">*</span></label>
                                    <div class="grid grid-cols-3 gap-3">
                                        <div>
                                            <input v-model="form.birth_day" type="number" placeholder="Kun"
                                                   min="1" max="31" class="field-input"
                                                   :class="errors.birth_day ? 'field-error' : ''">
                                        </div>
                                        <div>
                                            <select v-model="form.birth_month" class="field-input"
                                                    :class="errors.birth_month ? 'field-error' : ''">
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
                                    <p v-if="errors.birth_day || errors.birth_month || errors.birth_year" class="err">
                                        Tug'ilgan sanani to'liq kiriting
                                    </p>
                                </div>

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

                        <!-- Hujjat va aloqa -->
                        <div class="bg-white rounded-2xl border border-gray-100 p-5">
                            <h2 class="section-title">
                                <Icon icon="mdi:card-account-details-outline" class="w-4 h-4 text-[#0f3460]" />
                                Hujjat va aloqa
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                <div>
                                    <label class="field-label">Pasport seriyasi <span class="req">*</span></label>
                                    <input v-model="form.passport_series" type="text" maxlength="9"
                                           class="field-input font-mono" :class="errors.passport_series ? 'field-error' : ''"
                                           @input="form.passport_series = form.passport_series.toUpperCase()">
                                    <p v-if="errors.passport_series" class="err">{{ errors.passport_series }}</p>
                                </div>

                                <div>
                                    <label class="field-label">JShShIR</label>
                                    <input v-model="form.jshshir" type="text" maxlength="14"
                                           class="field-input font-mono"
                                           @input="form.jshshir = form.jshshir.replace(/\D/g, '')">
                                </div>

                                <div>
                                    <label class="field-label">Telefon <span class="req">*</span></label>
                                    <input v-model="form.phone" type="tel" class="field-input"
                                           :class="errors.phone ? 'field-error' : ''">
                                    <p v-if="errors.phone" class="err">{{ errors.phone }}</p>
                                </div>

                                <div>
                                    <label class="field-label">Qo'shimcha raqam</label>
                                    <input v-model="form.extra_phone" type="tel" class="field-input">
                                </div>

                                <div>
                                    <label class="field-label">Viloyat <span class="req">*</span></label>
                                    <select v-model="form.region_id" class="field-input"
                                            :class="errors.region_id ? 'field-error' : ''"
                                            @change="form.district_id = ''">
                                        <option value="">Tanlang</option>
                                        <option v-for="r in regions" :key="r.id" :value="r.id">{{ r.name_uz }}</option>
                                    </select>
                                    <p v-if="errors.region_id" class="err">{{ errors.region_id }}</p>
                                </div>

                                <div>
                                    <label class="field-label">Tuman <span class="req">*</span></label>
                                    <select v-model="form.district_id" class="field-input"
                                            :class="errors.district_id ? 'field-error' : ''"
                                            :disabled="!form.region_id">
                                        <option value="">Tanlang</option>
                                        <option v-for="d in selectedDistricts" :key="d.id" :value="d.id">{{ d.name_uz }}</option>
                                    </select>
                                    <p v-if="errors.district_id" class="err">{{ errors.district_id }}</p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="field-label">To'liq manzil</label>
                                    <textarea v-model="form.address" rows="2" class="field-input" style="resize:none"></textarea>
                                </div>

                            </div>
                        </div>

                    </div>

                    <!-- O'ng ustun -->
                    <div class="space-y-5">

                        <!-- Saqlash -->
                        <div class="bg-white rounded-2xl border border-gray-100 p-5">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full btn-primary"
                            >
                                <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                                <Icon v-else icon="mdi:content-save-outline" class="w-4 h-4" />
                                {{ form.processing ? 'Saqlanmoqda...' : 'Saqlash' }}
                            </button>

                            <Link
                                :href="route('admin.applicants.show', applicant.id)"
                                class="w-full mt-3 btn-secondary flex items-center justify-center gap-2"
                            >
                                <Icon icon="mdi:close" class="w-4 h-4" />
                                Bekor qilish
                            </Link>
                        </div>

                        <!-- Ariza ma'lumotlari -->
                        <div class="bg-white rounded-2xl border border-gray-100 p-5">
                            <h2 class="section-title mb-3">
                                <Icon icon="mdi:information-outline" class="w-4 h-4 text-[#0f3460]" />
                                Ariza
                            </h2>
                            <div class="space-y-2.5">
                                <div>
                                    <p class="info-label">Ariza raqami</p>
                                    <p class="text-sm font-mono font-bold text-[#0f3460]">{{ applicant.application_number }}</p>
                                </div>
                                <div>
                                    <p class="info-label">Status</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="statusBadge(applicant.status)">
                                        {{ statusLabel(applicant.status) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="info-label">Topshirilgan sana</p>
                                    <p class="text-sm text-gray-700">{{ formatDate(applicant.created_at) }}</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </form>

        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    applicant: { type: Object, required: true },
    faculties: { type: Array, default: () => [] },
    regions:   { type: Array, default: () => [] },
})

// Hozirgi fakultetni topish
const currentFaculty = props.faculties.find(f =>
    f.directions?.some(d => d.id === props.applicant.direction_id)
)
const selectedFacultyId = ref(currentFaculty?.id || '')

const form = useForm({
    education_type:  props.applicant.education_type  || '',
    direction_id:    props.applicant.direction_id    || '',
    study_form:      props.applicant.study_form      || '',
    first_name:      props.applicant.first_name      || '',
    last_name:       props.applicant.last_name       || '',
    middle_name:     props.applicant.middle_name     || '',
    birth_day:       props.applicant.birth_day       || '',
    birth_month:     props.applicant.birth_month     || '',
    birth_year:      props.applicant.birth_year      || '',
    gender:          props.applicant.gender          || '',
    passport_series: props.applicant.passport_series || '',
    jshshir:         props.applicant.jshshir         || '',
    phone:           props.applicant.phone           || '',
    extra_phone:     props.applicant.extra_phone     || '',
    region_id:       props.applicant.region_id       || '',
    district_id:     props.applicant.district_id     || '',
    address:         props.applicant.address         || '',
})

const errors = computed(() => form.errors)

const filteredDirections = computed(() => {
    const all = props.faculties.flatMap(f => (f.directions || []))
    if (!selectedFacultyId.value) return all
    const faculty = props.faculties.find(f => f.id == selectedFacultyId.value)
    return faculty?.directions || []
})

const selectedDistricts = computed(() => {
    if (!form.region_id) return []
    return props.regions.find(r => r.id == form.region_id)?.districts || []
})

const months = ['Yanvar','Fevral','Mart','Aprel','May','Iyun','Iyul','Avgust','Sentabr','Oktabr','Noyabr','Dekabr']
const genders = [
    { value: 'male',   label: 'Erkak' },
    { value: 'female', label: 'Ayol' },
]

const submit = () => {
    form.put(route('admin.applicants.update', props.applicant.id))
}

const statuses = [
    { value: 'new',        label: 'Yangi',            class: 'bg-blue-50 text-blue-700' },
    { value: 'accepted',   label: 'Qabul qilindi',    class: 'bg-green-50 text-green-700' },
    { value: 'interview',  label: 'Suhbat',           class: 'bg-yellow-50 text-yellow-700' },
    { value: 'tested',     label: 'Test',             class: 'bg-purple-50 text-purple-700' },
    { value: 'contracted', label: 'Kontrakt',         class: 'bg-indigo-50 text-indigo-700' },
    { value: 'enrolled',   label: "Ro'yxatga olindi", class: 'bg-teal-50 text-teal-700' },
    { value: 'rejected',   label: 'Rad etildi',       class: 'bg-red-50 text-red-700' },
]

const statusLabel = (s) => statuses.find(x => x.value === s)?.label || s
const statusBadge = (s) => statuses.find(x => x.value === s)?.class || 'bg-gray-50 text-gray-600'

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('uz-UZ', {
        day: '2-digit', month: '2-digit', year: 'numeric'
    })
}
</script>

<style scoped>
.section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 1rem;
}
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
.field-input:disabled { opacity: 0.5; cursor: not-allowed; }
.field-error { border-color: #f87171 !important; background: #fef2f2 !important; }
.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }
.info-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.25rem;
}
.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
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
    width: 100%;
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
