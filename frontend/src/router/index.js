import { createRouter, createWebHistory } from "vue-router"

import Home from "../views/Home.vue"
import Login from "../views/Login.vue"
import Register from "../views/Register.vue"
import ArtisanDetails from "../views/ArtisanDetails.vue" 
import Profile from "../views/Profile.vue"
import ArtisanDashboard from "../views/ArtisanDashboard.vue"

const routes = [
  { path: "/", component: Home },
  { path: "/login", component: Login },
  { path: "/register", component: Register },
  { path: "/artisans/:id", component: ArtisanDetails },
  { path: "/profile", component: Profile },
  { path: "/artisan/dashboard", component: ArtisanDashboard },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router


