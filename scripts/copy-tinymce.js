// TinyMCE npm paketi juda ko'p fayldan iborat (skin, plugin, til fayllari) va
// ular Laravel'ning public/ papkasidan to'g'ridan-to'g'ri (Vite build orqali
// emas) xizmat qilinishi kerak, chunki brauzer ularni <script> va CSS
// manzillari orqali runtime'da o'zi so'raydi. Shu sababli bu skript har safar
// `npm install` dan keyin (postinstall) node_modules/tinymce papkasini
// public/vendor/tinymce ga avtomatik nusxalaydi — Windows/macOS/Linux'da bir
// xil ishlaydi (fs.cpSync orqali, alohida shell buyrug'i kerak emas).
import { cpSync, existsSync, rmSync } from 'node:fs'
import { fileURLToPath } from 'node:url'
import { dirname, join } from 'node:path'

const __dirname = dirname(fileURLToPath(import.meta.url))
const src = join(__dirname, '..', 'node_modules', 'tinymce')
const dest = join(__dirname, '..', 'public', 'vendor', 'tinymce')

if (!existsSync(src)) {
    console.warn('[copy-tinymce] node_modules/tinymce topilmadi, o\'tkazib yuborildi.')
    process.exit(0)
}

rmSync(dest, { recursive: true, force: true })
cpSync(src, dest, { recursive: true })
console.log('[copy-tinymce] TinyMCE public/vendor/tinymce ga nusxalandi.')
