<template>
    <AppLayout title="Muloqot tarixi">
        <div class="max-w-3xl mx-auto space-y-5">

            <div class="flex items-center gap-4">
                <Link :href="route('admin.crm.debtors.index')"
                      class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ person.full_name || '—' }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Muloqot tarixi</p>
                </div>
            </div>

            <!-- Shaxs ma'lumotlari -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 flex flex-wrap gap-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Telefon</p>
                    <p class="text-sm font-semibold text-gray-900">{{ person.phone || '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Email</p>
                    <p class="text-sm font-semibold text-gray-900">{{ person.email || '—' }}</p>
                </div>
            </div>

            <!-- Yangi yozuv qo'shish -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <p class="text-sm font-bold text-gray-900">Yangi yozuv qo'shish</p>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1 block">Turi</label>
                        <select v-model="form.type" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50">
                            <option value="call">Qo'ng'iroq</option>
                            <option value="email">Email</option>
                            <option value="meeting">Uchrashuv</option>
                            <option value="note">Eslatma</option>
                        </select>
                    </div>
                    <div v-if="form.type === 'call' || form.type === 'email'">
                        <label class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1 block">Yo'nalishi</label>
                        <select v-model="form.direction" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50">
                            <option value="outgoing">Chiquvchi (biz qildik)</option>
                            <option value="incoming">Kiruvchi (u qildi)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1 block">Mazmuni</label>
                    <textarea v-model="form.summary" rows="3"
                              placeholder="Masalan: ota-onasi bilan gaplashdim, oy oxirigacha to'lashga va'da berishdi"
                              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50 resize-none"></textarea>
                    <p v-if="form.errors.summary" class="text-xs text-red-600 mt-1">{{ form.errors.summary }}</p>
                </div>

                <button @click="submit" :disabled="form.processing" class="btn-primary">
                    <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                    <span v-else>Saqlash</span>
                </button>
            </div>

            <!-- Tarix -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="p-6 pb-0">
                    <p class="text-sm font-bold text-gray-900">Tarix</p>
                </div>

                <div v-if="!logs.length" class="p-10 text-center text-gray-400">
                    <Icon icon="mdi:phone-off-outline" class="w-8 h-8 mx-auto mb-2 opacity-40" />
                    <p class="text-sm">Hozircha muloqot qayd etilmagan</p>
                </div>

                <div v-else class="divide-y divide-gray-100 mt-4">
                    <div v-for="log in logs" :key="log.id" class="flex items-start gap-3 px-6 py-4">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                             :class="typeClass(log.type)">
                            <Icon :icon="typeIcon(log.type)" class="w-4 h-4" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold text-gray-900">{{ typeLabel(log.type) }}</p>
                                <span v-if="log.direction" class="text-xs text-gray-400">
                                    ({{ log.direction === 'incoming' ? 'kiruvchi' : 'chiquvchi' }})
                                </span>
                                <span class="text-xs text-gray-400">· {{ formatDateTime(log.occurred_at) }}</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1 whitespace-pre-line">{{ log.summary }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ log.creator?.full_name || '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    subjectType: { type: String, required: true },
    subjectId:   { type: [Number, String], required: true },
    person:      { type: Object, default: () => ({}) },
    logs:        { type: Array, default: () => [] },
})

const form = useForm({
    type:        'call',
    direction:   'outgoing',
    summary:     '',
    occurred_at: '',
})

const submit = () => {
    // "Uchrashuv"/"Eslatma" turlarida yo'nalish (kiruvchi/chiquvchi) tanlanmaydi
    // — shu holatda saqlanmasin.
    if (form.type === 'meeting' || form.type === 'note') {
        form.direction = null
    }

    form.post(route('admin.crm.contact.logs.store', [props.subjectType, props.subjectId]), {
        preserveScroll: true,
        onSuccess: () => form.reset('summary'),
    })
}

const typeLabel = (v) => ({ call: "Qo'ng'iroq", email: 'Email', meeting: 'Uchrashuv', note: 'Eslatma' }[v] || v)
const typeIcon = (v) => ({
    call:    'mdi:phone-outline',
    email:   'mdi:email-outline',
    meeting: 'mdi:account-group-outline',
    note:    'mdi:note-text-outline',
}[v] || 'mdi:note-text-outline')
const typeClass = (v) => ({
    call:    'bg-blue-50 text-blue-700',
    email:   'bg-purple-50 text-purple-700',
    meeting: 'bg-green-50 text-green-700',
    note:    'bg-gray-100 text-gray-500',
}[v] || 'bg-gray-100 text-gray-500')

const formatDateTime = (v) => v
    ? new Date(v).toLocaleString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
    : '—'
</script>

<style scoped>
.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
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
</style>
