<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
 
const router = useRouter()
 
const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  city: '',
  role: '',
  phone: '',
  bio: '',
  categories: [],
})
 
const categories = ref([])
const isLoading = ref(false)
const showPassword = ref(false)
const errorMessage = ref('')
 
const loadCategories = async () => {
  try {
    const res = await axios.get('http://127.0.0.1:8000/api/categories')
    categories.value = res.data
  } catch {
  }
}
 
const toggleCategory = (id) => {
  const idx = form.value.categories.indexOf(id)
  if (idx === -1) {
    form.value.categories.push(id)
  } else {
    form.value.categories.splice(idx, 1)
  }
}
 
const isSelected = (id) => form.value.categories.includes(id)
 
const submit = async () => {
  errorMessage.value = ''
 
  if (!form.value.role) {
    errorMessage.value = 'Veuillez choisir un type de compte.'
    return
  }
 
  if (form.value.role === 'artisan' && form.value.categories.length === 0) {
    errorMessage.value = 'Veuillez choisir au moins une catégorie.'
    return
  }
 
  isLoading.value = true
  try {
    const res = await axios.post('http://127.0.0.1:8000/api/register', form.value)
 
    localStorage.setItem('token', res.data.access_token)
    localStorage.setItem('user', JSON.stringify(res.data.user))
 
    const role = res.data.user.roles?.[0]?.name
    if (role === 'artisan') {
      router.push('/artisan/dashboard')
    } else {
      router.push('/')
    }
  } catch (err) {
    if (err.response?.status === 422) {
      const errors = err.response.data.errors
      const firstError = Object.values(errors)?.[0]?.[0]
      errorMessage.value = firstError ?? 'Données invalides.'
    } else {
      errorMessage.value = 'Erreur serveur. Réessayez.'
    }
  } finally {
    isLoading.value = false
  }
}
 
onMounted(() => {
  loadCategories()
})
</script>
 
