import { createRouter, createWebHistory } from 'vue-router';
import Login from '../../views/Auth/Login.vue';
import Register from '../../views/Auth/Register.vue';
import { useAuthStore } from '../stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: Login, // @TODO: Add View
        meta: { guestOnly: true } 
    },
    {
        path: '/register',
        name: 'register',
        component: Register, //
        meta: { guestOnly: true }
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach(async (to, from, next) => {
    const auth = useAuthStore();

    if (auth.initialising) {
        await new Promise(resolve => {
            const unwatch = auth.$subscribe(() => {
                if (!auth.initialising) {
                    unwatch();
                    resolve();
                }
            });
        });
    }

    // Handle protected routes
    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return next({ name: 'login' });
    }

    next();
});

export default router;