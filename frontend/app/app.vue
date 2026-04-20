<template>
  <div class="shell">
    <NuxtRouteAnnouncer />
    <NuxtLayout>
      <NuxtPage
        :transition="{
          name: 'wsc-page',
          mode: 'out-in',
        }"
      />
    </NuxtLayout>
    <ClientOnly>
      <CookieConsent />
    </ClientOnly>
  </div>
</template>

<style scoped>
.shell {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
</style>
