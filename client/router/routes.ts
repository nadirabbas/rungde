import Dashboard from "../views/Dashboard.vue";
import Game from "../views/Game.vue";
import Ion from "../views/Ion.vue";
import Login from "../views/Login.vue";

export const routes = [
    {
        path: "/ion",
        component: Ion,
        meta: {
            blank: true,
        },
    },
    {
        path: "/login",
        name: "Login",
        component: Login,
        meta: {
            redirectIfAuth: true,
        },
    },
    {
        path: "/",
        name: "Dashboard",
        component: Dashboard,
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: "/play",
        name: "Game",
        component: Game,
        meta: {
            requiresAuth: true,
            blank: true,
        },
    },
];
