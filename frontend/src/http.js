// src/http.js
import axios from 'axios';
import { useAuthStore } from './stores/auth'; // Importamos el store

// La URL base de tu API (Lumen corriendo en 8082)
const API_BASE_URL = 'http://localhost:8082/api';

const http = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Interceptor para inyectar el token en cada petición si está disponible
http.interceptors.request.use((config) => {
  const authStore = useAuthStore();
  if (authStore.token) {
    config.headers['Authorization'] = `Bearer ${authStore.token}`;
  }
  return config;
}, (error) => {
  return Promise.reject(error);
});


export default http;