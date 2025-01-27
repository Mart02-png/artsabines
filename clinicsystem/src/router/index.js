// Import necessary dependencies
import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import ClientView from '../views/ClientView.vue'
import AdminView from '../views/AdminView.vue'
import AboutView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import ApprovalView from '../views/ApprovalView.vue'
// Create routes array
const routes = [
  {
    path: '/home',
    name: 'home',
    component: HomeView
  },
  {
    path: '/about',
    name: 'about',
    component: AboutView
  },
  {
    path: '/admin',
    name: 'admin',
    component: AdminView,
    meta: { requiresAuth: true }, // Add meta field to indicate route requires authentication
    // props: true // This allows passing route params as props
  },
  {
    path: '/',
    name: 'client',
    component: ClientView
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
  },
  {
    path: '/approval',
    name: 'approval',
    component: ApprovalView,
    meta: { requiresAuth: true } // Route requires authentication
  },
]

// Create router instance
const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

// Add navigation guard to check if user is logged in
router.beforeEach((to, from, next) => {
  const isAuthenticated = localStorage.getItem('token'); // Check if token exists in local storage
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth); // Check if route requires authentication

  // If route requires authentication and user is not logged in, redirect to login page
  if (requiresAuth && !isAuthenticated) {
    next('/login');
  } else if (to.name === 'login'&& isAuthenticated  || isAuthenticated && to.path === '/') {
    // If user is already logged in and tries to access login page, redirect to admin page or home page
    next('/admin'); // You can change '/admin' to any other page you want to redirect to
  }  else if (to.path === '/approval' && !isAuthenticated) {
    // If user tries to access the approval page without being authenticated, redirect to login
    next('/login');
  } else {
    next(); // Continue to the requested route
  }
});

// Export router instance
export default router
