<template>
    <AppLayout title="Mening profilim">
        <div class="max-w-xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.dashboard')"
                    class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition"
                >
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <h1 class="text-xl font-bold text-gray-900">Mening profilim</h1>
            </div>

            <!-- Avatar + Asosiy ma'lumotlar -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <!-- Avatar -->
                <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-100">
                    <div class="relative">
                        <div v-if="photoPreview || user.photo_url"
                             class="w-20 h-20 rounded-full bg-cover bg-center border border-gray-200"
                             :style="`background-image:url('${photoPreview || user.photo_url}')`">
                        </div>
                        <div v-else
                             class="w-20 h-20 rounded-full flex items-center justify-center text-white text-xl font-semibold"
                             style="background:linear-gradient(135deg,#0f3460,#533483)">
                            {{ initials }}
                        </div>
                        <button
                            type="button"
                            @click="$refs.photoInput.click()"
                            class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition"
                            title="Rasmni almashtirish"
                        >
                            <Icon icon="mdi:camera-outline" class="w-3.5 h-3.5 text-gray-600" />
                        </button>
                        <input
                            ref="photoInput"
                            type="file"
                            accept="image/png,image/jpeg,image/webp"
                            class="hidden"
                            @change="onPhotoSelected"
                        >
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-800">{{ user.full_name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ user.email }}</p>
                        <div class="flex gap-3 mt-2">
                            <button
                                type="button"
                                @click="$refs.photoInput.click()"
                                :disabled="photoForm.processing"
                                class="text-xs font-medium hover:underline"
                                style="color:#0f3460"
                            >
                                Rasm yuklash
                            </button>
                            <button
                                v-if="user.photo_url"
                                type="button"
                                @click="removePhoto"
                                class="text-xs font-medium text-red-500 hover:underline"
                            >
                                O'chirish
                            </button>
                        </div>
                        <p v-if="photoForm.errors.photo" class="err mt-1">{{ photoForm.errors.photo }}</p>
                    </div>
                </div>

                <div class="space-y-5">

                    <!-- To'liq ism -->
                    <div>
                        <label class="field-label">To'liq ism <span class="req">*</span></label>
                        <div class="relative">
                            <Icon icon="mdi:account-outline"
                                  class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input
                                v-model="infoForm.full_name"
                                type="text"
                                name="full_name"
                                autocomplete="name"
                                placeholder="Ism Familiya"
                                class="field-input pl-10"
                                :class="infoForm.errors.full_name ? 'field-error' : ''"
                            >
                        </div>
                        <p v-if="infoForm.errors.full_name" class="err">{{ infoForm.errors.full_name }}</p>
                    </div>

                    <!-- Telefon -->
                    <div>
                        <label class="field-label">Telefon</label>
                        <div class="relative">
                            <Icon icon="mdi:phone-outline"
                                  class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input
                                v-model="infoForm.phone"
                                type="text"
                                name="phone"
                                autocomplete="tel"
                                inputmode="tel"
                                placeholder="998901234567"
                                class="field-input pl-10"
                                :class="infoForm.errors.phone ? 'field-error' : ''"
                            >
                        </div>
                        <p v-if="infoForm.errors.phone" class="err">{{ infoForm.errors.phone }}</p>
                    </div>

                    <!-- Tug'ilgan sana + Jins -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="field-label">Tug'ilgan sana</label>
                            <input
                                v-model="infoForm.birth_date"
                                type="date"
                                name="birth_date"
                                autocomplete="bday"
                                class="field-input"
                                :class="infoForm.errors.birth_date ? 'field-error' : ''"
                            >
                            <p v-if="infoForm.errors.birth_date" class="err">{{ infoForm.errors.birth_date }}</p>
                        </div>
                        <div>
                            <label class="field-label">Jins</label>
                            <select v-model="infoForm.gender" name="gender" autocomplete="sex" class="field-input">
                                <option value="">Tanlanmagan</option>
                                <option value="male">Erkak</option>
                                <option value="female">Ayol</option>
                            </select>
                        </div>
                    </div>

                    <!-- Manzil -->
                    <div>
                        <label class="field-label">Manzil</label>
                        <textarea
                            v-model="infoForm.address"
                            rows="2"
                            name="address"
                            autocomplete="street-address"
                            placeholder="Yashash manzili"
                            class="field-input"
                            :class="infoForm.errors.address ? 'field-error' : ''"
                        ></textarea>
                        <p v-if="infoForm.errors.address" class="err">{{ infoForm.errors.address }}</p>
                    </div>

                </div>

                <div class="flex justify-end mt-6">
                    <button
                        type="button"
                        @click="submitInfo"
                        :disabled="infoForm.processing"
                        class="btn-primary"
                    >
                        <Icon v-if="infoForm.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                        <Icon v-else icon="mdi:content-save-outline" class="w-4 h-4" />
                        {{ infoForm.processing ? 'Saqlanmoqda...' : 'Saqlash' }}
                    </button>
                </div>
            </div>

            <!-- Email -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <h2 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <Icon icon="mdi:email-outline" class="w-4 h-4" style="color:#0f3460" />
                    Email manzil
                </h2>

                <!-- Kutilayotgan tasdiqlash bo'lmasa: joriy email + o'zgartirish tugmasi -->
                <template v-if="!pending && !showEmailForm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-700">{{ user.email }}</p>
                        <button type="button" @click="showEmailForm = true"
                                class="text-xs font-medium hover:underline" style="color:#0f3460">
                            O'zgartirish
                        </button>
                    </div>
                </template>

                <!-- Yangi email so'rash formasi -->
                <template v-else-if="!pending && showEmailForm">
                    <div class="space-y-4">
                        <div>
                            <label class="field-label">Yangi email <span class="req">*</span></label>
                            <input
                                v-model="emailRequestForm.new_email"
                                type="email"
                                name="new_email"
                                autocomplete="email"
                                placeholder="yangi@email.com"
                                class="field-input"
                                :class="emailRequestForm.errors.new_email ? 'field-error' : ''"
                            >
                            <p v-if="emailRequestForm.errors.new_email" class="err">{{ emailRequestForm.errors.new_email }}</p>
                        </div>
                        <div>
                            <label class="field-label">Joriy parol <span class="req">*</span></label>
                            <input
                                v-model="emailRequestForm.current_password"
                                type="password"
                                name="current_password"
                                autocomplete="current-password"
                                placeholder="Tasdiqlash uchun joriy parolingiz"
                                class="field-input"
                                :class="emailRequestForm.errors.current_password ? 'field-error' : ''"
                            >
                            <p v-if="emailRequestForm.errors.current_password" class="err">{{ emailRequestForm.errors.current_password }}</p>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="showEmailForm = false" class="btn-secondary flex-1">
                                Bekor qilish
                            </button>
                            <button type="button" @click="submitEmailRequest" :disabled="emailRequestForm.processing" class="btn-primary flex-1">
                                <Icon v-if="emailRequestForm.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                                {{ emailRequestForm.processing ? 'Yuborilmoqda...' : 'Tasdiqlash kodini yuborish' }}
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Kod tasdiqlash -->
                <template v-else>
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600">
                            <strong>{{ pending.email }}</strong> manziliga tasdiqlash kodi yuborildi.
                            Kod 10 daqiqa amal qiladi.
                        </p>
                        <div>
                            <label class="field-label">Tasdiqlash kodi <span class="req">*</span></label>
                            <input
                                v-model="emailVerifyForm.code"
                                type="text"
                                name="code"
                                autocomplete="one-time-code"
                                inputmode="numeric"
                                maxlength="6"
                                placeholder="123456"
                                class="field-input tracking-widest text-center"
                                :class="emailVerifyForm.errors.code ? 'field-error' : ''"
                            >
                            <p v-if="emailVerifyForm.errors.code" class="err">{{ emailVerifyForm.errors.code }}</p>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="cancelEmailChange" class="btn-secondary flex-1">
                                Bekor qilish
                            </button>
                            <button type="button" @click="submitEmailVerify" :disabled="emailVerifyForm.processing" class="btn-primary flex-1">
                                <Icon v-if="emailVerifyForm.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                                {{ emailVerifyForm.processing ? 'Tekshirilmoqda...' : 'Tasdiqlash' }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Parolni o'zgartirish -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <h2 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <Icon icon="mdi:lock-outline" class="w-4 h-4" style="color:#0f3460" />
                    Parolni o'zgartirish
                </h2>

                <div class="space-y-4">
                    <div>
                        <label class="field-label">Joriy parol <span class="req">*</span></label>
                        <input
                            v-model="passwordForm.current_password"
                            type="password"
                            name="current_password"
                            autocomplete="current-password"
                            placeholder="Joriy parolingiz"
                            class="field-input"
                            :class="passwordForm.errors.current_password ? 'field-error' : ''"
                        >
                        <p v-if="passwordForm.errors.current_password" class="err">{{ passwordForm.errors.current_password }}</p>
                    </div>
                    <div>
                        <label class="field-label">Yangi parol <span class="req">*</span></label>
                        <input
                            v-model="passwordForm.password"
                            type="password"
                            name="password"
                            autocomplete="new-password"
                            placeholder="Kamida 8 ta belgi"
                            class="field-input"
                            :class="passwordForm.errors.password ? 'field-error' : ''"
                        >
                        <p v-if="passwordForm.errors.password" class="err">{{ passwordForm.errors.password }}</p>
                    </div>
                    <div>
                        <label class="field-label">Yangi parolni tasdiqlang <span class="req">*</span></label>
                        <input
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            name="password_confirmation"
                            autocomplete="new-password"
                            placeholder="Yangi parolni qayta kiriting"
                            class="field-input"
                            :class="passwordForm.errors.password_confirmation ? 'field-error' : ''"
                        >
                        <p v-if="passwordForm.errors.password_confirmation" class="err">{{ passwordForm.errors.password_confirmation }}</p>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button
                        type="button"
                        @click="submitPassword"
                        :disabled="passwordForm.processing"
                        class="btn-primary"
                    >
                        <Icon v-if="passwordForm.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                        <Icon v-else icon="mdi:key-change" class="w-4 h-4" />
                        {{ passwordForm.processing ? 'Yangilanmoqda...' : 'Parolni yangilash' }}
                    </button>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    user:                { type: Object, required: true },
    pendingEmailChange:  { type: Object, default: null },
})

