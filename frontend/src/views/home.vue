<script setup>
import { ref, onMounted } from "vue"
import axios from "axios"

const artisans = ref([])
const categories = ref([])
const search = ref("")
const selectedCategory = ref("")

const getInitials = (name) => {
  if (!name) return "?"
  return name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .toUpperCase()
    .slice(0, 2)
}

const avatarColors = ["av-brown", "av-teal", "av-blue", "av-purple", "av-coral"]
const getAvatarColor = (id) => avatarColors[id % avatarColors.length]

const loadArtisans = async () => {
  let url = "http://127.0.0.1:8000/api/artisans?"
  if (search.value) url += `search=${search.value}&`
  if (selectedCategory.value) url += `category_id=${selectedCategory.value}`
  const res = await axios.get(url)
  artisans.value = res.data
}

const loadCategories = async () => {
  const res = await axios.get("http://127.0.0.1:8000/api/categories")
  categories.value = res.data.data
}

const selectCategory = (id) => {
  selectedCategory.value = id
  loadArtisans()
}

onMounted(() => {
  loadArtisans()
  loadCategories()
})
</script>

<template>
  <div class="page">

    <nav class="navbar">
      <span class="nav-logo">Le Moqufe</span>
      <div class="nav-links">
        <a class="nav-link" href="#">Accueil</a>
        <a class="nav-link" href="#">Comment ça marche</a>
        <router-link to="/login" class="nav-btn">Connexion</router-link>
      </div>
    </nav>

    <div class="hero">
      <h1 class="hero-title">Trouvez un artisan fiable près de chez vous</h1>
      <p class="hero-sub">Plombiers, électriciens, jardiniers et plus — au Maroc</p>

      <div class="search-bar">
        <input
          v-model="search"
          @input="loadArtisans"
          class="search-input"
          placeholder="Rechercher un artisan..."
        />
        <select
          v-model="selectedCategory"
          @change="loadArtisans"
          class="search-select"
        >
          <option value="">Toutes catégories</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
        <button class="search-btn" @click="loadArtisans">Rechercher</button>
      </div>

      <div class="cats-row">
        <span
          class="cat-pill"
          :class="{ active: selectedCategory === '' }"
          @click="selectCategory('')"
        >Tous</span>
        <span
          v-for="cat in categories"
          :key="cat.id"
          class="cat-pill"
          :class="{ active: selectedCategory === cat.id }"
          @click="selectCategory(cat.id)"
        >{{ cat.name }}</span>
      </div>
    </div>

    <div class="main">
      <div class="section-header">
        <span class="section-title">Artisans disponibles</span>
        <span class="result-count">{{ artisans.length }} résultat{{ artisans.length !== 1 ? 's' : '' }}</span>
      </div>

      <div v-if="artisans.length === 0" class="empty-state">
        Aucun artisan trouvé.
      </div>

      <div class="grid">
        <div class="card" v-for="artisan in artisans" :key="artisan.id">

          <div class="card-top">
            <div class="avatar" :class="getAvatarColor(artisan.id)">
              {{ getInitials(artisan.name) }}
            </div>
            <div>
              <div class="card-name">{{ artisan.name }}</div>
              <div class="card-city">{{ artisan.city ?? artisan.email }}</div>
            </div>
            <span v-if="artisan.available !== false" class="badge-avail">Disponible</span>
          </div>

          <div class="stars" v-if="artisan.rating">
            <template v-for="i in 5" :key="i">
              <svg class="star" viewBox="0 0 12 12">
                <polygon
                  :class="i <= Math.round(artisan.rating) ? 'star-fill' : 'star-empty'"
                  points="6,1 7.5,4.5 11,5 8.5,7.5 9,11 6,9.5 3,11 3.5,7.5 1,5 4.5,4.5"
                />
              </svg>
            </template>
            <span class="rating-text">{{ artisan.rating }} ({{ artisan.reviews_count ?? 0 }} avis)</span>
          </div>

          <div class="cats" v-if="artisan.categories?.length">
            <span
              class="cat-tag"
              v-for="cat in artisan.categories"
              :key="cat.id"
            >{{ cat.name }}</span>
          </div>

          <div class="card-footer">
            <div class="card-price" v-if="artisan.price">
              À partir de <span>{{ artisan.price }} MAD</span>
            </div>
            <div class="card-price" v-else>
              <span>–</span>
            </div>
            <router-link :to="`/artisans/${artisan.id}`" class="book-btn">
              Réserver
            </router-link>
          </div>

        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
.page {
  background: #F5F3F0;
  min-height: 100vh;
  font-family: inherit;
}

