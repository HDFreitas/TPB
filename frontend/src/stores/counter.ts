import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useCounterStore = defineStore('counter', () => {
  // state
  const count = ref<number>(0)

  // actions
  function increment(): void {
    count.value++
  }

  // getters
  const doubleCount = computed((): number => count.value * 2)

  return {
    count,
    increment,
    doubleCount
  }
})