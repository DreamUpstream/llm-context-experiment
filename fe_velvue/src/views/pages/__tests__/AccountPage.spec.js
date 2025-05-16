import { describe, it, expect, beforeEach, vi } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import { createTestingPinia } from "@pinia/testing";
import AccountPage from "../AccountPage.vue";
import { useAuthStore } from "@/stores/auth";
import api from "@/service/apiService";

// Mock components
vi.mock("primevue/useconfirm", () => ({
  useConfirm: () => ({
    require: vi.fn(),
  }),
}));

vi.mock("@/service/apiService", () => ({
  default: {
    post: vi.fn().mockResolvedValue({ data: { success: true } }),
  },
}));

// Mock router
vi.mock("vue-router", () => ({
  useRouter: () => ({
    push: vi.fn(),
  }),
}));

describe("AccountPage.vue", () => {
  let wrapper;
  let authStore;

  // Common test user data
  const testUser = {
    name: "Test User",
    email: "test@example.com",
    bio: "Initial test bio",
    avatar: null,
  };

  // Setup before each test
  beforeEach(() => {
    // Reset mocks
    vi.clearAllMocks();

    // Create a testing pinia with initial state
    const pinia = createTestingPinia({
      createSpy: vi.fn,
      initialState: {
        auth: {
          user: testUser,
          fetchUser: vi.fn(),
        },
      },
    });

    // Mount the component with shallow rendering
    wrapper = mount(AccountPage, {
      global: {
        plugins: [pinia],
        stubs: {
          // Global stubs
          ConfirmDialog: true,
          Message: true,
          Tabs: true,
          TabPanel: true,
          Avatar: true,
          FileUpload: true,
          InputText: true,
          Textarea: true,
          Button: true,
          DataTable: true,
          Column: true,
        },
      },
      shallow: true,
    });

    // Get auth store from pinia
    authStore = useAuthStore();
  });

  it("verifies bio field exists in component data", () => {
    // Instead of looking for DOM elements, we'll check the component's data directly
    // This confirms the bioField ref is defined and has the correct initial value
    expect(wrapper.vm.bioField).toBeDefined();
    expect(wrapper.vm.bioField).toBe(testUser.bio);
  });

  it("initializes bio field with user bio from auth store", () => {
    // Direct check of the bioField reactive property
    expect(wrapper.vm.bioField).toBe("Initial test bio");
  });

  it("allows updating bio field value", async () => {
    // Directly set the bioField to simulate user input
    wrapper.vm.bioField = "Updated bio content";
    await flushPromises();

    expect(wrapper.vm.bioField).toBe("Updated bio content");
  });

  it("includes bio field data when saving profile", async () => {
    // Set the bio field directly
    wrapper.vm.bioField = "New bio for profile save test";

    // Trigger save profile method
    await wrapper.vm.saveProfile();
    await flushPromises();

    // Verify API was called with correct data including bio
    expect(api.post).toHaveBeenCalledWith("/account/update", {
      name: wrapper.vm.nameField,
      email: wrapper.vm.emailField,
      bio: "New bio for profile save test",
      avatar: wrapper.vm.profileImage,
    });

    // Verify user data update was triggered
    expect(authStore.fetchUser).toHaveBeenCalled();
  });
});
