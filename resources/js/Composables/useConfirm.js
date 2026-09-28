import { reactive } from 'vue'

/**
 * Butun loyiha uchun BITTA (singleton) tasdiqlash modali holati.
 *
 * MUHIM: brauzerning o'ziga xos `window.confirm()`/`alert()` funksiyalari
 * juda eski uslubdagi, dizaynga mos kelmaydigan va mobil qurilmalarda
 * qulay bo'lmagan oyna ko'rsatadi. Shu sababli loyiha bo'ylab BARCHA
 * "tasdiqlaysizmi?" so'rovlari shu composable orqali, bitta umumiy
 * <ConfirmDialog /> komponenti (AppLayout.vue'da bir marta joylashtirilgan)
 * yordamida ko'rsatiladi — natijada barcha sahifalarda BIR XIL ko'rinish
 * bo'ladi va har bir sahifada alohida modal HTML yozish shart emas.
 *
 * Ishlatilishi (avvalgi `if (!confirm('...')) return` o'rniga):
 *
 *   import { confirmDialog } from '@/Composables/useConfirm'
 *
 *   const deleteItem = async () => {
 *       const ok = await confirmDialog({
 *           title: "O'chirish",
 *           message: "Haqiqatan ham o'chirasizmi?",
 *           confirmText: "O'chirish",
 *           danger: true,
 *       })
 *       if (!ok) return
 *       // ... davom etadi
 *   }
 *
 * Eslatma: chaqiruvchi funksiya `async` bo'lishi kerak (yoki `.then()`
 * ishlatilishi kerak), chunki `confirmDialog()` foydalanuvchi "Tasdiqlash"
 * yoki "Bekor qilish" tugmasini bosgunicha kutadigan Promise qaytaradi.
 */
const state = reactive({
    visible: false,
    title: '',
    message: '',
    confirmText: 'Tasdiqlash',
    cancelText: 'Bekor qilish',
    danger: false,
    resolver: null,
})

export function useConfirmState() {
    return state
}

function settle(result) {
    state.visible = false
    const resolve = state.resolver
    state.resolver = null
    resolve?.(result)
}

/**
 * Modalni ochadi va foydalanuvchi javobini (true — tasdiqladi,
 * false — bekor qildi/tashqarisiga bosdi) Promise sifatida qaytaradi.
 *
 * @param {string|{title?: string, message: string, confirmText?: string, cancelText?: string, danger?: boolean}} options
 * @returns {Promise<boolean>}
 */
export function confirmDialog(options) {
    const opts = typeof options === 'string' ? { message: options } : (options || {})

    // Agar boshqa bitta modal allaqachon ochiq bo'lsa (kamdan-kam holat),
    // avvalgisini "bekor qilingan" deb yopib, yangisini ochamiz — ikkita
    // modal bir-birining ustiga chiqib qolmasligi uchun.
    if (state.visible) {
        settle(false)
    }

    return new Promise((resolve) => {
        state.title = opts.title ?? "Tasdiqlaysizmi?"
        state.message = opts.message ?? ''
        state.confirmText = opts.confirmText ?? 'Tasdiqlash'
        state.cancelText = opts.cancelText ?? 'Bekor qilish'
        state.danger = opts.danger ?? false
        state.resolver = resolve
        state.visible = true
    })
}

export function confirmAccept() {
    settle(true)
}

export function confirmDecline() {
    settle(false)
}
