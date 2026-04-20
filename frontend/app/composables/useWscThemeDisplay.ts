import type { ComputedRef } from 'vue'

/**
 * Actieve light/dark voor UI (segment, meta theme-color) gelijk aan wat de gebruiker ziet.
 *
 * @nuxtjs/color-mode kan tijdens SSR/hydratie `value === 'system'` of kort out-of-sync zijn
 * met `html.dark` (preference system + OS dark → verkeerde pill). De DOM-class is leidend op de client.
 */
export function useWscThemeDisplay(): { themeDisplay: ComputedRef<'light' | 'dark'> } {
  const colorMode = useColorMode()
  /** Triggert computed op OS-wissel als preference `system` is. */
  const schemeTick = ref(0)

  if (import.meta.client) {
    onMounted(() => {
      const mq = window.matchMedia('(prefers-color-scheme: dark)')
      const bump = () => {
        schemeTick.value++
      }
      mq.addEventListener('change', bump)
      onUnmounted(() => mq.removeEventListener('change', bump))
    })
  }

  const themeDisplay = computed((): 'light' | 'dark' => {
    if (import.meta.server) {
      return colorMode.value === 'dark' ? 'dark' : 'light'
    }
    void colorMode.value
    void colorMode.preference
    void schemeTick.value
    return document.documentElement.classList.contains('dark') ? 'dark' : 'light'
  })

  return { themeDisplay }
}
