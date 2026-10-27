import type { Directive } from 'vue'

/**
 * `v-smooth-details` op een <details>: open- en dichtklappen animeren de hoogte in plaats van
 * direct te verspringen. Werkt met de gewone <summary> (toetsenbord en schermlezers blijven werken);
 * bij `prefers-reduced-motion` of zonder JS valt het terug op het standaardgedrag.
 */
const DURATION = 320
const EASING = 'cubic-bezier(0.22, 1, 0.36, 1)'

type State = { animation: Animation | null, onClick: (e: MouseEvent) => void }
const states = new WeakMap<HTMLDetailsElement, State>()

function reducedMotion(): boolean {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches
}

function animateHeight(el: HTMLDetailsElement, state: State, from: number, to: number, onDone: () => void) {
  state.animation?.cancel()
  el.style.overflow = 'hidden'
  const animation = el.animate({ height: [`${from}px`, `${to}px`] }, { duration: DURATION, easing: EASING })
  state.animation = animation
  animation.onfinish = () => {
    state.animation = null
    el.style.height = ''
    el.style.overflow = ''
    onDone()
  }
  animation.oncancel = () => {
    state.animation = null
  }
}

export default defineNuxtPlugin((nuxtApp) => {
  const smoothDetails: Directive<HTMLDetailsElement> = {
    getSSRProps: () => ({}),
    mounted(el) {
      if (!import.meta.client || typeof el.animate !== 'function')
        return
      const summary = el.querySelector(':scope > summary') as HTMLElement | null
      if (!summary)
        return

      const state: State = {
        animation: null,
        onClick(e) {
          if (reducedMotion())
            return
          e.preventDefault()
          const start = el.offsetHeight
          if (el.open && !el.classList.contains('is-opening')) {
            // Dichtklappen: van huidige hoogte naar alleen de summary.
            el.classList.add('is-closing')
            animateHeight(el, state, start, summary.offsetHeight, () => {
              el.open = false
              el.classList.remove('is-closing')
            })
          }
          else {
            // Openklappen: eerst openen (inhoud meten), dan van summary-hoogte naar volle hoogte.
            el.classList.remove('is-closing')
            el.classList.add('is-opening')
            el.style.height = `${start}px`
            el.open = true
            requestAnimationFrame(() => {
              el.style.height = ''
              const end = el.scrollHeight
              animateHeight(el, state, start, end, () => el.classList.remove('is-opening'))
            })
          }
        },
      }
      summary.addEventListener('click', state.onClick)
      states.set(el, state)
    },
    beforeUnmount(el) {
      const state = states.get(el)
      if (!state)
        return
      state.animation?.cancel()
      el.querySelector(':scope > summary')?.removeEventListener('click', state.onClick as EventListener)
      states.delete(el)
    },
  }

  nuxtApp.vueApp.directive('smooth-details', smoothDetails)
})