.navbar {
  background: #fff;
  border-bottom: 0.5px solid rgba(0,0,0,0.08);
  padding: 0 2rem;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.nav-logo {
  font-size: 17px;
  font-weight: 500;
  color: #5D4037;
  letter-spacing: -0.3px;
}
.nav-links {
  display: flex;
  gap: 20px;
  align-items: center;
}
.nav-link {
  font-size: 13px;
  color: #6B7280;
  text-decoration: none;
}
.nav-btn {
  font-size: 13px;
  font-weight: 500;
  background: #5D4037;
  color: #F5F0EE;
  border: none;
  border-radius: 8px;
  padding: 6px 14px;
  cursor: pointer;
  text-decoration: none;
}

.hero {
  background: #5D4037;
  padding: 3rem 2rem 2.5rem;
  text-align: center;
}
.hero-title {
  font-size: 26px;
  font-weight: 500;
  color: #F5F0EE;
  margin-bottom: 8px;
}
.hero-sub {
  font-size: 14px;
  color: #BCAAA4;
  margin-bottom: 1.8rem;
}

.search-bar {
  display: flex;
  gap: 8px;
  max-width: 640px;
  margin: 0 auto;
  flex-wrap: wrap;
  justify-content: center;
}
.search-input {
  flex: 2;
  min-width: 180px;
  padding: 10px 14px;
  border-radius: 8px;
  border: 0.5px solid #BCAAA4;
  background: #fff;
  font-size: 14px;
  color: #333;
  outline: none;
}
.search-input:focus {
  border-color: #8D6E63;
}
.search-select {
  flex: 1;
  min-width: 150px;
  padding: 10px 14px;
  border-radius: 8px;
  border: 0.5px solid #BCAAA4;
  background: #fff;
  font-size: 14px;
  color: #555;
  outline: none;
}
.search-btn {
  padding: 10px 20px;
  background: #fff;
  color: #5D4037;
  font-size: 14px;
  font-weight: 500;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}
.search-btn:hover {
  background: #EFEBE9;
}

.cats-row {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  justify-content: center;
  margin-top: 1.2rem;
}
.cat-pill {
  font-size: 12px;
  padding: 4px 14px;
  border-radius: 99px;
  background: rgba(255,255,255,0.12);
  color: #F5F0EE;
  border: 0.5px solid rgba(255,255,255,0.2);
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}
.cat-pill.active,
.cat-pill:hover {
  background: #fff;
  color: #5D4037;
}

.main {
  padding: 2rem;
  max-width: 1000px;
  margin: 0 auto;
}
.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
}
.section-title {
  font-size: 15px;
  font-weight: 500;
  color: #1a1a1a;
}
.result-count {
  font-size: 13px;
  color: #6B7280;
}
.empty-state {
  text-align: center;
  padding: 3rem;
  font-size: 14px;
  color: #9CA3AF;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 14px;
}

.card {
  background: #fff;
  border: 0.5px solid rgba(0,0,0,0.08);
  border-radius: 12px;
  padding: 1rem 1.1rem;
  cursor: pointer;
  transition: border-color 0.15s;
}
.card:hover {
  border-color: rgba(0,0,0,0.18);
}

.card-top {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 500;
  flex-shrink: 0;
}
.av-brown  { background: #EFEBE9; color: #5D4037; }
.av-teal   { background: #E1F5EE; color: #085041; }
.av-blue   { background: #E6F1FB; color: #0C447C; }
.av-purple { background: #EEEDFE; color: #3C3489; }
.av-coral  { background: #FAECE7; color: #993C1D; }

.card-name {
  font-size: 14px;
  font-weight: 500;
  color: #1a1a1a;
}
.card-city {
  font-size: 12px;
  color: #6B7280;
  margin-top: 1px;
}

.badge-avail {
  display: inline-block;
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 99px;
  background: #EAF3DE;
  color: #3B6D11;
  margin-left: auto;
  white-space: nowrap;
}

.stars {
  display: flex;
  align-items: center;
  gap: 2px;
  margin-bottom: 8px;
}
.star {
  width: 12px;
  height: 12px;
}
.star-fill  { fill: #EF9F27; }
.star-empty { fill: #E5E7EB; }
.rating-text {
  font-size: 12px;
  color: #6B7280;
  margin-left: 4px;
}

.cats {
  display: flex;
  gap: 5px;
  flex-wrap: wrap;
  margin-bottom: 10px;
}
.cat-tag {
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 99px;
  background: #EFEBE9;
  color: #712B13;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 0.5px solid rgba(0,0,0,0.06);
  padding-top: 10px;
  margin-top: 4px;
}
.card-price {
  font-size: 13px;
  color: #6B7280;
}
.card-price span {
  font-size: 15px;
  font-weight: 500;
  color: #1a1a1a;
}
.book-btn {
  font-size: 12px;
  font-weight: 500;
  background: #5D4037;
  color: #F5F0EE;
  border: none;
  border-radius: 8px;
  padding: 6px 14px;
  cursor: pointer;
  text-decoration: none;
  transition: background 0.15s;
}
.book-btn:hover {
  background: #4E342E;
}
</style> 