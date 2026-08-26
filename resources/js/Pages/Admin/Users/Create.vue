<template>
    <AppLayout :title="isEdit ? 'Foydalanuvchini tahrirlash' : 'Yangi foydalanuvchi'">
        <div class="max-w-xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.users.index')"
                    class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition"
                >
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <h1 class="text-xl font-bold text-gray-900">
                    {{ isEdit ? 'Foydalanuvchini tahrirlash' : 'Yangi foydalanuvchi' }}
                </h1>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <div class="space-y-5">

                    <!-- To'liq ism -->
                    <div>
                        <label class="field-label">To'liq ism <span class="req">*</span></label>
                        <div class="relative">
                            <Icon icon="mdi:account-outline"
                                  class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input
                                v-model="form.full_name"
                                type="text"
                                placeholder="Ism Familiya"
                                class="field-input pl-10"
                                :class="form.errors.full_name ? 'field-error' : ''"
                            >
                        </div>
                        <p v-if="form.errors.full_name" class="err">{{ form.errors.full_name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="field-label">Email <span class="req">*</span></label>
                        <div class="relative">
                            <Icon icon="mdi:email-outline"
                                  class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="email@yaumd.uz"
                                class="field-input pl-10"
                                :class="form.errors.email ? 'field-error' : ''"
                            >
                        </div>
                        <p v-if="form.errors.email" class="err">{{ form.errors.email }}</p>
                    </div>

                    <!-- Parol -->
                    <div>
                        <label class="field-label">
                            Parol
                            <span class="req">*</span>
                            <span v-if="isEdit" class="text-gray-400 font-normal ml-1">(o'zgartirmasak bo'sh qoldiring)</span>
                        </label>
                        <div class="relative">
                            <Icon icon="mdi:lock-outline"
                                  class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Kamida 8 ta belgi"
                                class="field-input pl-10 pr-10"
                                :class="form.errors.password ? 'field-error' : ''"
                            >
                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <Icon :icon="showPassword ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" class="w-4 h-4" />
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="err">{{ form.errors.password }}</p>
                    </div>

                    <!-- Parol tasdiqlash -->
                    <div>
                        <label class="field-label">Parolni tasdiqlang <span v-if="!isEdit" class="req">*</span></label>
                        <div class="relative">
                            <Icon icon="mdi:lock-check-outline"
                                  class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input
                                v-model="form.password_confirmation"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Parolni qayta kiriting"
                                class="field-input pl-10"
                                :class="form.errors.password_confirmation ? 'field-error' : ''"
                            >
                        </div>
                        <p v-if="form.errors.password_confirmation" class="err">{{ form.errors.password_confirmation }}</p>
                    </div>

                    <!-- Rol -->
                    <div>
                        <label class="field-label">Rol <span class="req">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                v-for="role in roles"
                                :key="role.name"
                                type="button"
                                @click="form.role = role.name"
                                class="flex items-center gap-2 px-3 py-2.5 rounded-xl border-2 transition-all text-left"
                                :style="form.role === role.name
                                    ? 'border-color:#0f3460; background:linear-gradient(135deg,#eff6ff,#f5f3ff)'
                                    : 'border-color:#e5e7eb; background:#fafafa'"
                            >
                                <Icon :icon="roleIcon(role.name)" class="w-4 h-4 flex-shrink-0"
                                      :style="form.role === role.name ? 'color:#0f3460' : 'color:#9ca3af'" />
                                <span class="text-sm font-medium"
                                      :style="form.role === role.name ? 'color:#0f3460' : 'color:#374151'">
                                    {{ roleLabel(role.name) }}
                                </span>
                                <Icon v-if="form.role === role.name" icon="mdi:check" class="w-3.5 h-3.5 ml-auto"
                                      style="color:#0f3460" />
                            </button>
                        </div>
                        <p v-if="form.errors.role" class="err">{{ form.errors.role }}</p>
                    </div>

                </div>

                <!-- Tugmalar -->
                <div class="flex gap-3 mt-6">
                    <Link
                        :href="route('admin.users.index')"
                        class="btn-secondary flex-1 flex items-center justify-center gap-2"
                    >
                        <Icon icon="mdi:close" class="w-4 h-4" />
                        Bekor qilish
                    </Link>
                    <button
                        type="button"
                        @click="submit"
                        :disabled="form.processing"
                        class="btn-primary flex-1"
                    >
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
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    user:  { type: Object, default: null },
    roles: { type: Array,  default: () => [] },
})

const isEdit      = computed(() => !!props.user)
const showPassword = ref(false)

const form = useForm({
    full_name:            props.user?.full_name            || '',
    email:                props.user?.email                || '',
    password:             '',
    password_confirmation:'',
    role:                 props.user?.roles?.[0]?.name     || '',
})

const roleLabels = [
    { value: 'super-admin', label: 'Super Admin',  icon: 'mdi:shield-crown-outline' },
    { value: 'admin',       label: 'Admin',        icon: 'mdi:shield-account-outline' },
    { value: 'admission',   label: 'Qabul',        icon: 'mdi:clipboard-account-outline' },
    { value: 'teacher',     label: "O'qituvchi",   icon: 'mdi:school-outline' },
    { value: 'finance',     label: 'Moliya',       icon: 'mdi:cash-multiple' },
    { value: 'librarian',   label: 'Kutubxonachi', icon: 'mdi:bookshelf' },
    { value: 'student',     label: 'Talaba',       icon: 'mdi:account-school-outline' },
]

const roleLabel = (name) => roleLabels.find(r => r.value === name)?.label || name
const roleIcon  = (name) => roleLabels.find(r => r.value === name)?.icon  || 'mdi:account-outline'

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.users.update', props.user.id))
    } else {
        form.post(route('admin.users.store'))
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
}
.field-input.pl-10 { padding-left: 2.5rem; }
.field-input.pr-10 { padding-right: 2.5rem; }
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
