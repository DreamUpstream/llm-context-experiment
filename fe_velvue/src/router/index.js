import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth.js";
import AppLayout from "@/layout/AppLayout.vue";

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: "/",
      component: AppLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: "/",
          name: "dashboard",
          component: () => import("@/views/DashboardPage.vue"),
          meta: { requiresVerified: true, title: "Overview" },
        },
        {
          path: "/account",
          name: "account",
          component: () => import("@/views/pages/AccountPage.vue"),
        },

        {
          path: "/pages/empty",
          name: "empty",
          component: () => import("@/views/pages/EmptyPage.vue"),
          meta: { requiresVerified: true },
        },
        {
          path: "/pages/crud",
          name: "crud",
          component: () => import("@/views/pages/uikit/CrudPage.vue"),
          meta: { requiresVerified: true },
        },
        {
          path: "/documentation",
          name: "documentation",
          component: () => import("@/views/pages/uikit/DocumentationPage.vue"),
          meta: { requiresVerified: true },
        },
      ],
    },

    {
      path: "/auth/login",
      name: "login",
      component: () => import("@/views/pages/auth/LoginPage.vue"),
    },

    {
      path: "/404",
      name: "notfound",
      component: () => import("@/views/pages/NotFoundPage.vue"),
    },
    {
      path: "/auth/access",
      name: "accessDenied",
      component: () => import("@/views/pages/auth/AccessPage.vue"),
    },
    {
      path: "/auth/error",
      name: "error",
      component: () => import("@/views/pages/auth/ErrorPage.vue"),
    },
    {
      path: "/auth/register",
      name: "register",
      component: () => import("@/views/pages/auth/RegisterPage.vue"),
      meta: { isGuest: true },
    },
    {
      path: "/auth/forgot-password",
      name: "forgotPassword",
      component: () => import("@/views/pages/auth/ForgotPasswordPage.vue"),
      meta: { isGuest: true },
    },

    {
      path: "/:pathMatch(.*)*",
      redirect: "/404",
    },
  ],
});

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();

  // 1) Fetch the user if not loaded yet.
  if (authStore.user === null && !authStore.loading) {
    try {
      await authStore.fetchUser();
    } catch {
      // user stays null if error
    }
  }

  // 2) If route is protected but user is not logged in -> login
  if (to.meta.requiresAuth && !authStore.isLoggedIn) {
    return next({ name: "login" });
  }

  // 3) If route is guest-only but user *is* logged in -> dashboard
  if (to.meta.isGuest && authStore.isLoggedIn) {
    return next({ name: "dashboard" });
  }

  // 4) Check for email verification if route requires it
  //    - If user is logged in but not verified -> redirect to an "unverified" page
  if (to.meta.requiresVerified && authStore.isLoggedIn) {
    if (authStore.user.must_verify_email) {
      // user is unverified, so handle it
      return next({ name: "account" });
    }
  }

  return next();
});

export default router;
