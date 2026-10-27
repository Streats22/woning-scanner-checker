<template>
  <div class="ocr" role="group" :aria-labelledby="ocrTitleId">
    <p :id="ocrTitleId" class="ocr__label">{{ t('ocr.title') }}</p>
    <p class="ocr__hint">{{ t('ocr.hint') }}</p>
    <div class="ocr__row">
      <input
        ref="fileRef"
        type="file"
        class="ocr__input"
        accept="image/jpeg,image/png,image/webp,image/gif"
        :disabled="busy"
        @change="onFile"
      >
      <button
        type="button"
        class="ocr__btn"
        :disabled="busy"
        :aria-busy="busy"
        @click="openFilePicker"
      >
        {{ busy ? t('ocr.buttonBusy') : t('ocr.button') }}
      </button>
    </div>
    <p v-if="errorMsg" class="ocr__err" role="alert">{{ errorMsg }}</p>
    <p class="ocr__privacy">{{ t('ocr.privacy') }}</p>
  </div>
</template>

<script setup lang="ts">
const emit = defineEmits<{
  appendText: [text: string]
}>()

const { t } = useI18n()
const ocrTitleId = 'wsc-ocr-title'
const busy = ref(false)
const errorMsg = ref('')
const fileRef = ref<HTMLInputElement | null>(null)

const MAX_BYTES = 8 * 1024 * 1024

function openFilePicker() {
  fileRef.value?.click()
}

async function onFile(ev: Event) {
  errorMsg.value = ''
  const input = ev.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file)
    return
  if (file.size > MAX_BYTES) {
    errorMsg.value = t('ocr.errorSize')
    input.value = ''
    return
  }

  busy.value = true
  try {
    const { createWorker } = await import('tesseract.js')
    const worker = await createWorker('nld+eng')
    const {
      data: { text },
    } = await worker.recognize(file)
    await worker.terminate()

    const cleaned = text.replace(/\r\n/g, '\n').trim()
    if (cleaned === '') {
      errorMsg.value = t('ocr.errorEmpty')
    }
    else {
      emit('appendText', cleaned)
    }
  }
  catch {
    errorMsg.value = t('ocr.errorGeneric')
  }
  finally {
    busy.value = false
    input.value = ''
  }
}
</script>

<style scoped>
.ocr {
  padding: 1rem 1.1rem;
  border-radius: var(--radius-md);
  border: 2px dashed var(--border-strong);
  background: var(--surface-page);
}

.ocr__label {
  margin: 0 0 0.35rem;
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--text-primary);
}

.ocr__hint {
  margin: 0 0 0.65rem;
  font-size: 0.88rem;
  color: var(--text-secondary);
  line-height: 1.45;
}

.ocr__row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
}

.ocr__input {
  position: absolute;
  width: 0.1px;
  height: 0.1px;
  opacity: 0;
  overflow: hidden;
  z-index: -1;
}

.ocr__btn {
  min-height: 44px;
  padding: 0 1.1rem;
  font: inherit;
  font-size: 0.92rem;
  font-weight: 700;
  color: var(--brand);
  background: var(--brand-soft);
  border: 0;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition:
    background var(--duration-fast) var(--ease-out),
    opacity var(--duration-fast) var(--ease-out);
}

.ocr__btn:hover:not(:disabled) {
  background: color-mix(in srgb, var(--brand-soft) 82%, #ffffff);
}

.ocr__btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.ocr__btn:focus-visible {
  outline: 3px solid var(--accent);
  outline-offset: 2px;
}

.ocr__err {
  margin: 0.5rem 0 0;
  font-size: 0.8rem;
  color: var(--err);
}

.ocr__privacy {
  margin: 0.45rem 0 0;
  font-size: 0.8rem;
  color: var(--text-tertiary);
  line-height: 1.4;
}
</style>
