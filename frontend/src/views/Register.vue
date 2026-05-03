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

const categories  = ref([])
const isLoading   = ref(false)
const showPassword = ref(false)
const errorMessage = ref('')

const loadCategories = async () => {
  try {
    const res = await axios.get('http://127.0.0.1:8000/api/categories')
    categories.value = res.data
  } catch {}
}

const toggleCategory = (id) => {
  const idx = form.value.categories.indexOf(id)
  if (idx === -1) form.value.categories.push(id)
  else form.value.categories.splice(idx, 1)
}

const isSelected = (id) => form.value.categories.includes(id)

const submit = async () => {
  errorMessage.value = ''

  if (!form.value.role) {
    errorMessage.value = 'Veuillez choisir un type de compte.'
    return
  }
  if (form.value.role === 'artisan' && form.value.categories.length === 0) {
    errorMessage.value = 'Veuillez choisir au moins une spécialité.'
    return
  }

  isLoading.value = true
  try {
    const res = await axios.post('http://127.0.0.1:8000/api/register', form.value)

    localStorage.setItem('token', res.data.access_token)
    localStorage.setItem('user', JSON.stringify(res.data.user))

    const role = res.data.user.roles?.[0]?.name
    router.push(role === 'artisan' ? '/artisan/dashboard' : '/')

  } catch (err) {
    if (err.response?.status === 422) {
      const first = Object.values(err.response.data.errors)?.[0]?.[0]
      errorMessage.value = first ?? 'Données invalides.'
    } else {
      errorMessage.value = 'Erreur serveur. Réessayez.'
    }
  } finally {
    isLoading.value = false
  }
}

onMounted(loadCategories)
</script>

<template>
  <div class="page">

    <!-- Left panel -->
    <div class="panel-left">
      <div class="panel-left-inner">

        <div class="brand">
          <div class="brand-mark">M</div>
          <span class="brand-name">Le Moqufe</span>
        </div>

        <div class="panel-text">
          <h2 class="panel-headline">Rejoignez la communauté<br>des artisans du Maroc.</h2>
          <p class="panel-sub">Créez votre compte en quelques minutes et commencez à recevoir des missions.</p>
        </div>

        <div class="panel-steps">
          <div class="step" v-for="(s, i) in steps" :key="i">
            <div class="step-num">{{ i + 1 }}</div>
            <span>{{ s }}</span>
          </div>
        </div>

        <div class="panel-cities">
          <span class="city-tag" v-for="c in cities" :key="c">{{ c }}</span>
        </div>

      </div>
      <div class="deco deco-1"></div>
      <div class="deco deco-2"></div>
    </div>

    <!-- Right panel -->
    <div class="panel-right">
      <div class="form-box">

        <div class="form-top">
          <h1 class="form-title">Créer un compte</h1>
          <p class="form-sub">Déjà inscrit ? <router-link to="/login" class="form-link">Se connecter</router-link></p>
        </div>

        <!-- Role selector -->
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

        <!-- Error -->
        <div class="alert-error" v-if="errorMessage">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/>
          </svg>
          {{ errorMessage }}
        </div>

        <form @submit.prevent="submit" novalidate>

          <!-- Nom + Ville -->
          <div class="row-2">
            <div class="field">
              <label class="field-label">Nom complet</label>
              <div class="field-wrap">
                <svg class="field-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                <input v-model="form.name" type="text" class="field-input" placeholder="Youssef Benali" required />
              </div>
            </div>
            <div class="field">
              <label class="field-label">Ville</label>
              <div class="field-wrap">
                <svg class="field-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                </svg>
                <input v-model="form.city" type="text" class="field-input" placeholder="Marrakech" />
              </div>
            </div>
          </div>

          <!-- Email -->
          <div class="field">
            <label class="field-label">Adresse e-mail</label>
            <div class="field-wrap">
              <svg class="field-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="2" y="4" width="20" height="16" rx="2"/>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
              </svg>
              <input v-model="form.email" type="email" class="field-input" placeholder="vous@exemple.com" required />
            </div>
          </div>

          <!-- Password -->
          <div class="row-2">
            <div class="field">
              <label class="field-label">Mot de passe</label>
              <div class="field-wrap">
                <svg class="field-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  class="field-input" placeholder="••••••••" required
                />
                <button type="button" class="eye-btn" @click="showPassword = !showPassword" tabindex="-1">
                  <svg v-if="!showPassword" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                  </svg>
                  <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                    <path d="m1 1 22 22"/>
                  </svg>
                </button>
              </div>
            </div>
            <div class="field">
              <label class="field-label">Confirmer</label>
              <div class="field-wrap">
                <svg class="field-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input v-model="form.password_confirmation" type="password" class="field-input" placeholder="••••••••" required />
              </div>
            </div>
          </div>

          <!-- Artisan fields -->
          <template v-if="form.role === 'artisan'">
            <div class="divider">
              <span>Informations artisan</span>
            </div>

            <div class="field">
              <label class="field-label">Téléphone <span class="req">*</span></label>
              <div class="field-wrap">
                <svg class="field-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 17z"/>
                </svg>
                <input v-model="form.phone" type="tel" class="field-input" placeholder="+212 6XX XXX XXX" required />
              </div>
            </div>

            <!-- Categories -->
            <div class="field">
              <label class="field-label">
                Spécialités <span class="req">*</span>
                <span class="hint">(plusieurs choix)</span>
              </label>
              <div v-if="categories.length === 0" class="cats-empty">Chargement...</div>
              <div v-else class="cats-grid">
                <button
                  v-for="cat in categories" :key="cat.id"
                  type="button" class="cat-chip"
                  :class="{ selected: isSelected(cat.id) }"
                  @click="toggleCategory(cat.id)"
                >
                  <svg v-if="isSelected(cat.id)" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <path d="M20 6 9 17l-5-5"/>
                  </svg>
                  {{ cat.name }}
                </button>
              </div>
              <p class="cats-count" v-if="form.categories.length > 0">
                {{ form.categories.length }} spécialité{{ form.categories.length > 1 ? 's' : '' }} sélectionnée{{ form.categories.length > 1 ? 's' : '' }}
              </p>
            </div>

            <!-- Bio -->
            <div class="field">
              <label class="field-label">Description <span class="hint">(optionnel)</span></label>
              <textarea
                v-model="form.bio"
                class="field-input field-textarea"
                placeholder="Ex: Plombier avec 5 ans d'expérience, disponible à Marrakech..."
                rows="3"
              />
            </div>
          </template>

          <button type="submit" class="btn-submit" :disabled="isLoading || !form.role">
            <span v-if="isLoading" class="spinner"></span>
            <template v-else>
              Créer mon compte
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M5 12h14M12 5l7 7-7 7"/>
              </svg>
            </template>
          </button>

        </form>
      </div>
    </div>

  </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=DM+Sans:wght@300;400;500&display=swap');
