<template>
    <div class="rte-wrap" :class="{ 'rte-error': error, 'rte-disabled': disabled }">
        <Editor
            v-model="localValue"
            :disabled="disabled"
            :tinymce-script-src="tinymceScriptSrc"
            license-key="gpl"
            :init="editorInit"
        />
        <div class="rte-status">
            <span>SO'ZLAR: {{ wordCount }}</span>
            <span>BELGILAR: {{ charCount }}</span>
        </div>
    </div>
</template>

<script setup>
/**
 * Butun tizim bo'ylab barcha <textarea> o'rnini bosuvchi yagona rich-text
 * (WYSIWYG) muharrir komponenti — TinyMCE'ning o'z serverimizga joylashgan
 * (self-hosted, bepul/GPL) nusxasi asosida. Hech qanday tashqi CDN yoki
 * bulutli API kalitiga muhtoj emas: barcha fayllar `public/vendor/tinymce`
 * papkasidan (npm install → postinstall skripti orqali avtomatik) beriladi.
 *
 * Ishlatilishi oddiy <textarea> bilan bir xil, faqat v-model orqali:
 *   <RichTextEditor v-model="form.description_uz" :error="!!form.errors.description_uz" />
 *
 * Saqlanadigan qiymat — HTML matn (oddiy matn emas). Buni ko'rsatish kerak
 * bo'lgan joylarda (masalan talaba tomonidan ko'riladigan dars matni)
 * `v-html` bilan chiqarish kerak — bu andoza allaqachon Lesson.vue'da
 * qo'llanilgan.
 */
import { computed } from 'vue'
import Editor from '@tinymce/tinymce-vue'

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    height: { type: [Number, String], default: 320 },
    error: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const localValue = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
})

// public/vendor/tinymce/tinymce.min.js — scripts/copy-tinymce.js orqali
// node_modules/tinymce'dan avtomatik nusxalanadi (npm install/postinstall).
const tinymceScriptSrc = '/vendor/tinymce/tinymce.min.js'

const stripHtml = (html) => (html || '').replace(/<[^>]*>/g, ' ').replace(/&nbsp;/g, ' ')

const wordCount = computed(() => {
    const text = stripHtml(props.modelValue).trim()
    return text ? text.split(/\s+/).filter(Boolean).length : 0
})

const charCount = computed(() => stripHtml(props.modelValue).replace(/\s+/g, '').length)

// MUHIM: litsenziya kaliti shu yerda (init ichida) emas, balki <Editor>
// komponentiga to'g'ridan-to'g'ri "license-key" prop sifatida beriladi
// (yuqoridagi shablonga qarang) — @tinymce/tinymce-vue paketi finalInit'ni
// yasaganda init ichidagi `license_key`ni har doim shu prop bilan qayta
// yozib qo'yadi, shuning uchun uni faqat shu yerga (init'ga) qo'yish
// "TinyMCE litsenziya kaliti berilmagani uchun muharrir o'chirilgan" degan
// xatoga olib kelgan edi.
const editorInit = {
    // MUHIM: "height" (umumiy balandlik) o'rniga "min_height" + "autoresize"
    // plagini ishlatiladi. To'liq toolbar 4 qatordan iborat (~150-200px) —
    // agar "height" umumiy (toolbar + tahrirlash maydoni) sifatida
    // ishlatilganda, kichik qiymatlarda (masalan 160) tahrirlash maydoniga
    // deyarli joy qolmay, u ko'rinmas bo'lib qolgan edi. "min_height" esa
    // FAQAT tahrirlanadigan maydonning eng kichik balandligi — toolbar necha
    // qatorli bo'lishidan qat'iy nazar, matn yozish uchun joy har doim
    // kafolatlanadi (kontent ko'payib ketsa, avtomatik pastga o'sadi).
    min_height: Number(props.height) || 200,
    autoresize_bottom_margin: 16,
    menubar: false,
    statusbar: false,
    branding: false,
    promotion: false,
    placeholder: props.placeholder,
    skin: 'oxide',
    content_css: 'default',
    content_style: "body { font-family: inherit; font-size: 14px; color: #111827; }",
    plugins: 'advlist autolink autoresize lists link image charmap preview anchor searchreplace ' +
        'visualblocks code fullscreen insertdatetime media table help wordcount ' +
        'emoticons directionality pagebreak nonbreaking codesample accordion',
    toolbar: [
        'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough removeformat',
        'forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
        'link image media table charmap emoticons | blockquote hr pagebreak anchor | subscript superscript ltr rtl',
        'searchreplace code fullscreen preview rtePrint | help',
    ],
    setup: (editor) => {
        // TinyMCE 6+ da "print" plugini brauzer cheklovlari sababli olib
        // tashlangan — o'rniga tarkibni yangi oynada ochib, brauzerning o'z
        // chop etish oynasini chaqiruvchi tugma qo'shildi.
        editor.ui.registry.addButton('rtePrint', {
            icon: 'print',
            tooltip: 'Chop etish',
            onAction: () => {
                const win = window.open('', '_blank')
                if (! win) return
                win.document.write(
                    '<html><head><title>Chop etish</title></head><body>' +
                    editor.getContent() +
                    '</body></html>'
                )
                win.document.close()
                win.focus()
                win.print()
            },
        })
    },
}
</script>

<style scoped>
.rte-wrap {
    border-radius: 0.625rem;
    border: 1.5px solid #e5e7eb;
    overflow: hidden;
    background: white;
    transition: border-color 0.2s;
}
.rte-wrap:focus-within { border-color: var(--color-brand-600); }
.rte-error { border-color: #f87171 !important; }
.rte-disabled { opacity: 0.6; pointer-events: none; }

.rte-status {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding: 0.4rem 0.75rem;
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    color: #9ca3af;
    background: #fafafa;
    border-top: 1px solid #f3f4f6;
}

:deep(.tox-tinymce) {
    border: none !important;
    border-radius: 0 !important;
}
:deep(.tox .tox-toolbar-overlord),
:deep(.tox .tox-toolbar__primary),
:deep(.tox .tox-toolbar__overflow) {
    background: #fafafa !important;
}
:deep(.tox .tox-edit-area__iframe) {
    background: white !important;
}
</style>
