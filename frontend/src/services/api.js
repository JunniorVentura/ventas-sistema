// src/services/api.js
import axios from 'axios';
import { eventBus } from '../utils/eventBus';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  timeout: 10000,
});

api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Aquí usamos el eventBus
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      eventBus.dispatchEvent(new Event('logout')); // 
    }

    return Promise.reject(error);
  }
);

export default api;
