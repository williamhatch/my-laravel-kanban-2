import { createRouter, createWebHistory } from "vue-router";
import type { RouteLocationNormalized, NavigationGuardNext } from "vue-router";
import { authState, authService } from "../services/auth";
import KanbanBoard from "../components/KanbanBoard.vue";
import Login from "../views/Login.vue";
import Register from "../views/Register.vue";

const routes = [
  {
    path: "/",
    name: "KanbanBoard",
    component: KanbanBoard,
    beforeEnter: async (
      to: RouteLocationNormalized,
      from: RouteLocationNormalized,
      next: NavigationGuardNext,
    ) => {
      // Check if we have a token but haven't initialized auth state yet
      const token = localStorage.getItem("auth_token");
      if (token && !authState.isAuthenticated) {
        try {
          await authService.fetchUser();
          next();
        } catch {
          localStorage.removeItem("auth_token");
          next({ name: "Login" });
        }
      }

      if (!authState.isAuthenticated) {
        next({ name: "Login" });
      } else {
        next();
      }
    },
  },
  {
    path: "/login",
    name: "Login",
    component: Login,
  },
  {
    path: "/register",
    name: "Register",
    component: Register,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