:root {
  --brown-dark:  #3E2723;
  --brown-mid:   #5D4037;
  --brown-light: #8D6E63;
  --cream:       #FAF7F4;
  --cream-dark:  #F0EAE4;
  --text-main:   #1C1410;
  --text-muted:  #8C7B72;
  --red:         #993C1D;
  --red-bg:      #FAECE7;
}
</style>

<style scoped>
* { box-sizing: border-box; margin: 0; padding: 0; }

.page {
  display: flex;
  min-height: 100vh;
  font-family: 'DM Sans', sans-serif;
  background: var(--cream);
}

/* Left panel */
.panel-left {
  width: 380px; flex-shrink: 0;
  background: var(--brown-dark);
  padding: 3rem;
  display: flex; flex-direction: column;
  position: relative; overflow: hidden;
}
.panel-left-inner {
  position: relative; z-index: 2; flex: 1;
  display: flex; flex-direction: column; justify-content: space-between;
}
.deco { position: absolute; border-radius: 50%; opacity: 0.06; background: #fff; }
.deco-1 { width: 320px; height: 320px; bottom: -80px; right: -100px; }
.deco-2 { width: 180px; height: 180px; top: 60px; right: -60px; }

.brand { display: flex; align-items: center; gap: 10px; }
.brand-mark {
  width: 34px; height: 34px; border-radius: 10px;
  background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2);
  display: flex; align-items: center; justify-content: center;
  font-family: 'Playfair Display', serif;
  font-size: 16px; color: #FFF8F6; font-weight: 600;
}
.brand-name { font-size: 15px; font-weight: 500; color: #FFF8F6; letter-spacing: -0.3px; }

.panel-text { margin-top: auto; padding-top: 3rem; }
.panel-headline {
  font-family: 'Playfair Display', serif;
  font-size: 24px; font-weight: 500; color: #FFF8F6;
  line-height: 1.35; margin-bottom: 12px;
}
.panel-sub { font-size: 13px; color: #BCAAA4; line-height: 1.6; }

.panel-steps { display: flex; flex-direction: column; gap: 12px; margin-top: 2rem; }
.step { display: flex; align-items: center; gap: 10px; font-size: 12.5px; color: #D7CCC8; }
.step-num {
  width: 22px; height: 22px; border-radius: 50%;
  background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15);
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 500; flex-shrink: 0;
}

.panel-cities { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 2rem; }
.city-tag {
  font-size: 10px; font-weight: 500;
  padding: 3px 10px; border-radius: 99px;
  background: rgba(255,255,255,0.07);
  border: 1px solid rgba(255,255,255,0.1); color: #BCAAA4;
}

/* Right panel */
.panel-right {
  flex: 1; display: flex; align-items: flex-start; justify-content: center;
  padding: 3rem 2rem; background: var(--cream); overflow-y: auto;
}
.form-box {
  width: 100%; max-width: 440px;
  animation: fadeUp 0.4s ease both;
}
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}

.form-top { margin-bottom: 1.5rem; }
.form-title {
  font-family: 'Playfair Display', serif;
  font-size: 28px; font-weight: 600; color: var(--text-main);
  letter-spacing: -0.5px; margin-bottom: 6px;
}
.form-sub { font-size: 13px; color: var(--text-muted); }
.form-link { color: var(--brown-mid); font-weight: 500; text-decoration: none; }
.form-link:hover { color: var(--brown-dark); }

/* Role selector */
.role-selector { display: flex; gap: 10px; margin-bottom: 1.2rem; }
.role-btn {
  flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px;
  padding: 12px 8px; border-radius: 10px;
  border: 1.5px solid var(--cream-dark);
  background: #fff; cursor: pointer;
  transition: all 0.15s; font-family: inherit;
}
.role-btn:hover { border-color: var(--brown-light); background: var(--cream); }
.role-btn.active { border-color: var(--brown-mid); background: #FFF8F6; }
.role-icon { font-size: 20px; }
.role-label { font-size: 13px; font-weight: 500; color: var(--text-main); }
.role-desc { font-size: 11px; color: var(--text-muted); }

/* Alert */
.alert-error {
  display: flex; align-items: center; gap: 8px;
  background: var(--red-bg); color: var(--red);
  border-radius: 9px; padding: 10px 13px;
  font-size: 12.5px; font-weight: 500;
  margin-bottom: 1rem;
}

/* Fields */
.row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.field { margin-bottom: 1rem; }
.field-label {
  display: block; font-size: 12px; font-weight: 500;
  color: var(--text-main); margin-bottom: 6px; letter-spacing: 0.01em;
}
.hint { font-size: 11px; font-weight: 400; color: var(--text-muted); margin-left: 4px; }
.req { color: var(--red); }

.field-wrap { position: relative; display: flex; align-items: center; }
.field-icon { position: absolute; left: 13px; color: var(--text-muted); pointer-events: none; }
.field-input {
  width: 100%; padding: 11px 42px 11px 40px;
  border: 1.5px solid var(--cream-dark); border-radius: 10px;
  background: #fff; font-size: 13.5px;
  font-family: 'DM Sans', sans-serif; color: var(--text-main);
  outline: none; transition: border-color 0.15s, box-shadow 0.15s;
}
.field-input::placeholder { color: #BFB5AE; }
.field-input:focus {
  border-color: var(--brown-mid);
  box-shadow: 0 0 0 3px rgba(93,64,55,0.08);
}
.field-textarea {
  padding: 11px 14px; resize: vertical;
  font-family: 'DM Sans', sans-serif;
}

.eye-btn {
  position: absolute; right: 12px; background: none; border: none;
  cursor: pointer; color: var(--text-muted); padding: 2px;
  display: flex; align-items: center; transition: color 0.15s;
}
.eye-btn:hover { color: var(--brown-mid); }

/* Divider */
.divider {
  display: flex; align-items: center; gap: 10px;
  margin: 1rem 0 0.8rem; font-size: 11px;
  font-weight: 500; color: var(--text-muted);
  text-transform: uppercase; letter-spacing: 0.05em;
}
.divider::before, .divider::after {
  content: ''; flex: 1; height: 1px; background: var(--cream-dark);
}

/* Categories */
.cats-grid { display: flex; flex-wrap: wrap; gap: 7px; margin-top: 4px; }
.cat-chip {
  display: flex; align-items: center; gap: 5px;
  padding: 6px 12px; border-radius: 99px;
  border: 1.5px solid var(--cream-dark);
  background: #fff; font-size: 12.5px;
  color: var(--text-main); cursor: pointer;
  transition: all 0.15s; font-family: inherit;
}
.cat-chip:hover { border-color: var(--brown-light); }
.cat-chip.selected {
  border-color: var(--brown-mid); background: #FFF8F6;
  color: var(--brown-mid); font-weight: 500;
}
.cats-empty { font-size: 13px; color: var(--text-muted); padding: 6px 0; }
.cats-count { font-size: 12px; color: var(--brown-mid); margin-top: 6px; font-weight: 500; }

/* Submit */
.btn-submit {
  width: 100%; padding: 13px;
  background: var(--brown-dark); color: #FFF8F6;
  border: none; border-radius: 10px;
  font-size: 13.5px; font-weight: 500;
  font-family: 'DM Sans', sans-serif; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: background 0.15s, transform 0.1s; margin-top: 0.4rem;
}
.btn-submit:hover:not(:disabled) { background: #2C1A17; }
.btn-submit:active:not(:disabled) { transform: scale(0.99); }
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }

.spinner {
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff; border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 700px) {
  .page { flex-direction: column; }
  .panel-left { width: 100%; padding: 2rem; min-height: auto; }
  .panel-steps, .panel-cities { display: none; }
  .panel-right { padding: 2rem 1.5rem 3rem; }
  .row-2 { grid-template-columns: 1fr; }
}
</style>