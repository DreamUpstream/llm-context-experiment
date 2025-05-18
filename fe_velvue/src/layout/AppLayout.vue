<script setup>
import AppSidebar from "./AppSidebar.vue";
import UserMenu from "./UserMenu.vue";
import { useLayout } from "@/layout/composables/layout";
import { useAuthStore } from "@/stores/auth";
import { ref, computed, onMounted, watch } from "vue";

// Track window width dynamically
const windowWidth = ref(window.innerWidth);
const authStore = useAuthStore();
const dashboardBackgroundStyle = ref({});

// Apply dashboard preferences when user data is available
function applyDashboardPreferences() {
  if (authStore.user?.dashboard_preference) {
    const { accent_color, background_image_path } =
      authStore.user.dashboard_preference;

    // Apply accent color by setting PrimeVue primary color variables
    if (accent_color) {
      // Set the primary color variable which affects buttons, links, and highlights
      document.documentElement.style.setProperty(
        "--primary-color",
        accent_color
      );

      // Also set the other primary shade variables based on the main color for a complete theme
      // This uses a simple opacity-based approach for demo purposes
      // In production, you might want to use a color manipulation library for better shade generation
      document.documentElement.style.setProperty(
        "--primary-color-text",
        "#ffffff"
      );
      document.documentElement.style.setProperty(
        "--primary-50",
        `${accent_color}0d`
      ); // 5% opacity
      document.documentElement.style.setProperty(
        "--primary-100",
        `${accent_color}1a`
      ); // 10% opacity
      document.documentElement.style.setProperty(
        "--primary-200",
        `${accent_color}33`
      ); // 20% opacity
      document.documentElement.style.setProperty(
        "--primary-300",
        `${accent_color}4d`
      ); // 30% opacity
      document.documentElement.style.setProperty(
        "--primary-400",
        `${accent_color}66`
      ); // 40% opacity
      document.documentElement.style.setProperty("--primary-500", accent_color); // 100% - main color
      document.documentElement.style.setProperty("--primary-600", accent_color); // darker shades would need color manipulation
      document.documentElement.style.setProperty("--primary-700", accent_color);
      document.documentElement.style.setProperty("--primary-800", accent_color);
      document.documentElement.style.setProperty("--primary-900", accent_color);
    } else {
      // Reset to default theme colors by removing our custom properties
      document.documentElement.style.removeProperty("--primary-color");
      document.documentElement.style.removeProperty("--primary-color-text");
      document.documentElement.style.removeProperty("--primary-50");
      document.documentElement.style.removeProperty("--primary-100");
      document.documentElement.style.removeProperty("--primary-200");
      document.documentElement.style.removeProperty("--primary-300");
      document.documentElement.style.removeProperty("--primary-400");
      document.documentElement.style.removeProperty("--primary-500");
      document.documentElement.style.removeProperty("--primary-600");
      document.documentElement.style.removeProperty("--primary-700");
      document.documentElement.style.removeProperty("--primary-800");
      document.documentElement.style.removeProperty("--primary-900");
    }

    // Apply background image if available
    if (background_image_path) {
      dashboardBackgroundStyle.value = {
        backgroundImage: `url(${import.meta.env.VITE_API_URL}/storage/${background_image_path})`,
        backgroundSize: "cover",
        backgroundPosition: "center",
        backgroundRepeat: "no-repeat",
      };
    } else {
      dashboardBackgroundStyle.value = {};
    }
  }
}

onMounted(() => {
  const handleResize = () => {
    windowWidth.value = window.innerWidth;
  };
  window.addEventListener("resize", handleResize);

  // Apply preferences on component mount
  applyDashboardPreferences();

  return () => window.removeEventListener("resize", handleResize);
});

// Watch for changes in user preferences
watch(
  () => authStore.user?.dashboard_preference,
  () => applyDashboardPreferences(),
  { deep: true }
);

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
        class="layout-main p-6 bg-main-content"
        :style="dashboardBackgroundStyle"
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
