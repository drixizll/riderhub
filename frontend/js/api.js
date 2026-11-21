/**
 * RiderHub Frontend API Client (v1)
 * Connects the standalone /frontend folder to the Laravel Backend REST API.
 */
export class RiderHubApi {
    constructor(baseUrl = window.RIDERHUB_API_BASE || 'http://127.0.0.1:8000/api/v1') {
        this.baseUrl = baseUrl.replace(/\/+$/, '');
    }

    async request(path, options = {}) {
        const response = await fetch(`${this.baseUrl}${path}`, {
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                ...(options.headers || {}),
            },
            ...options,
        });

        if (!response.ok) {
            throw new Error(`API ${response.status}: ${response.statusText}`);
        }

        return response.json();
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
}
