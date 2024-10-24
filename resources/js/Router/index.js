import VueRouter from "vue-router";
import routes from "./routes";

const router = new VueRouter({
    mode: "history",
    routes,
});

router.beforeEach((to, from, next) => {
    if (to.matched.some((record) => record.meta?.requiresAth)) {
        if (!window.WMS.isLoggedIn && to.name !== "login") {
            window.location.href = "/login";
        }
    }
    next();
});

export default router;
