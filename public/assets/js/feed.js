// Handles loading and rendering the social feed
import { apiGet, apiPost } from './api.js';

let nextCursor = null;
let loading = false;

export function loadFeed(initial = false) {
	if (loading) return;
	loading = true;
	let url = '/api/v1/feed?limit=10';
	if (nextCursor && !initial) url += `&cursor=${encodeURIComponent(nextCursor)}`;
	apiGet(url)
		.then(res => {
			if (res.success) {
				renderFeed(res.data.posts, initial);
				nextCursor = res.data.next_cursor;
				if (!nextCursor) {
					document.getElementById('loadMoreBtn').style.display = 'none';
				} else {
					document.getElementById('loadMoreBtn').style.display = '';
				}
			} else {
				showError(res.error || 'Failed to load feed');
			}
		})
		.catch(() => showError('Failed to load feed'))
		.finally(() => loading = false);
}

function renderFeed(posts, initial) {
	const feed = document.getElementById('feedList');
	if (initial) feed.innerHTML = '';
	for (const post of posts) {
		const el = renderPostCard(post);
		feed.appendChild(el);
	}
}

function renderPostCard(post) {
	const card = document.createElement('div');
	card.className = 'post-card';
	card.innerHTML = `
		<div class="post-header">
			<img src="${post.avatar_url || '/assets/img/avatar.png'}" class="avatar" alt="avatar">
			<div>
				<b>${post.username}</b>
				<span class="timestamp">${formatTime(post.created_at)}</span>
			</div>
		</div>
		<div class="post-body">${post.body}</div>
		<div class="post-actions">
			<button class="like-btn" data-id="${post.id}" data-liked="${post.viewer_has_liked}">
				<span class="like-icon ${post.viewer_has_liked ? 'liked' : ''}">♥</span> <span class="like-count">${post.reaction_count}</span>
			</button>
			<button class="comment-btn" data-id="${post.id}">💬 <span class="comment-count">${post.comment_count}</span></button>
		</div>
		<div class="comments-drawer" id="comments-${post.id}" style="display:none"></div>
	`;
	return card;
}

function formatTime(ts) {
	const d = new Date(ts.replace(' ', 'T'));
	return d.toLocaleString();
}

function showError(msg) {
	alert(msg);
}

document.addEventListener('DOMContentLoaded', () => {
	loadFeed(true);
	document.getElementById('loadMoreBtn').onclick = () => loadFeed();
});
