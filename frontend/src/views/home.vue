<script setup>
import { ref, onMounted, computed } from "vue"
import axios from "axios"

// ── Auth ──────────────────────────────────────────────
const user = JSON.parse(localStorage.getItem("user") || "null")
const isArtisan = computed(() => user?.roles?.some(r => r.name === "artisan"))

// ── Client state ──────────────────────────────────────
const artisans = ref([])
const categories = ref([])
const search = ref("")
const selectedCategory = ref("")
const selectedCity = ref("")
const isLoading = ref(false)

const cities = ["Casablanca", "Rabat", "Marrakech", "Fès", "Tanger", "Agadir", "Meknès", "Oujda"]

// ── Artisan dashboard state ───────────────────────────
const bookings = ref([])
const bookingsLoading = ref(false)
const stats = computed(() => ({
  total:    bookings.value.length,
  pending:  bookings.value.filter(b => b.status === "pending").length,
  accepted: bookings.value.filter(b => b.status === "accepted").length,
  rejected: bookings.value.filter(b => b.status === "rejected").length,
}))

// ── Helpers ───────────────────────────────────────────
const getInitials = (name) => {
  if (!name) return "?"
  return name.split(" ").map(n => n[0]).join("").toUpperCase().slice(0, 2)
}

const avatarColors = [
  { bg: "#EFEBE9", color: "#5D4037" },
  { bg: "#E1F5EE", color: "#085041" },
  { bg: "#E6F1FB", color: "#0C447C" },
  { bg: "#EEEDFE", color: "#3C3489" },
  { bg: "#FFF3E0", color: "#E65100" },
]
const getAvatarStyle = (id) => avatarColors[id % avatarColors.length]

const statusLabel = { pending: "En attente", accepted: "Acceptée", rejected: "Refusée" }
const statusClass = { pending: "badge-pending", accepted: "badge-accepted", rejected: "badge-rejected" }

const formatDate = (d) => d ? new Date(d).toLocaleDateString("fr-FR", { day: "numeric", month: "short", year: "numeric" }) : "—"

// ── Client methods ────────────────────────────────────
const loadArtisans = async () => {
  isLoading.value = true
  try {
    let url = "http://127.0.0.1:8000/api/artisans?"
    if (search.value)          url += `search=${search.value}&`
    if (selectedCategory.value) url += `category_id=${selectedCategory.value}&`
    if (selectedCity.value)    url += `city=${selectedCity.value}&`
    const res = await axios.get(url)
    artisans.value = res.data
  } finally {
    isLoading.value = false
  }
}

const loadCategories = async () => {
  const res = await axios.get("http://127.0.0.1:8000/api/categories")
  categories.value = res.data.data ?? res.data
}

const selectCategory = (id) => {
  selectedCategory.value = selectedCategory.value === id ? "" : id
  loadArtisans()
}

const selectCity = (city) => {
  selectedCity.value = selectedCity.value === city ? "" : city
  loadArtisans()
}

const clearFilters = () => {
  search.value = ""
  selectedCategory.value = ""
  selectedCity.value = ""
  loadArtisans()
}

const hasFilters = () => search.value || selectedCategory.value || selectedCity.value

// ── Artisan methods ───────────────────────────────────
const loadBookings = async () => {
  bookingsLoading.value = true
  try {
    const token = localStorage.getItem("token")
    const res = await axios.get("http://127.0.0.1:8000/api/artisan-bookings", {
      headers: { Authorization: `Bearer ${token}` }
    })
    bookings.value = res.data
  } finally {
    bookingsLoading.value = false
  }
}

const updateStatus = async (booking, status) => {
  const token = localStorage.getItem("token")
  await axios.put(`http://127.0.0.1:8000/api/bookings/${booking.id}/${status}`, {}, {
    headers: { Authorization: `Bearer ${token}` }
  })
  booking.status = status
}

// ── Logout ────────────────────────────────────────────
const logout = async () => {
  const token = localStorage.getItem("token")
  try {
    await axios.post("http://127.0.0.1:8000/api/logout", {}, {
      headers: { Authorization: `Bearer ${token}` }
    })
  } finally {
    localStorage.removeItem("token")
    localStorage.removeItem("user")
    window.location.href = "/login"
  }
}

