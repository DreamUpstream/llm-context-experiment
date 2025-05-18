import { describe, it, expect, beforeEach, vi } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useAuthStore } from "@/stores/auth";
import api from "@/service/apiService";

vi.mock("@/service/apiService");

describe("Auth Store - Dashboard Preferences", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    api.post.mockClear();
    api.get.mockClear();
  });

  it("dashboardPreference getter returns null if user or preferences are not set", () => {
    const authStore = useAuthStore();
    authStore.user = null;
    expect(authStore.dashboardPreference).toBeNull();

    authStore.user = { name: "Test" };
    expect(authStore.dashboardPreference).toBeNull();

    authStore.user = { name: "Test", dashboard_preference: null };
    expect(authStore.dashboardPreference).toBeNull();
  });

  it("dashboardPreference getter returns preference object when set", () => {
    const authStore = useAuthStore();
    const prefs = {
      accent_color: "#123",
      background_image_path: "path/img.webp",
    };
    authStore.user = { name: "Test", dashboard_preference: prefs };
    expect(authStore.dashboardPreference).toEqual(prefs);
  });

  it("fetchUser action correctly populates dashboard_preference", async () => {
    const authStore = useAuthStore();
    const mockUserResponse = {
      user: {
        name: "Test User",
        email: "test@example.com",
        dashboard_preference: {
          accent_color: "#FF0000",
          background_image_path: "test.webp",
        },
      },
    };
    api.get.mockResolvedValue({ data: mockUserResponse });

    await authStore.fetchUser();
    expect(authStore.user.dashboard_preference).toEqual(
      mockUserResponse.user.dashboard_preference
    );
  });

  it("updateDashboardPreferences action calls API and updates store", async () => {
    const authStore = useAuthStore();
    authStore.user = { id: 1, name: "Test User", dashboard_preference: null };

    const newPreferencesPayload = {
      accent_color: "#00FF00",
      background_image_path: "new.webp",
    };
    const apiResponse = {
      success: true,
      dashboard_preference: newPreferencesPayload,
    };
    api.post.mockResolvedValue({ data: apiResponse });

    await authStore.updateDashboardPreferences(newPreferencesPayload);

    expect(api.post).toHaveBeenCalledWith(
      "/account/dashboard-preferences",
      newPreferencesPayload
    );
    expect(authStore.user.dashboard_preference).toEqual(newPreferencesPayload);
  });

  it("updateDashboardPreferences handles API error", async () => {
    const authStore = useAuthStore();
    authStore.user = { id: 1, name: "Test User", dashboard_preference: null };
    api.post.mockRejectedValue(new Error("API Error"));

    await expect(authStore.updateDashboardPreferences({})).rejects.toThrow(
      "API Error"
    );
  });
});