const pending = computed(() => props.pendingEmailChange)
const showEmailForm = ref(false)

const initials = computed(() => {
    const name = props.user.full_name || ''
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()
})

// --- Asosiy ma'lumotlar ---
const infoForm = useForm({
    full_name:  props.user.full_name  || '',
    phone:      props.user.phone      || '',
    address:    props.user.address    || '',
    birth_date: props.user.birth_date || '',
    gender:     props.user.gender     || '',
})

const submitInfo = () => {
    infoForm.put(route('admin.profile.update'), { preserveScroll: true })
}

// --- Profil rasmi ---
const photoPreview = ref(null)
const photoForm = useForm({ photo: null })

const onPhotoSelected = (e) => {
    const file = e.target.files[0]
    if (!file) return

    photoForm.photo = file
    photoPreview.value = URL.createObjectURL(file)

    photoForm.post(route('admin.profile.photo.update'), {
        preserveScroll: true,
        onSuccess: () => { photoForm.reset() },
        onError:   () => { photoPreview.value = null },
    })
}

const removePhoto = () => {
    router.delete(route('admin.profile.photo.destroy'), { preserveScroll: true })
}

// --- Parol ---
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const submitPassword = () => {
    passwordForm.put(route('admin.profile.password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    })
}

// --- Email o'zgartirish ---
const emailRequestForm = useForm({ new_email: '', current_password: '' })

const submitEmailRequest = () => {
    emailRequestForm.post(route('admin.profile.email.request'), {
        preserveScroll: true,
        onSuccess: () => {
            emailRequestForm.reset()
            showEmailForm.value = false
        },
    })
}

const emailVerifyForm = useForm({ code: '' })

const submitEmailVerify = () => {
    emailVerifyForm.post(route('admin.profile.email.verify'), {
        preserveScroll: true,
        onSuccess: () => emailVerifyForm.reset(),
    })
}

const cancelEmailChange = () => {
    router.post(route('admin.profile.email.cancel'), {}, { preserveScroll: true })
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
}
.field-input.pl-10 { padding-left: 2.5rem; }
.field-input:focus { border-color: #0f3460; background: white; }
.field-error { border-color: #f87171 !important; background: #fef2f2 !important; }
.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }

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
