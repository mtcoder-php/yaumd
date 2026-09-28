<template>
    <Teleport to="body">
        <Transition name="confirm-fade">
            <div
                v-if="state.visible"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                style="background: rgba(0,0,0,0.5)"
                @click.self="decline"
                @keydown.esc="decline"
            >
                <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                    <div
                        class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4"
                        :class="state.danger ? 'bg-red-100' : 'bg-brand-100'"
                    >
                        <Icon
                            :icon="state.danger ? 'mdi:alert-outline' : 'mdi:help-circle-outline'"
                            class="w-6 h-6"
                            :class="state.danger ? 'text-red-500' : 'text-brand-600'"
                        />
                    </div>
                    <h3 class="text-base font-bold text-gray-900 text-center mb-2">{{ state.title }}</h3>
                    <p class="text-sm text-gray-500 text-center mb-6 whitespace-pre-line">{{ state.message }}</p>
                    <div class="flex gap-3">
                        <button @click="decline" class="btn-neutral flex-1 justify-center">
                            {{ state.cancelText }}
                        </button>
                        <button
                            @click="accept"
                            class="flex-1 justify-center"
                            :class="state.danger ? 'btn-danger-pill' : 'btn-brand'"
                        >
                            {{ state.confirmText }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { Icon } from '@iconify/vue'
import { useConfirmState, confirmAccept, confirmDecline } from '@/Composables/useConfirm'

// Loyihadagi BARCHA sahifalar uchun bitta umumiy tasdiqlash modali —
// bu komponent AppLayout.vue'da bir marta joylashtiriladi, sahifalarning
// o'zi esa faqat `confirmDialog()` composable'ini chaqiradi (useConfirm.js'ga
// qarang), o'zi alohida modal HTML yozmaydi.
const state = useConfirmState()

const accept = () => confirmAccept()
const decline = () => confirmDecline()
</script>

<style scoped>
.confirm-fade-enter-active,
.confirm-fade-leave-active {
    transition: opacity 0.15s ease;
}
.confirm-fade-enter-from,
.confirm-fade-leave-to {
    opacity: 0;
}
</style>
