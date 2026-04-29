<template>
  <div class="container" v-if="artisan" style="padding: 20px; max-width: 800px; margin: auto;">
    <div class="header">
      <h1>{{ artisan.name }}</h1>
      <p style="color: #666;">{{ artisan.email }}</p>
      <div class="badges">
        <span v-for="cat in artisan.categories" :key="cat.id" class="badge">
          {{ cat.name }}
        </span>
      </div>
    </div>

    <hr />

    <div class="booking-section" style="background: #f9f9f9; padding: 20px; border-radius: 8px;">
      <h2>Réserver cet artisan</h2>
      
      <div class="form-group" style="margin-bottom: 10px;">
        <label>Date et Heure:</label><br>
        <input type="datetime-local" v-model="bookingDate" class="form-control" />
      </div>

      <div class="form-group" style="margin-bottom: 10px;">
        <label>Description du problème:</label><br>
        <textarea v-model="description" placeholder="Ex: Fuite d'eau dans la cuisine..." class="form-control"></textarea>
      </div>

      <button @click="book" class="btn-book">Book now 🔥</button>
    </div>

    <hr />

    <div class="reviews-section">
      <h2>Avis Clients</h2>
      <div v-if="reviews.length === 0" style="color: #999;">Aucun avis pour le moment.</div>
      <div v-for="rev in reviews" :key="rev.id" class="review-card">
        <span style="color: #f1c40f;">{{ "⭐".repeat(rev.rating) }}</span>
        <p>{{ rev.comment }}</p>
      </div>
    </div>
  </div>

  <div v-else class="loader">Chargement...</div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import axios from "axios"
import { useRoute } from "vue-router"

const route = useRoute()
const artisan = ref(null)
const reviews = ref([])

const bookingDate = ref("")
const description = ref("")

const loadArtisan = async () => {
  try {
    const res = await axios.get(`http://127.0.0.1:8000/api/artisans/${route.params.id}`)
    artisan.value = res.data
  } catch (e) {
    console.error("Artisan non trouvé")
  }
}

const loadReviews = async () => {
  try {
    const res = await axios.get(`http://127.0.0.1:8000/api/artisans/${route.params.id}/reviews`)
    reviews.value = res.data
  } catch (e) {
    console.log("Reviews not found")
  }
}

onMounted(() => {
  loadArtisan()
  loadReviews()
})

const book = async () => {
  const token = localStorage.getItem("token");
  console.log("Token checking:", token); 

  if (!token) {
    alert("Ma kanch token! Dir login ❌");
    return;
  }

  try {
    const response = await axios.post(
      "http://127.0.0.1:8000/api/bookings",
      {
        artisan_id: route.params.id,
        booking_date: bookingDate.value,
        description: description.value,
      },
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
      }
    );

    alert("Booking sent successfully! 🚀");
    bookingDate.value = "";
    description.value = "";

  } catch (e) {
    if (e.response && e.response.status === 401) {
       alert("Session expired or you are not logged in ❌");
    } else if (e.response && e.response.status === 422) {
       alert("Validation Error: " + JSON.stringify(e.response.data.errors));
    } else {
       alert("System Error: " + (e.response?.data?.message || "Check Laravel Logs"));
    }
    console.error("Error full details:", e.response?.data);
  }
};
</script>

<style scoped>
.badge {
  background: #5D4037;
  color: white;
  padding: 4px 10px;
  border-radius: 15px;
  margin-right: 5px;
  font-size: 12px;
}
.form-control {
  width: 100%;
  padding: 10px;
  margin-top: 5px;
  border: 1px solid #ccc;
  border-radius: 4px;
}
.btn-book {
  background: #5D4037;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-weight: bold;
  margin-top: 10px;
}
</style>