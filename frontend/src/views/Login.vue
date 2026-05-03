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
          <h2 class="panel-headline">L'artisan qu'il vous faut,<br>là où vous êtes.</h2>
          <p class="panel-sub">Plombiers, électriciens, jardiniers, piscinistes — partout au Maroc.</p>
        </div>

        <div class="panel-features">
          <div class="feature" v-for="f in features" :key="f.label">
            <div class="feature-dot"></div>
            <span>{{ f.label }}</span>
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
          <h1 class="form-title">Connexion</h1>
          <p class="form-sub">Bon retour parmi nous 👋</p>
        </div>

        <form @submit.prevent="submit" novalidate>

          <!-- Email -->
          <div class="field" :class="{ 'field-error': errors.email }">
            <label class="field-label">Adresse e-mail</label>
            <div class="field-wrap">
              <svg class="field-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="2" y="4" width="20" height="16" rx="2"/>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
              </svg>
              <input
                v-model="email"
                type="email"
                class="field-input"
                placeholder="vous@exemple.com"
                autocomplete="email"
                @blur="validateEmail"
              />
            </div>
            <span class="field-error-msg" v-if="errors.email">{{ errors.email }}</span>
          </div>

          <!-- Password -->
          <div class="field" :class="{ 'field-error': errors.password }">
            <label class="field-label">Mot de passe</label>
            <div class="field-wrap">
              <svg class="field-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="3" y="11" width="18" height="11" rx="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                class="field-input"
                placeholder="••••••••"
                autocomplete="current-password"
              />
              <button type="button" class="eye-btn" @click="showPassword = !showPassword" tabindex="-1">
                <svg v-if="!showPassword" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
                <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                  <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                  <path d="m1 1 22 22"/>
                </svg>
              </button>
            </div>
            <span class="field-error-msg" v-if="errors.password">{{ errors.password }}</span>
          </div>

          <!-- Global error -->
          <div class="alert-error" v-if="errorMsg">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/>
            </svg>
            {{ errorMsg }}
          </div>

          <!-- Submit -->
          <button type="submit" class="btn-submit" :disabled="isLoading">
            <span v-if="isLoading" class="spinner"></span>
            <template v-else>
              Se connecter
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M5 12h14M12 5l7 7-7 7"/>
              </svg>
            </template>
          </button>

        </form>

        <div class="form-footer">
          Pas encore de compte ?
          <router-link to="/register" class="form-link">S'inscrire</router-link>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const email        = ref("")
const password     = ref("")
const showPassword = ref(false)
const isLoading    = ref(false)
const errorMsg     = ref("")
const errors       = reactive({ email: "", password: "" })

const features = [
  { label: "Artisans vérifiés et notés" },
  { label: "Réservation en quelques clics" },
  { label: "Paiement sécurisé en ligne" },
  { label: "Disponible partout au Maroc" },
]

const cities = ["Casablanca", "Rabat", "Marrakech", "Fès", "Tanger", "Agadir"]

const validateEmail = () => {
  if (!email.value) {
    errors.email = "L'e-mail est requis."
  } else if (!/\S+@\S+\.\S+/.test(email.value)) {
    errors.email = "Format d'e-mail invalide."
  } else {
    errors.email = ""
  }
}

