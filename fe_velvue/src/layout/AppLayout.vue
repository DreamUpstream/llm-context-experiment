<script setup>
import AppSidebar from "./AppSidebar.vue";
import UserMenu from "./UserMenu.vue";
import { useLayout } from "@/layout/composables/layout";
import { ref, computed, onMounted, watch } from "vue";
import { useAuthStore } from "@/stores/auth";

// Track window width dynamically
const windowWidth = ref(window.innerWidth);
const authStore = useAuthStore();

onMounted(() => {
  const handleResize = () => {
    windowWidth.value = window.innerWidth;
  };
  window.addEventListener("resize", handleResize);
  return () => window.removeEventListener("resize", handleResize);
});

// Determine if the hamburger button is visible
const isSidebarVisible = computed(() => {
  if (windowWidth.value <= 991) return true; // Always show on mobile
  return layoutConfig.menuMode !== "static"; // Show based on menu mode for desktops
});

const { activeTitle, layoutConfig, isSidebarActive, toggleMenu } = useLayout();

// Custom dashboard styling based on user preferences
const customAccentColor = computed(
  () => authStore.user?.dashboard_preference?.accent_color || null
);

const customBackgroundImage = computed(() => {
  const path = authStore.user?.dashboard_preference?.background_image_path;
  return path ? `${import.meta.env.VITE_API_URL}/storage/${path}` : null;
});

// Apply accent color to CSS variables when it changes
watch(
  customAccentColor,
  (newValue) => {
    if (newValue) {
      // Apply the custom accent color to the CSS variable
      document.documentElement.style.setProperty("--p-primary-color", newValue);
      document.documentElement.style.setProperty("--p-primary-500", newValue);

      // Also update other primary color variants for comprehensive theming
      document.documentElement.style.setProperty("--p-primary-400", newValue);
      document.documentElement.style.setProperty("--p-primary-600", newValue);

      // For dark mode, we might need different contrast settings
      document.documentElement.style.setProperty(
        "--p-primary-text-color",
        "#ffffff"
      );
    } else {
      // Reset to default theme colors if no custom color is set
      document.documentElement.style.removeProperty("--p-primary-color");
      document.documentElement.style.removeProperty("--p-primary-500");
      document.documentElement.style.removeProperty("--p-primary-400");
      document.documentElement.style.removeProperty("--p-primary-600");
      document.documentElement.style.removeProperty("--p-primary-text-color");
    }
  },
  { immediate: true }
);
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
      <div
        class="layout-main p-6 bg-main-content relative"
        :style="
          customBackgroundImage
            ? {
                backgroundImage: `url('${customBackgroundImage}')`,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
                backgroundRepeat: 'no-repeat',
              }
            : {}
        "
      >
        <!-- Semi-transparent overlay when background image is present -->
        <div
          v-if="customBackgroundImage"
          class="absolute inset-0 bg-black bg-opacity-20 dark:bg-opacity-40"
        ></div>

        <!-- Content container with proper z-index -->
        <div class="relative z-10">
          <div class="flex items-center justify-between py-4 mt-4">
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
    </div>

    <!-- Overlay Mask (click anywhere outside to close) -->
    <div v-if="isSidebarActive" class="layout-mask" @click="toggleMenu" />
  </div>
</template>

<style scoped>
.bg-main-content {
  background-color: var(--main-background-color);
}

/* Ensure content is readable when a background image is present */
:deep(.card) {
  backdrop-filter: blur(5px);
  background-color: var(--surface-card);
}

:deep([class*="app-dark"]) .card {
  background-color: rgba(30, 30, 30, 0.85);
}
</style>
