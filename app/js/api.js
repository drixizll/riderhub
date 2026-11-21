/**
 * RiderHub Frontend API Client (v1)
 * Connects the standalone /frontend folder to the Laravel Backend REST API.
 */
export class RiderHubApi {
    constructor(baseUrl = window.RIDERHUB_API_BASE || 'http://127.0.0.1:8000/api/v1') {
        this.baseUrl = baseUrl.replace(/\/+$/, '');
        this.tokenKey = 'riderhub_api_token';
    }

    getToken() {
        try {
            return localStorage.getItem(this.tokenKey) || '';
        } catch {
            return '';
        }
    }

    setToken(token) {
        try {
            if (token) localStorage.setItem(this.tokenKey, token);
            else localStorage.removeItem(this.tokenKey);
        } catch {
            // Ignore storage restrictions
        }
    }

    async request(path, options = {}) {
        const token = this.getToken();
        const response = await fetch(`${this.baseUrl}${path}`, {
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                ...(token ? { Authorization: `Bearer ${token}` } : {}),
                ...(options.headers || {}),
            },
            ...options,
        });

        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(payload.message || `API ${response.status}: ${response.statusText}`);
        }

        return payload;
    }

    health() {
        return this.request('/health');
    }

    summary() {
        return this.request('/summary');
    }

    weather(lat = 8.5378, lng = 124.7536) {
        return this.request(`/weather?lat=${encodeURIComponent(lat)}&lng=${encodeURIComponent(lng)}`);
    }

    estimateFare(distanceKm) {
        return this.request('/fare-estimate', {
            method: 'POST',
            body: JSON.stringify({ distance_km: Number(distanceKm) }),
        });
    }

    chat(message, history = []) {
        return this.request('/assistant/chat', {
            method: 'POST',
            body: JSON.stringify({ message, history }),
        });
    }

    async register(data) {
        const res = await this.request('/register', {
            method: 'POST',
            body: JSON.stringify(data),
        });
        if (res.token) this.setToken(res.token);
        return res;
    }

    async login(email, password) {
        const res = await this.request('/login', {
            method: 'POST',
            body: JSON.stringify({ email, password }),
        });
        if (res.token) this.setToken(res.token);
        return res;
    }

    me() {
        return this.request('/me');
    }

    async logout() {
        try {
            await this.request('/logout', { method: 'POST' });
        } finally {
            this.setToken('');
        }
    }

    listBookings() {
        return this.request('/bookings');
    }

    createBooking(data) {
        return this.request('/bookings', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }
}
