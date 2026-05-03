<script setup>
import { ref, onMounted } from "vue"
import axios from "axios"
import { useRoute, useRouter } from "vue-router"

const route = useRoute()
const router = useRouter()

const artisan = ref(null)
const reviews = ref([])
const bookingDate = ref("")
const description = ref("")
const price = ref("")
const isBooking = ref(false)
const bookingSuccess = ref(false)
const errorMsg = ref("")

const getInitials = (name) => {
  if (!name) return "?"
  return name.split(" ").map((n) => n[0]).join("").toUpperCase().slice(0, 2)
}

const avgRating = () => {
  if (!reviews.value.length) return 0
  return (reviews.value.reduce((s, r) => s + r.rating, 0) / reviews.value.length).toFixed(1)
}

const loadArtisan = async () => {
  try {
    const res = await axios.get(`http://127.0.0.1:8000/api/artisans/${route.params.id}`)
    artisan.value = res.data
  } catch {
    console.error("Artisan non trouvé")
  }
}

const loadReviews = async () => {
  try {
    const res = await axios.get(`http://127.0.0.1:8000/api/artisans/${route.params.id}/reviews`)
    reviews.value = res.data
  } catch {
    console.error("Reviews not found")
  }
}

const book = async () => {
  errorMsg.value = ""
  const token = localStorage.getItem("token")

  if (!token) {
    router.push("/login")
    return
  }

  if (!bookingDate.value) {
    errorMsg.value = "Veuillez choisir une date."
    return
  }

  if (!price.value || isNaN(price.value) || Number(price.value) <= 0) {
    errorMsg.value = "Veuillez entrer un prix valide."
    return
  }

  isBooking.value = true
  try {
    await axios.post(
      "http://127.0.0.1:8000/api/bookings",
      {
        artisan_id: route.params.id,
        booking_date: bookingDate.value,
        description: description.value,
        price: price.value,
      },
      {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: "application/json",
          "Content-Type": "application/json",
        },
      }
    )

    bookingSuccess.value = true
    bookingDate.value = ""
    description.value = ""
    price.value = ""

    setTimeout(() => (bookingSuccess.value = false), 4000)
  } catch (e) {
    if (e.response?.status === 401) {
      errorMsg.value = "Session expirée — reconnectez-vous."
    } else if (e.response?.status === 422) {
      const first = Object.values(e.response.data.errors)?.[0]?.[0]
      errorMsg.value = first ?? "Données invalides."
    } else {
      errorMsg.value = "Erreur serveur. Réessayez."
    }
  } finally {
    isBooking.value = false
  }
}

onMounted(() => {
  loadArtisan()
  loadReviews()
})
</script>

