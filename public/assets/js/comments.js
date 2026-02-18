// Handles comments drawer, add/list logic
import { apiGet, apiPost } from './api.js';

document.addEventListener('click', async (e) => {
    if (e.target.closest('.comment-btn')) {
        const btn = e.target.closest('.comment-btn');
        const postId = btn.dataset.id;
        const drawer = document.getElementById('comments-' + postId);
        if (drawer.style.display === 'none' || !drawer.innerHTML) {
            drawer.style.display = '';
            drawer.innerHTML = '<div>Loading...</div>';
            const res = await apiGet(`/api/v1/posts/${postId}/comments?limit=10`);
            if (res.success) {
                renderComments(drawer, res.data.comments, postId);
            } else {
                drawer.innerHTML = '<div class="error-msg">Failed to load comments</div>';
            }
        } else {
            drawer.style.display = 'none';
        }
    }
});

function renderComments(drawer, comments, postId) {
    drawer.innerHTML = `
        <div class="comments-list">
            ${comments.map(c => `<div class="comment"><b>${c.username}</b>: ${c.body}</div>`).join('')}
        </div>
        <form class="comment-form" data-id="${postId}">
            <input type="text" name="body" maxlength="500" placeholder="Add a comment..." required>
            <button type="submit">Send</button>
        </form>
        <div class="comment-error error-msg"></div>
    `;
}

document.addEventListener('submit', async (e) => {
    if (e.target.classList.contains('comment-form')) {
        e.preventDefault();
        const form = e.target;
        const postId = form.dataset.id;
        const input = form.querySelector('input[name="body"]');
        const errorDiv = form.parentElement.querySelector('.comment-error');
        errorDiv.textContent = '';
        const body = input.value.trim();
        if (!body) {
            errorDiv.textContent = 'Comment cannot be empty.';
            return;
        }
        try {
            const res = await apiPost(`/api/v1/posts/${postId}/comments`, { body });
            if (res.success) {
                input.value = '';
                // Reload comments
                const drawer = document.getElementById('comments-' + postId);
                const res2 = await apiGet(`/api/v1/posts/${postId}/comments?limit=10`);
                if (res2.success) {
                    renderComments(drawer, res2.data.comments, postId);
                }
            } else {
                errorDiv.textContent = res.error || 'Failed to add comment.';
            }
        } catch {
            errorDiv.textContent = 'Failed to add comment.';
        }
    }
});
