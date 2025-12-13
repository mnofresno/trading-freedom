import axios from 'axios';
import storage from '../utils/storage';

const API_URL = 'https://trading-freedom.fresno.ar/api';

const api = axios.create({
    baseURL: API_URL,
    headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

api.interceptors.request.use(async (config) => {
    const token = await storage.getItemAsync('user_token');
    if (token) {
        // Backend middleware expects lowercase "bearer" (not standard but required for compatibility)
        config.headers.Authorization = `bearer ${token}`;
    }
    return config;
});

export default api;