<template>
  <div class="page">

    <!-- Navbar -->
    <nav class="navbar">
      <div class="nav-left">
        <button class="back-btn" @click="router.back()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
          </svg>
          Retour
        </button>
      </div>
      <span class="nav-logo">Le Moqufe</span>
      <div style="width: 80px"></div>
    </nav>

    <!-- Loading -->
    <div v-if="!artisan" class="loading">
      <div class="spinner"></div>
      <span>Chargement...</span>
    </div>

    <div v-else class="content">

      <!-- Left col -->
      <div class="left-col">

        <!-- Artisan card -->
        <div class="artisan-card">
          <div class="artisan-top">
            <div class="avatar-lg">{{ getInitials(artisan.name) }}</div>
            <div>
              <h1 class="artisan-name">{{ artisan.name }}</h1>
              <div class="artisan-location">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                </svg>
                {{ artisan.city ?? "Maroc" }}
              </div>
            </div>
            <span class="avail-badge">● Disponible</span>
          </div>

          <!-- Rating global -->
          <div class="rating-row" v-if="reviews.length">
            <div class="stars-big">
              <svg v-for="i in 5" :key="i" width="16" height="16" viewBox="0 0 24 24">
                <polygon
                  :fill="i <= Math.round(avgRating()) ? '#F59E0B' : '#E5E7EB'"
                  points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"
                />
              </svg>
            </div>
            <span class="rating-num">{{ avgRating() }}</span>
            <span class="rating-cnt">{{ reviews.length }} avis</span>
          </div>

          <!-- Categories -->
          <div class="cats" v-if="artisan.categories?.length">
            <span class="cat" v-for="cat in artisan.categories" :key="cat.id">{{ cat.name }}</span>
          </div>

          <!-- Bio -->
          <p class="bio" v-if="artisan.bio">{{ artisan.bio }}</p>

          <!-- Price -->
          <div class="price-banner" v-if="artisan.price">
            <span class="price-label">Tarif indicatif</span>
            <span class="price-val">{{ artisan.price }} MAD</span>
          </div>
        </div>

        <!-- Reviews -->
        <div class="reviews-card">
          <h2 class="section-title">
            Avis clients
            <span class="count-badge">{{ reviews.length }}</span>
          </h2>

          <div v-if="reviews.length === 0" class="no-reviews">
            <span>💬</span> Aucun avis pour le moment.
          </div>

          <div v-else class="reviews-list">
            <div class="review" v-for="rev in reviews" :key="rev.id">
              <div class="review-top">
                <div class="reviewer-avatar">{{ getInitials(rev.client?.name ?? 'CL') }}</div>
                <div>
                  <div class="reviewer-name">{{ rev.client?.name ?? "Client" }}</div>
                  <div class="review-stars">
                    <svg v-for="i in 5" :key="i" width="11" height="11" viewBox="0 0 24 24">
                      <polygon
                        :fill="i <= rev.rating ? '#F59E0B' : '#E5E7EB'"
                        points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"
                      />
                    </svg>
                  </div>
                </div>
              </div>
              <p class="review-comment">{{ rev.comment }}</p>
            </div>
          </div>
        </div>

      </div>

      <!-- Right col — Booking form -->
      <div class="right-col">
        <div class="booking-card">
          <h2 class="booking-title">Réserver cet artisan</h2>

          <!-- Success -->
          <div v-if="bookingSuccess" class="success-msg">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M20 6 9 17l-5-5"/>
            </svg>
            Demande envoyée avec succès !
          </div>

          <!-- Error -->
          <div v-if="errorMsg" class="error-msg">{{ errorMsg }}</div>

          <div class="form-group">
            <label class="form-label">Date et heure <span class="req">*</span></label>
            <input type="datetime-local" v-model="bookingDate" class="form-input" />
          </div>

          <div class="form-group">
            <label class="form-label">Budget estimé (MAD) <span class="req">*</span></label>
            <div class="input-prefix-wrap">
              <span class="input-prefix">MAD</span>
              <input
                type="number"
                v-model="price"
                class="form-input input-with-prefix"
                placeholder="150"
                min="0"
              />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Description du problème</label>
            <textarea
              v-model="description"
              class="form-input form-textarea"
              placeholder="Ex: Fuite d'eau dans la cuisine, besoin d'intervention urgente..."
              rows="4"
            />
          </div>

          <button class="btn-book" @click="book" :disabled="isBooking">
            <span v-if="isBooking" class="spinner-sm"></span>
            <span v-else>
              Envoyer la demande
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M5 12h14M12 5l7 7-7 7"/>
              </svg>
            </span>
          </button>

          <p class="booking-note">
            L'artisan recevra votre demande et vous confirmera sa disponibilité.
          </p>
        </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600&display=swap');
* { box-sizing: border-box; }