const submit = async () => {
  validateEmail()
  errors.password = !password.value ? "Le mot de passe est requis." : ""
  if (errors.email || errors.password) return

  isLoading.value = true
  errorMsg.value  = ""

  try {
    const res = await axios.post("http://127.0.0.1:8000/api/login", {
      email:    email.value,
      password: password.value,
    })

    localStorage.setItem("token", res.data.access_token)
    localStorage.setItem("user",  JSON.stringify(res.data.user))

    router.push("/")

  } catch (err) {
    if (err.response?.status === 401) {
      errorMsg.value = "E-mail ou mot de passe incorrect."
    } else if (err.response?.status === 422) {
      errorMsg.value = "Vérifiez les champs et réessayez."
    } else {
      errorMsg.value = "Erreur serveur. Réessayez dans un instant."
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<!-- global — :root khارج scoped باش variables katkhdem -->
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

/* ─── Left panel ─── */
.panel-left {
  width: 420px;
  flex-shrink: 0;
  background: var(--brown-dark);
  padding: 3rem;
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
}

.panel-left-inner {
  position: relative;
  z-index: 2;
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.deco {
  position: absolute;
  border-radius: 50%;
  opacity: 0.06;
  background: #fff;
}
.deco-1 { width: 320px; height: 320px; bottom: -80px; right: -100px; }
.deco-2 { width: 180px; height: 180px; top: 60px;    right: -60px;  }

.brand {
  display: flex;
  align-items: center;
  gap: 10px;
}
.brand-mark {
  width: 34px; height: 34px;
  border-radius: 10px;
  background: rgba(255,255,255,0.12);
  border: 1px solid rgba(255,255,255,0.2);
  display: flex; align-items: center; justify-content: center;
  font-family: 'Playfair Display', serif;
  font-size: 16px; color: #FFF8F6; font-weight: 600;
}
.brand-name {
  font-size: 15px; font-weight: 500;
  color: #FFF8F6; letter-spacing: -0.3px;
}

.panel-text { margin-top: auto; padding-top: 3rem; }
.panel-headline {
  font-family: 'Playfair Display', serif;
  font-size: 28px; font-weight: 500;
  color: #FFF8F6;
  line-height: 1.35;
  margin-bottom: 14px;
}
.panel-sub {
  font-size: 13px;
  color: #BCAAA4;
  line-height: 1.6;
}

.panel-features {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 2.5rem;
}
.feature {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 12.5px;
  color: #D7CCC8;
}
.feature-dot {
  width: 6px; height: 6px;
  border-radius: 50%;
  background: var(--brown-light);
  flex-shrink: 0;
}

.panel-cities {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 2rem;
}
.city-tag {
  font-size: 10px;
  font-weight: 500;
  padding: 3px 10px;
  border-radius: 99px;
  background: rgba(255,255,255,0.07);
  border: 1px solid rgba(255,255,255,0.1);
  color: #BCAAA4;
}

/* ─── Right panel ─── */
.panel-right {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: var(--cream);
}

.form-box {
  width: 100%;
  max-width: 400px;
  animation: fadeUp 0.4s ease both;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}

.form-top { margin-bottom: 2rem; }
.form-title {
  font-family: 'Playfair Display', serif;
  font-size: 30px; font-weight: 600;
  color: var(--text-main);
  letter-spacing: -0.5px;
  margin-bottom: 6px;
}
.form-sub { font-size: 13px; color: var(--text-muted); }

/* ─── Fields ─── */
.field { margin-bottom: 1.2rem; }

.field-label {
  display: block;
  font-size: 12px;
  font-weight: 500;
  color: var(--text-main);
  margin-bottom: 6px;
  letter-spacing: 0.01em;
}

.field-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.field-icon {
  position: absolute;
  left: 13px;
  color: var(--text-muted);
  pointer-events: none;
}

.field-input {
  width: 100%;
  padding: 11px 42px 11px 40px;
  border: 1.5px solid var(--cream-dark);
  border-radius: 10px;
  background: #fff;
  font-size: 13.5px;
  font-family: 'DM Sans', sans-serif;
  color: var(--text-main);
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.field-input::placeholder { color: #BFB5AE; }
.field-input:focus {
  border-color: var(--brown-mid);
  box-shadow: 0 0 0 3px rgba(93,64,55,0.08);
}

.field-error .field-input {
  border-color: #E57373;
  box-shadow: 0 0 0 3px rgba(229,115,115,0.1);
}
.field-error-msg {
  font-size: 11px;
  color: var(--red);
  margin-top: 5px;
  display: block;
}

.eye-btn {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  cursor: pointer;
  color: var(--text-muted);
  padding: 2px;
  display: flex;
  align-items: center;
  transition: color 0.15s;
}
.eye-btn:hover { color: var(--brown-mid); }

/* ─── Alert ─── */
.alert-error {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--red-bg);
  color: var(--red);
  border-radius: 9px;
  padding: 10px 13px;
  font-size: 12.5px;
  margin-bottom: 1rem;
  font-weight: 500;
}

/* ─── Submit ─── */
.btn-submit {
  width: 100%;
  padding: 13px;
  background: var(--brown-dark);
  color: #FFF8F6;
  border: none;
  border-radius: 10px;
  font-size: 13.5px;
  font-weight: 500;
  font-family: 'DM Sans', sans-serif;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: background 0.15s, transform 0.1s;
  margin-top: 0.4rem;
}
.btn-submit:hover:not(:disabled) { background: #2C1A17; }
.btn-submit:active:not(:disabled) { transform: scale(0.99); }
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }

.spinner {
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Footer ─── */
.form-footer {
  text-align: center;
  margin-top: 1.6rem;
  font-size: 12.5px;
  color: var(--text-muted);
}
.form-link {
  color: var(--brown-mid);
  font-weight: 500;
  text-decoration: none;
  margin-left: 4px;
  transition: color 0.15s;
}
.form-link:hover { color: var(--brown-dark); }

/* ─── Responsive ─── */
@media (max-width: 700px) {
  .page { flex-direction: column; }
  .panel-left { width: 100%; padding: 2rem; min-height: auto; }
  .panel-headline { font-size: 22px; }
  .panel-features, .panel-cities { display: none; }
  .panel-right { padding: 2rem 1.5rem 3rem; }
}
</style>