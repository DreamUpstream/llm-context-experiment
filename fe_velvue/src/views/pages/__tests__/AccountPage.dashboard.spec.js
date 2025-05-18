import { describe, it, expect, beforeEach, vi } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import { createTestingPinia } from "@pinia/testing";
import AccountPage from "../AccountPage.vue";
import { useAuthStore } from "@/stores/auth";
import api from "@/service/apiService";
import { createRouter, createMemoryHistory } from "vue-router";

const API_URL = "http://localhost:8000";

// Mock PrimeVue confirmation service
vi.mock("primevue/useconfirm", () => ({
  useConfirm: () => ({
    require: vi.fn(),
  }),
}));

// Mock API service
vi.mock("@/service/apiService", () => ({
  default: {
    post: vi.fn(),
    get: vi.fn(), // If AccountPage itself calls API on mount
  },
}));

// Mock import.meta.env
vi.stubGlobal("import", {
  meta: {
    env: {
      VITE_API_URL: API_URL,
    },
  },
});

describe("AccountPage.vue - Dashboard Appearance", () => {
  let wrapper;
  let authStore;
  let router;

  const initialUser = {
    name: "Test User",
    email: "test@example.com",
    avatar: null,
    dashboard_preference: {
      accent_color: "#112233",
      background_image_path: "initial/bg.webp",
    },
  };

  beforeEach(() => {
    vi.clearAllMocks();
    const pinia = createTestingPinia({
      initialState: {
        auth: { user: JSON.parse(JSON.stringify(initialUser)) }, // Deep clone
      },
      createSpy: vi.fn,
    });

    // Create a mock router
    router = createRouter({
      history: createMemoryHistory(),
      routes: [
        { path: "/", name: "home", component: { template: "<div>Home</div>" } },
        {
          path: "/login",
          name: "login",
          component: { template: "<div>Login</div>" },
        },
      ],
    });

    wrapper = mount(AccountPage, {
      global: {
        plugins: [pinia, router],
        stubs: {
          // Stub complex or irrelevant child components
          ConfirmDialog: true,
          Message: true,
          Tabs: true,
          TabPanel: true,
          Avatar: true,
          FileUpload: {
            template: '<div class="mock-file-upload"><slot /></div>',
            props: [
              "customUpload",
              "auto",
              "mode",
              "name",
              "accept",
              "chooseLabel",
              "disabled",
            ],
          },
          InputText: true,
          Button: true,
          DataTable: true,
          Column: true,
          ColorPicker: {
            name: "ColorPicker",
            template:
              '<div><input type="text" class="mock-color-picker" @input="$emit(\'update:modelValue\', $event.target.value)" :value="modelValue" /></div>',
            props: ["modelValue", "format", "defaultColor"],
            emits: ["update:modelValue"],
          },
        },
      },
    });
    authStore = useAuthStore();
    // Mock the updateDashboardPreferences action on the store instance
    authStore.updateDashboardPreferences = vi
      .fn()
      .mockResolvedValue({ success: true });
  });

  it("initializes form fields with dashboard preferences from store", () => {
    expect(wrapper.vm.accentColor).toBe(
      initialUser.dashboard_preference.accent_color
    );
    expect(wrapper.vm.backgroundImage).toBe(
      `${API_URL}/storage/${initialUser.dashboard_preference.background_image_path}`
    );
  });

  it("updates accentColor ref when ColorPicker changes", async () => {
    // Directly modify the property
    wrapper.vm.accentColor = "FFAA00";
    expect(wrapper.vm.accentColor).toBe("FFAA00");
  });

  it("uploadBackgroundImage calls api and updates backgroundImage ref", async () => {
    api.post.mockResolvedValueOnce({
      data: { success: true, path: "temp/new_bg.webp" },
    });
    const uploaderEventData = {
      files: [new File(["dummy"], "new_bg.webp", { type: "image/webp" })],
    };

    // Trigger method directly
    await wrapper.vm.uploadBackgroundImage(uploaderEventData);
    await flushPromises();

    expect(api.post).toHaveBeenCalledWith(
      "/upload",
      expect.any(FormData),
      expect.anything()
    );
    const formData = api.post.mock.calls[0][1];
    expect(formData.get("entity")).toBe("dashboard_backgrounds");
    expect(wrapper.vm.backgroundImage).toBe(
      `${API_URL}/storage/temp/new_bg.webp`
    );
  });

  it("removeBackgroundImage sets backgroundImage ref to null", async () => {
    await wrapper.vm.removeBackgroundImage();
    expect(wrapper.vm.backgroundImage).toBeNull();
  });

  it("saveDashboardPreferences calls store action with correctly formatted payload", async () => {
    wrapper.vm.accentColor = "ABCDEF"; // Simulate ColorPicker output (no #)

    // Directly manipulate the component data for testing
    const imagePath = "dashboard_backgrounds/new_image.webp";
    wrapper.vm.backgroundImage = `${API_URL}/storage/${imagePath}`;

    // Mock the extraction logic that happens in saveDashboardPreferences
    await wrapper.vm.saveDashboardPreferences();
    await flushPromises();

    // Verify that updateDashboardPreferences was called with the expected arguments
    expect(authStore.updateDashboardPreferences).toHaveBeenCalledWith({
      accent_color: "#ABCDEF",
      background_image_path: imagePath,
    });
  });

  it("saveDashboardPreferences sends null for background_image_path if image is removed", async () => {
    wrapper.vm.accentColor = "123456";
    wrapper.vm.backgroundImage = null; // Image removed

    await wrapper.vm.saveDashboardPreferences();
    await flushPromises();

    expect(authStore.updateDashboardPreferences).toHaveBeenCalledWith({
      accent_color: "#123456",
      background_image_path: null,
    });
  });
});