<template>
  <div class="page">
 
    <nav class="navbar">
      <router-link to="/" class="nav-logo">Le Moqufe</router-link>
      <div class="nav-links">
        <span class="nav-hint">Déjà inscrit ?</span>
        <router-link to="/login" class="nav-btn">Connexion</router-link>
      </div>
    </nav>
 
    <div class="wrapper">
 
      <div class="visual">
        <div class="visual-content">
          <h1 class="visual-title">Rejoignez Le Moqufe</h1>
          <p class="visual-sub">
            Trouvez un artisan fiable ou proposez vos services — en quelques minutes.
          </p>
          <div class="visual-steps">
            <div class="step"><span class="step-num">1</span><span>Choisissez votre type de compte</span></div>
            <div class="step"><span class="step-num">2</span><span>Remplissez vos informations</span></div>
            <div class="step"><span class="step-num">3</span><span>Commencez à utiliser la plateforme</span></div>
          </div>
        </div>
      </div>
 
      <div class="form-wrapper">
        <h2 class="form-title">Créer un compte</h2>
 
        <div class="role-selector">
          <button
            type="button" class="role-btn"
            :class="{ active: form.role === 'client' }"
            @click="form.role = 'client'; form.categories = []"
          >
            <span class="role-icon">👤</span>
            <span class="role-label">Client</span>
            <span class="role-desc">Je cherche un artisan</span>
          </button>
          <button
            type="button" class="role-btn"
            :class="{ active: form.role === 'artisan' }"
            @click="form.role = 'artisan'"
          >
            <span class="role-icon">🔧</span>
            <span class="role-label">Artisan</span>
            <span class="role-desc">Je propose mes services</span>
          </button>
        </div>
 
        <p v-if="errorMessage" class="error-msg">{{ errorMessage }}</p>
 
        <form @submit.prevent="submit">
 
          <div class="row-2">
            <div class="form-group">
              <label for="name" class="form-label">Nom complet</label>
              <input id="name" v-model="form.name" type="text" class="form-input" placeholder="Youssef Benali" required />
            </div>
            <div class="form-group">
              <label for="city" class="form-label">Ville</label>
              <input id="city" v-model="form.city" type="text" class="form-input" placeholder="Marrakech" />
            </div>
          </div>
 
          <div class="form-group">
            <label for="email" class="form-label">Adresse email</label>
            <input id="email" v-model="form.email" type="email" class="form-input" placeholder="nom@exemple.com" required />
          </div>
 
          <div class="form-group">
            <label for="password" class="form-label">Mot de passe</label>
            <div class="input-wrapper">
              <input
                id="password" v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="form-input" placeholder="••••••••" required
              />
              <button type="button" class="toggle-pw" @click="showPassword = !showPassword">
                {{ showPassword ? 'Masquer' : 'Afficher' }}
              </button>
            </div>
          </div>
 
          <div class="form-group">
            <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
            <input id="password_confirmation" v-model="form.password_confirmation" type="password" class="form-input" placeholder="••••••••" required />
          </div>
 
          <template v-if="form.role === 'artisan'">
            <div class="artisan-section">
              <span class="artisan-label">Informations artisan</span>
            </div>
 
            <div class="form-group">
              <label for="phone" class="form-label">Téléphone <span class="required">*</span></label>
              <input id="phone" v-model="form.phone" type="tel" class="form-input" placeholder="+212 6XX XXX XXX" required />
            </div>
 
            <div class="form-group">
              <label class="form-label">
                Spécialités <span class="required">*</span>
                <span class="label-hint">(plusieurs choix possibles)</span>
              </label>
 
              <div v-if="categories.length === 0" class="cats-empty">
                Chargement des catégories...
              </div>
 
              <div v-else class="cats-grid">
                <button
                  v-for="cat in categories"
                  :key="cat.id"
                  type="button"
                  class="cat-chip"
                  :class="{ selected: isSelected(cat.id) }"
                  @click="toggleCategory(cat.id)"
                >
                  <span class="cat-check" v-if="isSelected(cat.id)">✓</span>
                  {{ cat.name }}
                </button>
              </div>
 
              <p class="cats-count" v-if="form.categories.length > 0">
                {{ form.categories.length }} spécialité{{ form.categories.length > 1 ? 's' : '' }} sélectionnée{{ form.categories.length > 1 ? 's' : '' }}
              </p>
            </div>
 
            <div class="form-group">
              <label for="bio" class="form-label">Description de vos services</label>
              <textarea
                id="bio" v-model="form.bio"
                class="form-input form-textarea"
                placeholder="Ex: Plombier avec 5 ans d'expérience, disponible à Marrakech..."
                rows="3"
              />
            </div>
          </template>
 
          <button type="submit" class="btn-submit" :disabled="isLoading || !form.role">
            <span v-if="isLoading" class="spinner"></span>
            <span v-else>Créer mon compte</span>
          </button>
 
        </form>
      </div>
    </div>
 
  </div>
</template>
 
