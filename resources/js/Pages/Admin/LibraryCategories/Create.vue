<template>
    <AppLayout title="Yangi kategoriya">
        <div class="max-w-xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="route('admin.library-categories.index')"
                      class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <h1 class="text-xl font-bold text-gray-900">Yangi kategoriya</h1>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="space-y-5">

                    <div>
                        <label class="field-label">Nomi (O'zbek) <span class="req">*</span></label>
                        <input v-model="form.name_uz" type="text" placeholder="Masalan: Badiiy adabiyot"
                               class="field-input" :class="form.errors.name_uz ? 'field-error' : ''">
                        <p v-if="form.errors.name_uz" class="err">{{ form.errors.name_uz }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">Nomi (Rus)</label>
                            <input v-model="form.name_ru" type="text" placeholder="Ixtiyoriy" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Nomi (Ingliz)</label>
                            <input v-model="form.name_en" type="text" placeholder="Ixtiyoriy" class="field-input">
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 border border-gray-100">
                        <div>
                            <p class="text-sm font-semibold text-gray-700">Kategoriya faol</p>
                            <p class="text-xs text-gray-400 mt-0.5">Nofaol kategoriyalar kitob qo'shishda ko'rinmaydi</p>
                        </div>
                        <button type="button" @click="form.is_active = !form.is_active"
                                class="relative w-11 h-6 rounded-full transition-all duration-300"
                                :style="form.is_active ? 'background:linear-gradient(135deg,#0f3460,#533483)' : 'background:#e5e7eb'">
                            <span class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-all duration-300"
                                  :class="form.is_active ? 'left-6' : 'left-1'"></span>
                        </button>
                    </div>

                </div>

                <div class="flex gap-3 mt-6">
                    <Link :href="route('admin.library-categories.index')"
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
import { Link, useForm } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const form = useForm({
    name_uz:   '',
    name_ru:   '',
    name_en:   '',
    is_active: true,
})

const submit = () => {
    form.post(route('admin.library-categories.store'))
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
