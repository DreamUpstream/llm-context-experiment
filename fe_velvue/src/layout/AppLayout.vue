<script setup>
import AppSidebar from "./AppSidebar.vue";
import UserMenu from "./UserMenu.vue";
import { useLayout } from "@/layout/composables/layout";
import { ref, computed, onMounted, watch, onBeforeUnmount } from "vue";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const apiUrl = import.meta.env.VITE_API_URL;

// Track window width dynamically
const windowWidth = ref(window.innerWidth);

// Reference to main content area for background image
const mainContent = ref(null);

onMounted(() => {
  const handleResize = () => {
    windowWidth.value = window.innerWidth;
  };
  window.addEventListener("resize", handleResize);

  // Apply user preferences if they exist
  applyUserPreferences();

  return () => window.removeEventListener("resize", handleResize);
});

onBeforeUnmount(() => {
  // Reset any custom CSS variables when component is unmounted
  document.documentElement.style.removeProperty("--p-primary-color");
});

// Watch for changes in user preferences
watch(
  () => authStore.user?.dashboard_preference,
  (newPreference) => {
    applyUserPreferences();
  },
  { deep: true }
);

// Apply user preferences to the UI
const applyUserPreferences = () => {
  const preference = authStore.user?.dashboard_preference;

  if (preference) {
    // Apply accent color if set
    if (preference.accent_color) {
      document.documentElement.style.setProperty(
        "--p-primary-color",
        preference.accent_color
      );
    } else {
      document.documentElement.style.removeProperty("--p-primary-color");
    }
  } else {
    // Reset to defaults if no preferences
    document.documentElement.style.removeProperty("--p-primary-color");
  }
};

// Generate background image style if set
const backgroundStyle = computed(() => {
  const preference = authStore.user?.dashboard_preference;
  if (preference?.background_image_path) {
    return {
      backgroundImage: `url(${apiUrl}/storage/${preference.background_image_path})`,
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
      <div
        ref="mainContent"
        class="layout-main p-6 bg-main-content"
        :style="backgroundStyle"
      >
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
