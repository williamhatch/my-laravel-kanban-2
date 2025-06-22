import { reactive } from 'vue';
import apiClient from './api';
import router from '../router';

interface User {
    id: number;
    name: string;
    email: string;
}

interface AuthState {
    user: User | null;
    isAuthenticated: boolean;
}

export const authState = reactive<AuthState>({
    user: null,
    isAuthenticated: false,
});

export const authService = {
    async fetchUser() {
        const token = localStorage.getItem('auth_token');
        if (token) {
            try {
                apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
                const { data } = await apiClient.get('/api/user');
                authState.user = data;
                authState.isAuthenticated = true;
            } catch (error) {
                console.error('Failed to fetch user', error);
                this.clearAuthData();
                throw error; // Re-throw so the router can handle it
            }
        }
    },

    async register(credentials: any) {
        const response = await apiClient.post('/api/register', credentials);
        const { user, token } = response.data;
        localStorage.setItem('auth_token', token);
        authState.user = user;
        authState.isAuthenticated = true;
        apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        router.push('/');
    },

    async login(credentials: any) {
        const response = await apiClient.post('/api/login', credentials);
        const { user, token } = response.data;
        localStorage.setItem('auth_token', token);
        authState.user = user;
        authState.isAuthenticated = true;
        apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        router.push('/');
    },

    async logout() {
        try {
            await apiClient.post('/api/logout');
        } catch (error) {
            console.error('Logout failed, but clearing client-side data anyway.', error);
        } finally {
            this.clearAuthData();
            router.push('/login');
        }
    },

    clearAuthData() {
        localStorage.removeItem('auth_token');
        authState.user = null;
        authState.isAuthenticated = false;
        delete apiClient.defaults.headers.common['Authorization'];
    }
};

// Initialize auth on app start
const initAuth = async () => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        await authService.fetchUser();
    }
};

initAuth(); 