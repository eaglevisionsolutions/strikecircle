
// api.js - Handles JWT, refresh, and standardized API requests
const API = {
	base: '/api/v1/',
	token: null,
	setToken(token) {
		this.token = token;
		if (token) localStorage.setItem('access_token', token);
		else localStorage.removeItem('access_token');
	},
	getToken() {
		return this.token || localStorage.getItem('access_token');
	},
	getCookie(name) {
		return document.cookie.split('; ').reduce((acc, c) => {
			const [k, v] = c.split('=');
			return k === name ? decodeURIComponent(v) : acc;
		}, '');
	},
	async refreshAccessToken() {
		const csrf = this.getCookie('refresh_csrf');
		const res = await fetch(this.base + 'auth/refresh', {
			method: 'POST',
			headers: { 'X-Refresh-CSRF': csrf },
			credentials: 'include'
		});
		const data = await res.json().catch(() => ({}));
		if (!res.ok) throw data.error || { code: 'REFRESH_FAILED', message: 'Could not refresh' };
		if (data && data.data && data.data.access_token) this.setToken(data.data.access_token);
		return data;
	},
	async request(path, opts = {}) {
		opts.headers = opts.headers || {};
		opts.headers['Content-Type'] = 'application/json';
		const token = this.getToken();
		if (token) opts.headers['Authorization'] = 'Bearer ' + token;
		let res = await fetch(this.base + path, opts);
		if (res.status === 401) {
			try {
				await this.refreshAccessToken();
				const token2 = this.getToken();
				if (token2) opts.headers['Authorization'] = 'Bearer ' + token2;
				res = await fetch(this.base + path, opts);
			} catch (e) {
				// fall through
			}
		}
		const data = await res.json().catch(() => ({}));
		if (!res.ok) throw data.error || { code: 'HTTP_ERROR', message: res.statusText };
		return data;
	}
};