.page { background: #F7F5F2; min-height: 100vh; font-family: 'Sora', sans-serif; }

/* Navbar */
.navbar {
  background: rgba(255,255,255,0.95); backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(0,0,0,0.06);
  padding: 0 2rem; height: 58px;
  display: flex; align-items: center; justify-content: space-between;
  position: sticky; top: 0; z-index: 100;
}
.nav-logo { font-size: 16px; font-weight: 600; color: #3E2723; }
.back-btn {
  display: flex; align-items: center; gap: 6px;
  font-size: 13px; color: #6B7280; background: none;
  border: none; cursor: pointer; font-family: inherit;
  transition: color 0.15s;
}
.back-btn:hover { color: #3E2723; }

/* Loading */
.loading {
  display: flex; flex-direction: column; align-items: center;
  gap: 12px; padding: 5rem; color: #9CA3AF; font-size: 14px;
}
.spinner {
  width: 28px; height: 28px; border: 3px solid #EFEBE9;
  border-top-color: #5D4037; border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Layout */
.content {
  max-width: 960px; margin: 0 auto;
  padding: 2rem;
  display: grid; grid-template-columns: 1fr 380px;
  gap: 20px; align-items: start;
}

/* Cards */
.artisan-card, .reviews-card, .booking-card {
  background: #fff; border: 1px solid rgba(0,0,0,0.07);
  border-radius: 16px; padding: 1.5rem;
}
.artisan-card { margin-bottom: 16px; }

/* Artisan top */
.artisan-top { display: flex; align-items: center; gap: 14px; margin-bottom: 14px; }
.avatar-lg {
  width: 56px; height: 56px; border-radius: 14px;
  background: #EFEBE9; color: #5D4037;
  display: flex; align-items: center; justify-content: center;
  font-size: 18px; font-weight: 600; flex-shrink: 0;
}
.artisan-name { font-size: 18px; font-weight: 600; color: #1a1a1a; margin-bottom: 4px; }
.artisan-location {
  display: flex; align-items: center; gap: 4px;
  font-size: 12px; color: #9CA3AF;
}
.avail-badge {
  margin-left: auto; font-size: 11px; font-weight: 500;
  color: #3B6D11; background: #EAF3DE;
  padding: 4px 10px; border-radius: 99px; white-space: nowrap;
}

/* Rating */
.rating-row { display: flex; align-items: center; gap: 6px; margin-bottom: 12px; }
.stars-big { display: flex; gap: 2px; }
.rating-num { font-size: 15px; font-weight: 600; color: #1a1a1a; }
.rating-cnt { font-size: 12px; color: #9CA3AF; }

/* Cats */
.cats { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 12px; }
.cat {
  font-size: 12px; padding: 4px 10px; border-radius: 8px;
  background: #F5F0EE; color: #6D4C41; font-weight: 500;
}

/* Bio */
.bio { font-size: 13px; color: #6B7280; line-height: 1.7; margin-bottom: 14px; }

/* Price banner */
.price-banner {
  display: flex; justify-content: space-between; align-items: center;
  background: #F7F5F2; border-radius: 10px; padding: 12px 16px;
}
.price-label { font-size: 12px; color: #9CA3AF; }
.price-val { font-size: 18px; font-weight: 600; color: #3E2723; }

/* Reviews */
.section-title {
  font-size: 15px; font-weight: 600; color: #1a1a1a;
  margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;
}
.count-badge {
  font-size: 11px; background: #F3F4F6; color: #6B7280;
  padding: 2px 8px; border-radius: 99px; font-weight: 500;
}
.no-reviews { font-size: 13px; color: #9CA3AF; text-align: center; padding: 1.5rem 0; }
.reviews-list { display: flex; flex-direction: column; gap: 14px; }
.review { padding-bottom: 14px; border-bottom: 1px solid #F3F4F6; }
.review:last-child { border-bottom: none; padding-bottom: 0; }
.review-top { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; }
.reviewer-avatar {
  width: 30px; height: 30px; border-radius: 8px;
  background: #EFEBE9; color: #5D4037;
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 600;
}
.reviewer-name { font-size: 13px; font-weight: 500; color: #1a1a1a; }
.review-stars { display: flex; gap: 2px; margin-top: 2px; }
.review-comment { font-size: 13px; color: #6B7280; line-height: 1.6; }

/* Booking form */
.booking-title { font-size: 16px; font-weight: 600; color: #1a1a1a; margin-bottom: 1.2rem; }
.form-group { margin-bottom: 1rem; }
.form-label { font-size: 13px; font-weight: 500; color: #374151; display: block; margin-bottom: 5px; }
.req { color: #993C1D; }
.form-input {
  width: 100%; padding: 10px 12px;
  border: 1px solid #E5E7EB; border-radius: 9px;
  font-size: 14px; font-family: inherit; background: #fff;
  outline: none; transition: border-color 0.15s;
}
.form-input:focus { border-color: #5D4037; }
.form-textarea { resize: vertical; }

.input-prefix-wrap { position: relative; }
.input-prefix {
  position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
  font-size: 12px; font-weight: 500; color: #9CA3AF;
}
.input-with-prefix { padding-left: 46px !important; }

.success-msg {
  display: flex; align-items: center; gap: 8px;
  font-size: 13px; color: #3B6D11; background: #EAF3DE;
  border-radius: 9px; padding: 10px 14px; margin-bottom: 1rem;
}
.error-msg {
  font-size: 13px; color: #993C1D; background: #FAECE7;
  border-radius: 9px; padding: 10px 14px; margin-bottom: 1rem;
}

.btn-book {
  width: 100%; padding: 12px;
  background: #4E342E; color: #FFF8F6;
  border: none; border-radius: 10px;
  font-size: 14px; font-weight: 600; font-family: inherit;
  cursor: pointer; display: flex; align-items: center;
  justify-content: center; gap: 8px;
  transition: background 0.15s;
}
.btn-book:hover:not(:disabled) { background: #3E2723; }
.btn-book:disabled { opacity: 0.5; cursor: not-allowed; }

.spinner-sm {
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: #fff; border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

.booking-note {
  font-size: 11px; color: #9CA3AF; text-align: center;
  margin-top: 10px; line-height: 1.6;
}

@media (max-width: 700px) {
  .content { grid-template-columns: 1fr; }
}
</style>