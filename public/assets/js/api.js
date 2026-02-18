
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
	request(path, opts = {}) {
		opts.headers = opts.headers || {};
		opts.headers['Content-Type'] = 'application/json';
		const token = this.getToken();
		if (token) opts.headers['Authorization'] = 'Bearer ' + token;
		return fetch(this.base + path, opts).then(async r => {
			const data = await r.json().catch(() => ({}));
			if (!r.ok) throw data.error || { code: 'HTTP_ERROR', message: r.statusText };
			return data;
		});
	}
};