// ── Init ──────────────────────────────────────────────
onMounted(() => {
  if (isArtisan.value) {
    loadBookings()
  } else {
    loadArtisans()
    loadCategories()
  }
})
</script>

<template>
  <div class="page">

    <!-- ═══════════════ NAVBAR ═══════════════ -->
    <nav class="navbar">
      <div class="nav-left">
        <span class="nav-logo">Le Moqufe</span>
        <span class="nav-tag">Maroc</span>
      </div>
      <div class="nav-right">
        <template v-if="isArtisan">
          <router-link to="/profile" class="nav-link">Mon profil</router-link>
        </template>
        <template v-else>
          <router-link to="/my-bookings" class="nav-link">Mes réservations</router-link>
          <router-link to="/profile" class="nav-link">Mon profil</router-link>
        </template>
        <button class="nav-logout" @click="logout">Déconnexion</button>
      </div>
    </nav>

    <!-- ═══════════════ ARTISAN DASHBOARD ═══════════════ -->
    <div v-if="isArtisan" class="dashboard">

      <!-- Header -->
      <div class="dash-header">
        <div class="dash-header-inner">
          <div class="dash-greeting">
            <div class="dash-avatar">{{ getInitials(user?.name) }}</div>
            <div>
              <div class="dash-name">Bonjour, {{ user?.name }} 👋</div>
              <div class="dash-role">Artisan · {{ user?.city ?? "Maroc" }}</div>
            </div>
          </div>
          <button class="refresh-btn" @click="loadBookings">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
              <path d="M3 3v5h5"/>
            </svg>
            Actualiser
          </button>
        </div>
      </div>

      <div class="dash-body">

        <!-- Stats -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon stat-icon-total">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
              </svg>
            </div>
            <div class="stat-val">{{ stats.total }}</div>
            <div class="stat-label">Total réservations</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon stat-icon-pending">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
              </svg>
            </div>
            <div class="stat-val stat-pending">{{ stats.pending }}</div>
            <div class="stat-label">En attente</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon stat-icon-accepted">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
              </svg>
            </div>
            <div class="stat-val stat-accepted">{{ stats.accepted }}</div>
            <div class="stat-label">Acceptées</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon stat-icon-rejected">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/>
              </svg>
            </div>
            <div class="stat-val stat-rejected">{{ stats.rejected }}</div>
            <div class="stat-label">Refusées</div>
          </div>
        </div>

        <!-- Bookings table -->
        <div class="bookings-section">
          <div class="section-header">
            <h2 class="section-title">Réservations reçues</h2>
            <span class="section-sub" v-if="!bookingsLoading">{{ bookings.length }} au total</span>
          </div>

          <!-- Loading -->
          <div v-if="bookingsLoading" class="bookings-loading">
            <div v-for="i in 4" :key="i" class="booking-skeleton"></div>
          </div>

          <!-- Empty -->
          <div v-else-if="bookings.length === 0" class="bookings-empty">
            <div class="empty-icon">📭</div>
            <div class="empty-title">Aucune réservation pour l'instant</div>
            <div class="empty-sub">Vos futures réservations apparaîtront ici</div>
          </div>

          <!-- List -->
          <div v-else class="bookings-list">
            <div class="booking-card" v-for="b in bookings" :key="b.id">
              <div class="booking-left">
                <div class="booking-avatar" :style="{ background: getAvatarStyle(b.client?.id ?? b.id).bg, color: getAvatarStyle(b.client?.id ?? b.id).color }">
                  {{ getInitials(b.client?.name) }}
                </div>
                <div class="booking-info">
                  <div class="booking-client">{{ b.client?.name ?? "Client" }}</div>
                  <div class="booking-meta-row">
                    <span class="booking-date">
                      <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                      </svg>
                      {{ formatDate(b.booking_date ?? b.created_at) }}
                    </span>
                    <span class="booking-city" v-if="b.client?.city">
                      <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                      </svg>
                      {{ b.client.city }}
                    </span>
                    <span class="booking-price" v-if="b.price">
                      {{ b.price }} MAD
                    </span>
                  </div>
                  <div class="booking-note" v-if="b.description">{{ b.description }}</div>
                </div>
              </div>
              <div class="booking-right">
                <span class="badge" :class="statusClass[b.status]">{{ statusLabel[b.status] }}</span>
                <div class="booking-actions" v-if="b.status === 'pending'">
                  <button class="action-btn accept-btn" @click="updateStatus(b, 'accept')">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <path d="m20 6-11 11-5-5"/>
                    </svg>
                    Accepter
                  </button>
                  <button class="action-btn reject-btn" @click="updateStatus(b, 'reject')">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <path d="M18 6 6 18M6 6l12 12"/>
                    </svg>
                    Refuser
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- ═══════════════ CLIENT VIEW ═══════════════ -->
    <template v-else>

      <!-- Hero -->
      <div class="hero">
        <div class="hero-inner">
          <h1 class="hero-title">Trouvez l'artisan qu'il vous faut</h1>
          <p class="hero-sub">Plombiers · Électriciens · Jardiniers · Piscinistes — partout au Maroc</p>
          <div class="search-wrap">
            <div class="search-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
              </svg>
            </div>
            <input v-model="search" @input="loadArtisans" class="search-input" placeholder="Nom de l'artisan, spécialité..." />
            <select v-model="selectedCity" @change="loadArtisans" class="city-select">
              <option value="">Toute ville</option>
              <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters-bar">
        <div class="filters-inner">
          <span class="filter-label">Spécialité :</span>
          <div class="pills">
            <button class="pill" :class="{ active: selectedCategory === '' }" @click="selectCategory('')">Tous</button>
            <button v-for="cat in categories" :key="cat.id" class="pill" :class="{ active: selectedCategory === cat.id }" @click="selectCategory(cat.id)">
              {{ cat.name }}
            </button>
          </div>
          <button v-if="hasFilters()" class="clear-btn" @click="clearFilters">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M18 6 6 18M6 6l12 12"/>
            </svg>
            Effacer
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="main">
        <div class="results-header">
          <span class="results-count" v-if="!isLoading">
            <strong>{{ artisans.length }}</strong> artisan{{ artisans.length !== 1 ? 's' : '' }} trouvé{{ artisans.length !== 1 ? 's' : '' }}
            <span v-if="selectedCity"> à {{ selectedCity }}</span>
          </span>
          <span class="results-count" v-else>Chargement...</span>
        </div>

        <!-- Skeleton -->
        <div v-if="isLoading" class="grid">
          <div v-for="i in 6" :key="i" class="card skeleton"></div>
        </div>

        <!-- Empty -->
        <div v-else-if="artisans.length === 0" class="empty">
          <div class="empty-icon">🔍</div>
          <div class="empty-title">Aucun artisan trouvé</div>
          <div class="empty-sub">Essayez d'autres filtres ou une autre ville</div>
          <button class="empty-btn" @click="clearFilters">Réinitialiser</button>
        </div>

        <!-- Grid -->
        <div v-else class="grid">
          <div class="card" v-for="artisan in artisans" :key="artisan.id">
            <div class="card-head">
              <div class="avatar" :style="{ background: getAvatarStyle(artisan.id).bg, color: getAvatarStyle(artisan.id).color }">
                {{ getInitials(artisan.name) }}
              </div>
              <div class="card-meta">
                <div class="card-name">{{ artisan.name }}</div>
                <div class="card-location">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                  </svg>
                  {{ artisan.city ?? "Maroc" }}
                </div>
              </div>
              <span class="avail-badge">● Dispo</span>
            </div>

            <div class="stars-row" v-if="artisan.rating">
              <div class="stars">
                <svg v-for="i in 5" :key="i" width="13" height="13" viewBox="0 0 24 24">
                  <polygon :fill="i <= Math.round(artisan.rating) ? '#F59E0B' : '#E5E7EB'" points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/>
                </svg>
              </div>
              <span class="rating-val">{{ artisan.rating }}</span>
              <span class="rating-count">({{ artisan.reviews_count ?? 0 }} avis)</span>
            </div>

            <div class="cats" v-if="artisan.categories?.length">
              <span class="cat" v-for="cat in artisan.categories.slice(0, 3)" :key="cat.id">{{ cat.name }}</span>
              <span class="cat cat-more" v-if="artisan.categories.length > 3">+{{ artisan.categories.length - 3 }}</span>
            </div>

            <p class="card-bio" v-if="artisan.bio">{{ artisan.bio.slice(0, 80) }}{{ artisan.bio.length > 80 ? '…' : '' }}</p>

            <div class="card-footer">
              <div class="price-wrap">
                <span class="price-label">À partir de</span>
                <span class="price-val" v-if="artisan.price">{{ artisan.price }} MAD</span>
                <span class="price-val" v-else>Sur devis</span>
              </div>
              <router-link :to="`/artisans/${artisan.id}`" class="book-btn">
                Réserver
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
              </router-link>
            </div>
          </div>
        </div>
      </div>

    </template>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600&display=swap');
