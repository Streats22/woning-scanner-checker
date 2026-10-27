import type { Directive } from 'vue'

/**
 * `v-reveal` / `v-reveal="i"`: element schuift zacht in beeld zodra het zichtbaar wordt.
 * De waarde is de positie in een rij (vertraging i × 70 ms, max. 8 stappen).
 * Alleen in de browser en niet bij `prefers-reduced-motion`; zonder JS blijft alles gewoon zichtbaar.
 */
export default defineNuxtPlugin((nuxtApp) => {
  let observer: IntersectionObserver | null = null

  function getObserver(): IntersectionObserver {
    if (!observer) {
      observer = new IntersectionObserver((entries) => {
        for (const entry of entries) {
          if (!entry.isIntersecting)
            continue
          entry.target.classList.add('is-in')
          observer?.unobserve(entry.target)
        }
      }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 })
    }
    return observer
  }

  const reveal: Directive<HTMLElement, number | undefined> = {
    getSSRProps: () => ({}),
    mounted(el, binding) {
      if (!import.meta.client || typeof IntersectionObserver === 'undefined')
        return
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches)
        return
      const step = Math.min(Math.max(Number(binding.value) || 0, 0), 8)
      el.style.setProperty('--reveal-delay', `${step * 70}ms`)
      el.classList.add('wsc-reveal')
      getObserver().observe(el)
    },
    beforeUnmount(el) {
      observer?.unobserve(el)
    },
  }

  nuxtApp.vueApp.directive('reveal', reveal)
})
