import Settings from '@/pages/admin/settings/AccountSettings.vue';
import ForgotPassword from '@/pages/forgot-password.vue';
import Dashboard from '@/pages/index.vue';
import Landing from '@/pages/landing.vue';
import Login from '@/pages/login.vue';
import Register from '@/pages/register.vue';
import ResetPassword from '@/pages/reset-password.vue';
import VerifyEmail from '@/pages/verify-email.vue';
import { setupLayouts } from 'virtual:generated-layouts';
import { createRouter, createWebHistory } from 'vue-router';
import routes from '~pages';
import { isUserLoggedIn } from './utils';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/landing',
      name: 'Landing',
      component: Landing,
      beforeEnter: (to, from, next) => {
      const isLoggedIn = isUserLoggedIn()
        if (isLoggedIn) {
          next('/');
        } else {
          next();
        }
      }
    },

    {
      path: '/login',
      name: 'Login',
      component: Login,
      beforeEnter: (to, from, next) => {
      const isLoggedIn = isUserLoggedIn()
        if (isLoggedIn) {
          next('/');
        } else {
          next();
        }
      }
    },

    {
      path: '/register',
      name: 'Register',
      component: Register,
      beforeEnter: (to, from, next) => {
        const isLoggedIn = isUserLoggedIn();
        if (isLoggedIn) {
          next('/');
        } else {
          next(); // Allows access to the /register page if not logged in. 
        }
      }
    },

    {
      path: '/verify-email/:token',
      name: 'VerifyEmail',
      component: VerifyEmail,
      beforeEnter: (to, from, next) => {
        const isLoggedIn = isUserLoggedIn();
        if (isLoggedIn) {
          next('/');
        } else {
          next();
        }
      }
    },

    {
      path: '/reset-password/:token',
      name: 'ResetPassword',
      component: ResetPassword,
      beforeEnter: (to, from, next) => {
        const isLoggedIn = isUserLoggedIn();
        if (isLoggedIn) {
          next('/');
        } else {
          next();
        }
      }
    },
    {
      path: '/forgot-password',
      name: 'ForgotPassword',
      component: ForgotPassword,
      beforeEnter: (to, from, next) => {
        const isLoggedIn = isUserLoggedIn();
        if (isLoggedIn) {
          next('/');
        } else {
          next();
        }
      }
    },
    {
      path: '/admin/settings/:tab',
      name: '/admin/settings',
      component: Settings,
      beforeEnter: (to, from, next) => {
        const isLoggedIn = isUserLoggedIn();
        if (isLoggedIn) {
          next('/');
        } else {
          next();
        }
      }
    },
    

    ...setupLayouts(routes).map(route => {
      return {
        ...route,
        meta: { requiresAuth: true }
      }
    }),

    {
      path: '/',
      component: Dashboard,
      beforeEnter: (to, from, next) => {
      const isLoggedIn = isUserLoggedIn()
        if (!isLoggedIn) {
          next('/login');
        } else {
          next();
        }
      },
    },
  ],

});

// Add a navigation guard to check for the auth token on all routes

// router.beforeEach((to, from, next) => {
//   const isLoggedIn = isUserLoggedIn();
//   const userData = JSON.parse(localStorage.getItem('userData') || '{}')
//   const userRole = userData && userData.role ? userData.role : null

//   if (to.matched.some((record) => record.meta.requiresAuth) && !isLoggedIn) {
//     next('/login'); // Redirect to the login page if authentication is required, and the user is not logged in.
//   } else if (to.path.startsWith('/client') && userRole !== 'Client') {
//     next('/not-authorized'); // Redirect to the not-authorized page if the user is not a client and tries to access a client route.
//   } else if (to.path.startsWith('/admin') && userRole !== 'Admin') {
//     next('/not-authorized'); // Redirect to the not-authorized page if the user is not a admin and tries to access a admin route.
//   } else if (to.path.startsWith('/editor') && userRole !== 'Editor') {
//     next('/not-authorized'); // Redirect to the not-authorized page if the user is not a editor and tries to access a editor route.
//   } else {
//     next(); // Continue with the navigation if all conditions are met.
//   }
// });

router.beforeEach((to, from, next) => {
  const isLoggedIn = isUserLoggedIn();
  const userData = JSON.parse(localStorage.getItem('userData') || '{}');
  const userRole = userData && userData.role ? userData.role : null;
  const trueorfalse = JSON.parse(localStorage.getItem('isUnderMaintenance') || '{}');
  const isMaintenance = trueorfalse && trueorfalse ? trueorfalse : null;
  // Check if maintenance mode is active
  const isUnderMaintenance = isMaintenance; // Set to true or false as needed
  if (to.path === '/verify-email' && to.query.token) {
    // Allow access to the reset password route with a valid token, even if the user is logged in
    next();
  } else if (isLoggedIn && to.path === '/verify-email' && !to.query.token) {
    // Redirect to the home page if the user is logged in and trying to access the reset password page without a valid token
    next('/');
  } else if (to.path === '/reset-password' && to.query.token) {
    // Allow access to the reset password route with a valid token, even if the user is logged in
    next();
  } else if (isLoggedIn && to.path === '/reset-password' && !to.query.token) {
    // Redirect to the home page if the user is logged in and trying to access the reset password page without a valid token
    next('/');
  } else if (isUnderMaintenance === true && to.path !== '/under-maintenance' && (userRole === 'Client' || userRole === 'Editor')) {
    if (isLoggedIn) {
      // If the user is logged in during maintenance mode, redirect to maintenance page
      next('/under-maintenance');
    } else {
      // If the user is not logged in, continue with maintenance check
      next();
    }
  } else if (to.matched.some((record) => record.meta.requiresAuth) && !isLoggedIn) {
    next('/login'); // Redirect to the login page if authentication is required, and the user is not logged in.
  } else if (to.path.startsWith('/client') && userRole !== 'Client') {
    next('/not-authorized'); // Redirect to the not-authorized page if the user is not a client and tries to access a client route.
  } else if (to.path.startsWith('/admin') && userRole !== 'Admin') {
    next('/not-authorized'); // Redirect to the not-authorized page if the user is not an admin and tries to access an admin route.
  } else if (to.path.startsWith('/editor') && userRole !== 'Editor') {
    next('/not-authorized'); // Redirect to the not-authorized page if the user is not an editor and tries to access an editor route.
  } else {
    next(); // Continue with the navigation if all conditions are met.
  }
});


// Docs: https://router.vuejs.org/guide/advanced/navigation-guards.html#global-before-guards

export default router
