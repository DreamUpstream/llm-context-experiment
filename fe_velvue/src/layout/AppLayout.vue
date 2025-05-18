<script setup>
import AppSidebar from "./AppSidebar.vue";
import UserMenu from "./UserMenu.vue";
import { useLayout } from "@/layout/composables/layout";
import { useAuthStore } from "@/stores/auth";
import { ref, computed, onMounted, watch } from "vue";

// Track window width dynamically
const windowWidth = ref(window.innerWidth);
const authStore = useAuthStore();

onMounted(() => {
  const handleResize = () => {
    windowWidth.value = window.innerWidth;
  };
  window.addEventListener("resize", handleResize);
  applyUserCustomStyles();
  return () => window.removeEventListener("resize", handleResize);
});

// Apply user's custom styles when they change
watch(
  () => authStore.dashboardPreference,
  (newValue) => {
    applyUserCustomStyles();
  },
  { deep: true }
);

function applyUserCustomStyles() {
  const preferences = authStore.dashboardPreference;
  const root = document.documentElement;

  // Apply custom accent color if defined
  if (preferences?.accent_color) {
    root.style.setProperty("--p-primary-500", preferences.accent_color);
    root.style.setProperty("--p-primary-color", preferences.accent_color);
  } else {
    // Reset to default PrimeVue Aura theme colors
    root.style.removeProperty("--p-primary-500");
    root.style.removeProperty("--p-primary-color");
  }
}

// Compute the background style based on user preferences
const backgroundStyle = computed(() => {
  const preferences = authStore.dashboardPreference;
  if (preferences?.background_image_path) {
    return {
      backgroundImage: `url(${import.meta.env.VITE_API_URL}/storage/${preferences.background_image_path})`,
      backgroundSize: "cover",
      backgroundPosition: "center",
      backgroundRepeat: "no-repeat",
    };
  }
  return {};
});

// Determine if the hamburger button is visible
const isSidebarVisible = computed(() => {
  if (windowWidth.value <= 991) return true; // Always show on mobile
  return layoutConfig.menuMode !== "static"; // Show based on menu mode for desktops
});

const { activeTitle, layoutConfig, isSidebarActive, toggleMenu } = useLayout();
</script>

<template>
  <div
    :class="[
      'layout-wrapper',
      layoutConfig.menuMode === 'static' ? 'layout-static' : 'layout-overlay',
      isSidebarActive ? 'layout-mobile-active blocked-scroll' : '',
    ]"
  >
    <!-- Sidebar -->
    <app-sidebar class="card-container" />

    <!-- Main Content -->
    <div class="layout-main-container">
      <!-- Header -->

      <!-- Page Content -->
      <div class="layout-main p-6 bg-main-content" :style="backgroundStyle">
        <div
          class="flex items-center justify-between py-4 bg-main-content mt-4"
        >
          <!-- Hamburger button -->
          <button
            v-if="isSidebarVisible"
            class="p-link mr-4 p-2 px-3 inline-flex items-center justify-center border border-gray-200 rounded-lg"
            @click="toggleMenu"
          >
            <i class="pi pi-bars text-xl" />
          </button>

          <h1 class="text-xl font-semibold">
            {{ activeTitle }}
          </h1>
          <user-menu />
        </div>
        <router-view />
      </div>
    </div>

    <!-- Overlay Mask (click anywhere outside to close) -->
    <div v-if="isSidebarActive" class="layout-mask" @click="toggleMenu" />
  </div>
</template>

<style scoped>
.bg-main-content {
  background-color: var(--main-background-color);
}
</style>
