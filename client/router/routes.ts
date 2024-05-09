import Dashboard from "../views/Dashboard.vue";
import Game from "../views/Game.vue";
import Login from "../views/Login.vue";
import Profile from "../views/Profile.vue";

export const routes = [
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
        path: "/profile/:username?",
        name: "Profile",
        component: Profile,
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
