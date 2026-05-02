# Le Moqufe — Laravel + Vue.js

## Stack
- Backend: Laravel (PHP) — dossier racine
- Frontend: Vue.js — /frontend
- DB: MySQL

## Models
- User (roles: client, artisan)
- Booking (status: pending/accepted/rejected)
- Review, Payment, Category

## Relations
- User belongsToMany Category
- Booking belongsTo User (client + artisan)
- Booking hasOne Review, Payment

## Auth
- Laravel Sanctum (tokens)
- Rôles via table pivot role_user