import { defineStore } from "pinia";
import api from "../services/api";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        token: localStorage.getItem("token") || null,
        user: null
    }),

    actions: {
        async login(form) {
            const res = await api.post("/login", form)

            this.token = res.data.token
            localStorage.setItem("token", this.token)

            await this.getUser() // 
        },

        async getUser() {
            const res = await api.get("/profile", {
                headers: { Authorization: `Bearer ${this.token}` }
            });

            this.user = res.data;
        },

        logout() {
            this.token = null;
            this.user = null;
            localStorage.removeItem("token");
        }
    }
});