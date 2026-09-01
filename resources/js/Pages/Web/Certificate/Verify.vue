<template>
    <Head title="Sertifikatni tekshirish" />
    <div class="page">
        <div class="card">
            <div class="brand">
                <Icon icon="mdi:school-outline" class="w-6 h-6" />
                <span>Yangi Asr Universiteti</span>
            </div>

            <template v-if="found">
                <div class="status ok">
                    <Icon icon="mdi:check-decagram" class="w-9 h-9" />
                    <p>Sertifikat haqiqiy</p>
                </div>

                <div class="row">
                    <span class="label">Talaba</span>
                    <span class="value">{{ certificate.student_name }}</span>
                </div>
                <div class="row">
                    <span class="label">Kurs</span>
                    <span class="value">{{ certificate.course_title }}</span>
                </div>
                <div class="row">
                    <span class="label">Yakuniy ball</span>
                    <span class="value">{{ certificate.final_score }}</span>
                </div>
                <div class="row">
                    <span class="label">Berilgan sana</span>
                    <span class="value">{{ certificate.issued_at }}</span>
                </div>
                <div class="row">
                    <span class="label">Sertifikat raqami</span>
                    <span class="value mono">{{ certificate.certificate_number }}</span>
                </div>
            </template>

            <template v-else>
                <div class="status bad">
                    <Icon icon="mdi:close-circle-outline" class="w-9 h-9" />
                    <p>Bunday sertifikat topilmadi</p>
                </div>
                <p class="hint">Kiritilgan raqam noto'g'ri yoki bunday sertifikat mavjud emas. Iltimos, QR kod yoki sertifikatdagi raqamni qaytadan tekshiring.</p>
            </template>
        </div>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

defineProps({
    found: { type: Boolean, required: true },
    certificate: { type: Object, default: null },
})
</script>

<style scoped>
.page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #0f3460 0%, #533483 100%);
    padding: 1.5rem;
    font-family: -apple-system, "Segoe UI", Arial, sans-serif;
}
.card {
    width: 100%;
    max-width: 440px;
    background: white;
    border-radius: 1.25rem;
    padding: 2rem;
    box-shadow: 0 20px 50px rgba(0,0,0,0.25);
}
.brand {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    font-weight: 700;
    color: #0f3460;
    margin-bottom: 1.5rem;
}
.status {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1.25rem 0 1.5rem;
    text-align: center;
}
.status.ok { color: #16a34a; }
.status.bad { color: #dc2626; }
.status p { font-size: 1rem; font-weight: 700; color: #111827; }
.row {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.6rem 0;
    border-bottom: 1px solid #f3f4f6;
    font-size: 0.82rem;
}
.row:last-child { border-bottom: none; }
.label { color: #6b7280; }
.value { font-weight: 600; color: #111827; text-align: right; }
.value.mono { font-family: monospace; }
.hint { font-size: 0.78rem; color: #9ca3af; text-align: center; margin-top: 0.5rem; }
</style>