* { box-sizing: border-box; }

.page { background: #F7F5F2; min-height: 100vh; font-family: 'Sora', sans-serif; }

/* ── Navbar ── */
.navbar {
  position: sticky; top: 0; z-index: 100;
  background: rgba(255,255,255,0.92); backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(0,0,0,0.06);
  padding: 0 2rem; height: 58px;
  display: flex; align-items: center; justify-content: space-between;
}
.nav-left { display: flex; align-items: center; gap: 10px; }
.nav-logo { font-size: 16px; font-weight: 600; color: #3E2723; letter-spacing: -0.5px; }
.nav-tag { font-size: 10px; font-weight: 500; background: #EFEBE9; color: #6D4C41; padding: 2px 8px; border-radius: 99px; }
.nav-right { display: flex; align-items: center; gap: 16px; }
.nav-link { font-size: 13px; color: #6B7280; text-decoration: none; transition: color 0.15s; }
.nav-link:hover { color: #3E2723; }
.nav-logout { font-size: 12px; font-weight: 500; color: #993C1D; background: #FAECE7; border: none; border-radius: 8px; padding: 6px 12px; cursor: pointer; }

/* ── Dashboard ── */
.dashboard { min-height: calc(100vh - 58px); }

.dash-header {
  background: linear-gradient(135deg, #4E342E 0%, #6D4C41 60%, #8D6E63 100%);
  padding: 2rem;
}
.dash-header-inner {
  max-width: 1000px; margin: 0 auto;
  display: flex; align-items: center; justify-content: space-between;
}
.dash-greeting { display: flex; align-items: center; gap: 14px; }
.dash-avatar {
  width: 52px; height: 52px; border-radius: 14px;
  background: rgba(255,255,255,0.15); color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-size: 18px; font-weight: 600;
}
.dash-name { font-size: 18px; font-weight: 600; color: #FFF8F6; }
.dash-role { font-size: 12px; color: #BCAAA4; margin-top: 3px; }
.refresh-btn {
  display: flex; align-items: center; gap: 6px;
  font-size: 12px; font-weight: 500; color: #FFF8F6;
  background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2);
  border-radius: 8px; padding: 8px 14px; cursor: pointer; font-family: inherit;
  transition: background 0.15s;
}
.refresh-btn:hover { background: rgba(255,255,255,0.2); }

.dash-body { max-width: 1000px; margin: 0 auto; padding: 1.5rem 2rem 3rem; }

/* Stats */
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 1.5rem; }
.stat-card {
  background: #fff; border: 1px solid rgba(0,0,0,0.07);
  border-radius: 14px; padding: 1.2rem;
}
.stat-icon {
  width: 36px; height: 36px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center; margin-bottom: 10px;
}
.stat-icon-total   { background: #EFEBE9; color: #5D4037; }
.stat-icon-pending { background: #FFF8E1; color: #F59E0B; }
.stat-icon-accepted{ background: #E8F5E9; color: #2E7D32; }
.stat-icon-rejected{ background: #FFEBEE; color: #C62828; }
.stat-val { font-size: 26px; font-weight: 600; color: #1a1a1a; line-height: 1; margin-bottom: 4px; }
.stat-pending  { color: #F59E0B; }
.stat-accepted { color: #2E7D32; }
.stat-rejected { color: #C62828; }
.stat-label { font-size: 11px; color: #9CA3AF; }

/* Section */
.bookings-section { background: #fff; border: 1px solid rgba(0,0,0,0.07); border-radius: 14px; overflow: hidden; }
.section-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 1.1rem 1.4rem; border-bottom: 1px solid #F3F4F6;
}
.section-title { font-size: 14px; font-weight: 600; color: #1a1a1a; }
.section-sub { font-size: 12px; color: #9CA3AF; }

/* Skeleton */
.bookings-loading { padding: 1rem 1.4rem; display: flex; flex-direction: column; gap: 10px; }
.booking-skeleton {
  height: 64px; border-radius: 10px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 50%, #f0f0f0 75%);
  background-size: 200% 100%; animation: shimmer 1.2s infinite;
}

/* Empty */
.bookings-empty { text-align: center; padding: 3rem 2rem; }

/* Booking list */
.bookings-list { display: flex; flex-direction: column; }
.booking-card {
  display: flex; align-items: center; justify-content: space-between;
  padding: 1rem 1.4rem; border-bottom: 1px solid #F9F9F9;
  transition: background 0.1s;
}
.booking-card:last-child { border-bottom: none; }
.booking-card:hover { background: #FAFAFA; }
.booking-left { display: flex; align-items: center; gap: 12px; }
.booking-avatar {
  width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 600;
}
.booking-client { font-size: 13px; font-weight: 600; color: #1a1a1a; }
.booking-meta-row { display: flex; align-items: center; gap: 10px; margin-top: 3px; flex-wrap: wrap; }
.booking-date, .booking-city { display: flex; align-items: center; gap: 3px; font-size: 11px; color: #9CA3AF; }
.booking-price { font-size: 11px; font-weight: 600; color: #5D4037; background: #F5F0EE; padding: 1px 7px; border-radius: 99px; }
.booking-note { font-size: 11px; color: #6B7280; margin-top: 4px; font-style: italic; }
.booking-right { display: flex; align-items: center; gap: 10px; }

/* Badge */
.badge { font-size: 11px; font-weight: 500; padding: 3px 10px; border-radius: 99px; }
.badge-pending  { background: #FFF8E1; color: #B45309; }
.badge-accepted { background: #E8F5E9; color: #2E7D32; }
.badge-rejected { background: #FFEBEE; color: #C62828; }

/* Action buttons */
.booking-actions { display: flex; gap: 6px; }
.action-btn {
  display: flex; align-items: center; gap: 4px;
  font-size: 11px; font-weight: 500;
  border: none; border-radius: 7px; padding: 5px 10px;
  cursor: pointer; font-family: inherit; transition: opacity 0.15s;
}
.action-btn:hover { opacity: 0.85; }
.accept-btn { background: #E8F5E9; color: #2E7D32; }
.reject-btn { background: #FFEBEE; color: #C62828; }

/* ── Hero ── */
.hero {
  background: linear-gradient(135deg, #4E342E 0%, #6D4C41 50%, #8D6E63 100%);
  padding: 3.5rem 2rem 3rem; position: relative; overflow: hidden;
}
.hero::before {
  content: ''; position: absolute; inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.hero-inner { max-width: 680px; margin: 0 auto; text-align: center; position: relative; }
.hero-title { font-size: 28px; font-weight: 600; color: #FFF8F6; margin-bottom: 8px; letter-spacing: -0.5px; line-height: 1.3; }
.hero-sub { font-size: 13px; color: #BCAAA4; margin-bottom: 2rem; letter-spacing: 0.02em; }

.search-wrap {
  display: flex; background: #fff; border-radius: 12px; overflow: hidden;
  box-shadow: 0 8px 32px rgba(0,0,0,0.15); max-width: 580px; margin: 0 auto;
}
.search-icon { display: flex; align-items: center; padding: 0 12px 0 16px; color: #9CA3AF; }
.search-input { flex: 1; padding: 13px 0; border: none; outline: none; font-size: 14px; font-family: inherit; color: #1a1a1a; }
.city-select { padding: 13px 16px; border: none; border-left: 1px solid #F3F4F6; outline: none; background: #FAFAFA; font-size: 13px; font-family: inherit; color: #374151; cursor: pointer; min-width: 140px; }

/* ── Filters ── */
.filters-bar { background: #fff; border-bottom: 1px solid rgba(0,0,0,0.06); padding: 10px 2rem; position: sticky; top: 58px; z-index: 99; }
.filters-inner { max-width: 1000px; margin: 0 auto; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.filter-label { font-size: 12px; color: #9CA3AF; font-weight: 500; white-space: nowrap; }
.pills { display: flex; gap: 6px; flex-wrap: wrap; flex: 1; }
.pill { font-size: 12px; font-weight: 500; padding: 5px 14px; border-radius: 99px; border: 1px solid #E5E7EB; background: #fff; color: #6B7280; cursor: pointer; transition: all 0.15s; font-family: inherit; }
.pill:hover { border-color: #8D6E63; color: #5D4037; }
.pill.active { background: #5D4037; color: #fff; border-color: #5D4037; }
.clear-btn { display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 500; color: #6B7280; background: none; border: 1px solid #E5E7EB; border-radius: 99px; padding: 5px 12px; cursor: pointer; font-family: inherit; transition: all 0.15s; white-space: nowrap; }
.clear-btn:hover { color: #993C1D; border-color: #993C1D; }

/* ── Main ── */
.main { max-width: 1000px; margin: 0 auto; padding: 1.5rem 2rem 3rem; }
.results-header { margin-bottom: 1.2rem; }
.results-count { font-size: 13px; color: #6B7280; }
.results-count strong { color: #1a1a1a; font-weight: 600; }

/* ── Grid ── */
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px; }
.card { background: #fff; border: 1px solid rgba(0,0,0,0.07); border-radius: 14px; padding: 1.1rem 1.2rem 1rem; transition: transform 0.15s, box-shadow 0.15s; }
.card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
.card.skeleton { height: 200px; background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 50%, #f0f0f0 75%); background-size: 200% 100%; animation: shimmer 1.2s infinite; }
@keyframes shimmer { 0% { background-position: 200% 0 } 100% { background-position: -200% 0 } }

.card-head { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.avatar { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; flex-shrink: 0; }
.card-meta { flex: 1; min-width: 0; }
.card-name { font-size: 14px; font-weight: 600; color: #1a1a1a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.card-location { display: flex; align-items: center; gap: 3px; font-size: 11px; color: #9CA3AF; margin-top: 2px; }
.avail-badge { font-size: 10px; font-weight: 500; color: #3B6D11; background: #EAF3DE; padding: 3px 8px; border-radius: 99px; white-space: nowrap; }

.stars-row { display: flex; align-items: center; gap: 4px; margin-bottom: 8px; }
.stars { display: flex; gap: 1px; }
.rating-val { font-size: 12px; font-weight: 600; color: #1a1a1a; }
.rating-count { font-size: 11px; color: #9CA3AF; }

.cats { display: flex; gap: 5px; flex-wrap: wrap; margin-bottom: 8px; }
.cat { font-size: 11px; padding: 3px 9px; border-radius: 6px; background: #F5F0EE; color: #6D4C41; font-weight: 500; }
.cat-more { background: #F3F4F6; color: #9CA3AF; }

.card-bio { font-size: 12px; color: #6B7280; line-height: 1.5; margin-bottom: 10px; }

.card-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #F3F4F6; padding-top: 10px; margin-top: 4px; }
.price-wrap { display: flex; flex-direction: column; }
.price-label { font-size: 10px; color: #9CA3AF; }
.price-val { font-size: 15px; font-weight: 600; color: #1a1a1a; }

.book-btn { display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; background: #4E342E; color: #FFF8F6; border: none; border-radius: 9px; padding: 8px 14px; cursor: pointer; text-decoration: none; font-family: inherit; transition: background 0.15s; }
.book-btn:hover { background: #3E2723; }

/* ── Empty ── */
.empty { text-align: center; padding: 4rem 2rem; }
.empty-icon { font-size: 40px; margin-bottom: 12px; }
.empty-title { font-size: 16px; font-weight: 600; color: #1a1a1a; margin-bottom: 6px; }
.empty-sub { font-size: 13px; color: #9CA3AF; margin-bottom: 1.5rem; }
.empty-btn { font-size: 13px; font-weight: 500; background: #5D4037; color: #fff; border: none; border-radius: 8px; padding: 8px 20px; cursor: pointer; font-family: inherit; }

/* ── Responsive ── */
@media (max-width: 640px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .dash-header-inner { flex-direction: column; align-items: flex-start; gap: 12px; }
  .booking-card { flex-direction: column; align-items: flex-start; gap: 10px; }
}
</style>