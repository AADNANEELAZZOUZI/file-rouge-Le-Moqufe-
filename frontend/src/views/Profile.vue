<script setup>
import { ref, computed, onMounted } from "vue"
import axios from "axios"
import { useRouter } from "vue-router"

const router = useRouter()

const user = ref(null)
const isLoading = ref(true)
const isSaving = ref(false)
const activeTab = ref("info") // "info" | "password"

const form = ref({
  name: "",
  email: "",
  city: "",
  phone: "",
  bio: "",
})

const passwordForm = ref({
  current_password: "",
  password: "",
  password_confirmation: "",
})

const showCurrentPw = ref(false)
const showNewPw = ref(false)

const successMsg = ref("")
const errorMsg = ref("")

const isArtisan = computed(() => user.value?.roles?.some(r => r.name === "artisan"))

const getInitials = (name) => {
  if (!name) return "?"
  return name.split(" ").map((n) => n[0]).join("").toUpperCase().slice(0, 2)
}

const loadProfile = async () => {
  isLoading.value = true
  try {
    const token = localStorage.getItem("token")
    const res = await axios.get("http://127.0.0.1:8000/api/profile", {
      headers: { Authorization: `Bearer ${token}` },
    })
    user.value = res.data
    form.value = {
      name:  res.data.name  ?? "",
      email: res.data.email ?? "",
      city:  res.data.city  ?? "",
      phone: res.data.phone ?? "",
      bio:   res.data.bio   ?? "",
    }
  } catch {
    router.push("/login")
  } finally {
    isLoading.value = false
  }
}

const saveInfo = async () => {
  successMsg.value = ""
  errorMsg.value = ""
  isSaving.value = true
  try {
    const token = localStorage.getItem("token")
    const res = await axios.put(
      "http://127.0.0.1:8000/api/update-profile",
      { name: form.value.name, email: form.value.email, city: form.value.city, phone: form.value.phone, bio: form.value.bio },
      { headers: { Authorization: `Bearer ${token}` } }
    )
    user.value = res.data.user
    localStorage.setItem("user", JSON.stringify(res.data.user))
    successMsg.value = "Profil mis à jour avec succès."
  } catch (e) {
    if (e.response?.status === 422) {
      const first = Object.values(e.response.data.errors)?.[0]?.[0]
      errorMsg.value = first ?? "Données invalides."
    } else {
      errorMsg.value = "Erreur serveur. Réessayez."
    }
  } finally {
    isSaving.value = false
  }
}

const savePassword = async () => {
  successMsg.value = ""
  errorMsg.value = ""

  if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
    errorMsg.value = "Les mots de passe ne correspondent pas."
    return
  }

  isSaving.value = true
  try {
    const token = localStorage.getItem("token")
    await axios.put(
      "http://127.0.0.1:8000/api/update-profile",
      {
        current_password:      passwordForm.value.current_password,
        password:              passwordForm.value.password,
        password_confirmation: passwordForm.value.password_confirmation,
      },
      { headers: { Authorization: `Bearer ${token}` } }
    )
    successMsg.value = "Mot de passe changé avec succès."
    passwordForm.value = { current_password: "", password: "", password_confirmation: "" }
  } catch (e) {
    if (e.response?.status === 422) {
      errorMsg.value = e.response.data.message ?? "Mot de passe actuel incorrect."
    } else {
      errorMsg.value = "Erreur serveur. Réessayez."
    }
  } finally {
    isSaving.value = false
  }
}

const logout = () => {
  localStorage.removeItem("token")
  localStorage.removeItem("user")
  router.push("/login")
}

onMounted(loadProfile)
</script>