<style scoped>
.page { background: #F5F3F0; min-height: 100vh; }
 
.navbar {
  background: #fff;
  border-bottom: 0.5px solid rgba(0,0,0,0.08);
  padding: 0 2rem; height: 56px;
  display: flex; align-items: center; justify-content: space-between;
}
.nav-logo { font-size: 17px; font-weight: 500; color: #5D4037; text-decoration: none; }
.nav-links { display: flex; gap: 12px; align-items: center; }
.nav-hint { font-size: 13px; color: #6B7280; }
.nav-btn {
  font-size: 13px; font-weight: 500;
  background: #5D4037; color: #F5F0EE;
  border-radius: 8px; padding: 6px 14px; text-decoration: none;
}
 
.wrapper {
  display: flex; max-width: 920px;
  margin: 2rem auto; background: #fff;
  border-radius: 14px; border: 0.5px solid rgba(0,0,0,0.08);
  overflow: hidden; min-height: 580px;
}
 
.visual {
  flex: 1; background: #5D4037;
  display: flex; align-items: center; justify-content: center; padding: 2.5rem;
}
.visual-content { color: #F5F0EE; }
.visual-title { font-size: 22px; font-weight: 500; margin-bottom: 10px; }
.visual-sub { font-size: 13px; color: #BCAAA4; line-height: 1.7; margin-bottom: 2rem; }
.visual-steps { display: flex; flex-direction: column; gap: 14px; }
.step { display: flex; align-items: center; gap: 10px; font-size: 13px; color: #EFEBE9; }
.step-num {
  width: 24px; height: 24px; border-radius: 50%;
  background: rgba(255,255,255,0.18);
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; font-weight: 500; flex-shrink: 0;
}
 
.form-wrapper { flex: 1.2; padding: 2.5rem 2.5rem 2rem; overflow-y: auto; }
.form-title { font-size: 18px; font-weight: 500; color: #1a1a1a; margin-bottom: 1.5rem; }
 
.role-selector { display: flex; gap: 10px; margin-bottom: 1.5rem; }
.role-btn {
  flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px;
  padding: 14px 10px; border-radius: 10px;
  border: 1.5px solid rgba(0,0,0,0.1);
  background: #F9F8F7; cursor: pointer;
  transition: border-color 0.15s, background 0.15s;
}
.role-btn:hover { border-color: #8D6E63; background: #EFEBE9; }
.role-btn.active { border-color: #5D4037; background: #EFEBE9; }
.role-icon { font-size: 22px; }
.role-label { font-size: 14px; font-weight: 500; color: #1a1a1a; }
.role-desc { font-size: 11px; color: #6B7280; }
 
.error-msg {
  font-size: 13px; color: #993C1D;
  background: #FAECE7; border-radius: 8px;
  padding: 8px 12px; margin-bottom: 1rem;
}
 
.row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-group { margin-bottom: 1rem; }
.form-label { font-size: 13px; font-weight: 500; color: #374151; display: block; margin-bottom: 5px; }
.label-hint { font-size: 11px; font-weight: 400; color: #9CA3AF; margin-left: 4px; }
.required { color: #993C1D; }
.form-input {
  width: 100%; padding: 9px 12px;
  border: 0.5px solid #D1C4BC; border-radius: 8px;
  font-size: 14px; background: #fff; outline: none;
  box-sizing: border-box; transition: border-color 0.15s;
}
.form-input:focus { border-color: #5D4037; }
.form-textarea { resize: vertical; font-family: inherit; }
 
.input-wrapper { position: relative; }
.toggle-pw {
  position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
  font-size: 12px; color: #5D4037; background: none; border: none;
  cursor: pointer; font-weight: 500;
}
 
.artisan-section {
  margin: 1rem 0 0.8rem;
  padding-top: 1rem;
  border-top: 0.5px solid rgba(0,0,0,0.08);
}
.artisan-label {
  font-size: 12px; font-weight: 500; color: #6B7280;
  text-transform: uppercase; letter-spacing: 0.05em;
}
 
.cats-grid {
  display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px;
}
.cat-chip {
  display: flex; align-items: center; gap: 5px;
  padding: 6px 12px; border-radius: 99px;
  border: 1px solid #D1C4BC;
  background: #F9F8F7;
  font-size: 13px; color: #374151;
  cursor: pointer; transition: all 0.15s;
}
.cat-chip:hover { border-color: #8D6E63; background: #EFEBE9; }
.cat-chip.selected {
  border-color: #5D4037;
  background: #EFEBE9;
  color: #5D4037;
  font-weight: 500;
}
.cat-check { font-size: 11px; color: #5D4037; }
.cats-empty { font-size: 13px; color: #9CA3AF; padding: 8px 0; }
.cats-count { font-size: 12px; color: #5D4037; margin-top: 6px; font-weight: 500; }
 
.btn-submit {
  width: 100%; padding: 11px;
  background: #5D4037; color: #F5F0EE;
  border: none; border-radius: 8px;
  font-size: 14px; font-weight: 500; cursor: pointer;
  margin-top: 0.5rem;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: background 0.15s;
}
.btn-submit:hover:not(:disabled) { background: #4E342E; }
.btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }
 
.spinner {
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>