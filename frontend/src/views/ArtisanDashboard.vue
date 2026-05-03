<script setup>
import { ref, onMounted } from "vue"
import axios from "axios"

const bookings = ref([])
const isLoading = ref(false)

const token = localStorage.getItem("token")

const loadBookings = async () => {
  isLoading.value = true
  try {
    const res = await axios.get(
      "http://127.0.0.1:8000/api/artisan-bookings",
      { headers: { Authorization: `Bearer ${token}` } }
    )
    bookings.value = res.data
  } finally {
    isLoading.value = false
  }
}

const acceptBooking = async (id) => {
  await axios.put(
    `http://127.0.0.1:8000/api/bookings/${id}/accept`,
    {},
    { headers: { Authorization: `Bearer ${token}` } }
  )
  loadBookings()
}

const rejectBooking = async (id) => {
  await axios.put(
    `http://127.0.0.1:8000/api/bookings/${id}/reject`,
    {},
    { headers: { Authorization: `Bearer ${token}` } }
  )
  loadBookings()
}

onMounted(loadBookings)
</script>
<template>
  <div class="page">

    <nav class="navbar">
      <span class="nav-logo">Artisan Dashboard</span>
      <router-link to="/" class="nav-link">Home</router-link>
    </nav>

    <div class="container">
      <h1>Mes demandes de réservation</h1>

      <div v-if="isLoading">Loading...</div>

      <div v-else-if="bookings.length === 0">
        Aucune réservation pour le moment
      </div>

      <div v-else class="grid">
        <div class="card" v-for="b in bookings" :key="b.id">

          <h3>{{ b.user.name }}</h3>
          <p>{{ b.date }}</p>
          <p>{{ b.message }}</p>

          <div class="actions">
            <button class="accept" @click="acceptBooking(b.id)">Accepter</button>
            <button class="reject" @click="rejectBooking(b.id)">Refuser</button>
          </div>

        </div>
      </div>
    </div>

  </div>
</template>
<style scoped>
.container{max-width:900px;margin:auto;padding:40px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px}
.card{background:#fff;padding:20px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,.08)}
.actions{display:flex;gap:10px;margin-top:15px}
.accept{background:#16a34a;color:#fff;border:none;padding:8px 14px;border-radius:6px}
.reject{background:#dc2626;color:#fff;border:none;padding:8px 14px;border-radius:6px}
</style>