<template>
  <div class="page">

    <!-- Navbar -->
    <nav class="navbar">
      <div class="nav-left">
        <button class="back-btn" @click="router.push('/')">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
          </svg>
          Accueil
        </button>
      </div>
      <span class="nav-logo">Le Moqufe</span>
      <button class="logout-btn" @click="logout">Déconnexion</button>
    </nav>

    <!-- Loading -->
    <div v-if="isLoading" class="loading">
      <div class="spinner"></div>
    </div>

    <div v-else class="content">

      <!-- Left — Avatar + infos fixes -->
      <div class="sidebar">
        <div class="avatar-card">
          <div class="avatar-circle">{{ getInitials(user.name) }}</div>
          <div class="avatar-name">{{ user.name }}</div>
          <div class="avatar-email">{{ user.email }}</div>

          <div class="role-badge" :class="isArtisan ? 'role-artisan' : 'role-client'">
            {{ isArtisan ? '🔧 Artisan' : '👤 Client' }}
          </div>

          <div class="sidebar-meta" v-if="user.city">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
            </svg>
            {{ user.city }}
          </div>

          <div class="sidebar-meta" v-if="user.phone">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 17z"/>
            </svg>
            {{ user.phone }}
          </div>

          <!-- Categories artisan -->
          <div class="sidebar-cats" v-if="isArtisan && user.categories?.length">
            <div class="sidebar-cats-label">Spécialités</div>
            <div class="cats">
              <span class="cat" v-for="cat in user.categories" :key="cat.id">{{ cat.name }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right — Edit form -->
      <div class="main-col">

        <!-- Tabs -->
        <div class="tabs">
          <button class="tab" :class="{ active: activeTab === 'info' }" @click="activeTab = 'info'; successMsg = ''; errorMsg = ''">
            Informations
          </button>
          <button class="tab" :class="{ active: activeTab === 'password' }" @click="activeTab = 'password'; successMsg = ''; errorMsg = ''">
            Mot de passe
          </button>
        </div>

        <div class="form-card">

          <!-- Success / Error -->
          <div v-if="successMsg" class="success-msg">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M20 6 9 17l-5-5"/>
            </svg>
            {{ successMsg }}
          </div>
          <div v-if="errorMsg" class="error-msg">{{ errorMsg }}</div>

          <!-- Tab: Informations -->
          <template v-if="activeTab === 'info'">
            <div class="row-2">
              <div class="form-group">
                <label class="form-label">Nom complet</label>
                <input v-model="form.name" type="text" class="form-input" placeholder="Youssef Benali" />
              </div>
              <div class="form-group">
                <label class="form-label">Ville</label>
                <input v-model="form.city" type="text" class="form-input" placeholder="Marrakech" />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Adresse email</label>
              <input v-model="form.email" type="email" class="form-input" placeholder="nom@exemple.com" />
            </div>

            <!-- Artisan only -->
            <template v-if="isArtisan">
              <div class="form-group">
                <label class="form-label">Téléphone</label>
                <input v-model="form.phone" type="tel" class="form-input" placeholder="+212 6XX XXX XXX" />
              </div>
              <div class="form-group">
                <label class="form-label">Description de vos services</label>
                <textarea v-model="form.bio" class="form-input form-textarea" rows="4"
                  placeholder="Ex: Plombier avec 5 ans d'expérience..." />
              </div>
            </template>

            <button class="btn-save" @click="saveInfo" :disabled="isSaving">
              <span v-if="isSaving" class="spinner-sm"></span>
              <span v-else>Enregistrer les modifications</span>
            </button>
          </template>

          <!-- Tab: Password -->
          <template v-if="activeTab === 'password'">
            <div class="form-group">
              <label class="form-label">Mot de passe actuel</label>
              <div class="pw-wrap">
                <input
                  v-model="passwordForm.current_password"
                  :type="showCurrentPw ? 'text' : 'password'"
                  class="form-input" placeholder="••••••••"
                />
                <button type="button" class="pw-toggle" @click="showCurrentPw = !showCurrentPw">
                  {{ showCurrentPw ? 'Masquer' : 'Afficher' }}
                </button>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Nouveau mot de passe</label>
              <div class="pw-wrap">
                <input
                  v-model="passwordForm.password"
                  :type="showNewPw ? 'text' : 'password'"
                  class="form-input" placeholder="••••••••"
                />
                <button type="button" class="pw-toggle" @click="showNewPw = !showNewPw">
                  {{ showNewPw ? 'Masquer' : 'Afficher' }}
                </button>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Confirmer le nouveau mot de passe</label>
              <input
                v-model="passwordForm.password_confirmation"
                type="password" class="form-input" placeholder="••••••••"
              />
            </div>

            <button class="btn-save" @click="savePassword" :disabled="isSaving">
              <span v-if="isSaving" class="spinner-sm"></span>
              <span v-else>Changer le mot de passe</span>
            </button>
          </template>

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
}
.back-btn:hover { color: #3E2723; }
.logout-btn {
  font-size: 12px; font-weight: 500; color: #993C1D;
  background: #FAECE7; border: none; border-radius: 8px;
  padding: 6px 14px; cursor: pointer; font-family: inherit;
}

/* Loading */
.loading { display: flex; justify-content: center; padding: 5rem; }
.spinner {
  width: 28px; height: 28px; border: 3px solid #EFEBE9;
  border-top-color: #5D4037; border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Layout */
.content {
  max-width: 900px; margin: 2rem auto;
  padding: 0 2rem;
  display: grid; grid-template-columns: 260px 1fr;
  gap: 20px; align-items: start;
}

/* Sidebar */
.avatar-card {
  background: #fff; border: 1px solid rgba(0,0,0,0.07);
  border-radius: 16px; padding: 1.5rem;
  display: flex; flex-direction: column; align-items: center;
  text-align: center; gap: 8px;
}
.avatar-circle {
  width: 64px; height: 64px; border-radius: 16px;
  background: #EFEBE9; color: #5D4037;
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; font-weight: 600; margin-bottom: 4px;
}
.avatar-name { font-size: 15px; font-weight: 600; color: #1a1a1a; }
.avatar-email { font-size: 12px; color: #9CA3AF; }
.role-badge {
  font-size: 12px; font-weight: 500;
  padding: 4px 14px; border-radius: 99px; margin-top: 4px;
}
.role-artisan { background: #EFEBE9; color: #5D4037; }
.role-client { background: #E6F1FB; color: #0C447C; }
.sidebar-meta {
  display: flex; align-items: center; gap: 5px;
  font-size: 12px; color: #6B7280; margin-top: 2px;
}
.sidebar-cats { width: 100%; margin-top: 8px; text-align: left; }
.sidebar-cats-label { font-size: 11px; color: #9CA3AF; margin-bottom: 6px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.04em; }
.cats { display: flex; gap: 5px; flex-wrap: wrap; }
.cat { font-size: 11px; padding: 3px 8px; border-radius: 6px; background: #F5F0EE; color: #6D4C41; font-weight: 500; }

/* Main col */
.tabs {
  display: flex; gap: 4px; margin-bottom: 14px;
  background: #fff; border: 1px solid rgba(0,0,0,0.07);
  border-radius: 12px; padding: 5px;
}
.tab {
  flex: 1; padding: 8px; border-radius: 8px;
  font-size: 13px; font-weight: 500; font-family: inherit;
  border: none; background: none; cursor: pointer;
  color: #6B7280; transition: all 0.15s;
}
.tab.active { background: #5D4037; color: #fff; }

.form-card {
  background: #fff; border: 1px solid rgba(0,0,0,0.07);
  border-radius: 16px; padding: 1.5rem;
}

/* Messages */
.success-msg {
  display: flex; align-items: center; gap: 8px;
  font-size: 13px; color: #3B6D11; background: #EAF3DE;
  border-radius: 9px; padding: 10px 14px; margin-bottom: 1rem;
}
.error-msg {
  font-size: 13px; color: #993C1D; background: #FAECE7;
  border-radius: 9px; padding: 10px 14px; margin-bottom: 1rem;
}

/* Form */
.row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-group { margin-bottom: 1rem; }
.form-label { font-size: 13px; font-weight: 500; color: #374151; display: block; margin-bottom: 5px; }
.form-input {
  width: 100%; padding: 10px 12px;
  border: 1px solid #E5E7EB; border-radius: 9px;
  font-size: 14px; font-family: inherit; background: #fff;
  outline: none; transition: border-color 0.15s; box-sizing: border-box;
}
.form-input:focus { border-color: #5D4037; }
.form-textarea { resize: vertical; }
.pw-wrap { position: relative; }
.pw-toggle {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  font-size: 12px; color: #5D4037; background: none;
  border: none; cursor: pointer; font-weight: 500; font-family: inherit;
}

.btn-save {
  width: 100%; padding: 11px;
  background: #4E342E; color: #FFF8F6;
  border: none; border-radius: 10px;
  font-size: 14px; font-weight: 600; font-family: inherit;
  cursor: pointer; margin-top: 4px;
  display: flex; align-items: center; justify-content: center;
  transition: background 0.15s;
}
.btn-save:hover:not(:disabled) { background: #3E2723; }
.btn-save:disabled { opacity: 0.5; cursor: not-allowed; }

.spinner-sm {
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: #fff; border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@media (max-width: 640px) {
  .content { grid-template-columns: 1fr; }
  .row-2 { grid-template-columns: 1fr; }
}
</style>