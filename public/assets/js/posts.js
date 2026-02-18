// Handles post composer and score modal logic
import { apiPost } from './api.js';
import { loadFeed } from './feed.js';

document.addEventListener('DOMContentLoaded', () => {
    const composer = document.getElementById('postComposer');
    const composerError = document.getElementById('composerError');
    const addScoreBtn = document.getElementById('addScoreBtn');
    const scoreModal = document.getElementById('scoreModal');
    const closeScoreModal = document.getElementById('closeScoreModal');
    const scoreForm = document.getElementById('scoreForm');
    const scoreError = document.getElementById('scoreError');

    composer.onsubmit = async (e) => {
        e.preventDefault();
        composerError.textContent = '';
        const body = document.getElementById('composerBody').value.trim();
        if (!body) {
            composerError.textContent = 'Post cannot be empty.';
            return;
        }
        try {
            const res = await apiPost('/api/v1/posts', { type: 'text', body });
            if (res.success) {
                document.getElementById('composerBody').value = '';
                loadFeed(true);
            } else {
                composerError.textContent = res.error || 'Failed to post.';
            }
        } catch {
            composerError.textContent = 'Failed to post.';
        }
    };

    addScoreBtn.onclick = () => {
        scoreModal.style.display = '';
    };
    closeScoreModal.onclick = () => {
        scoreModal.style.display = 'none';
        scoreError.textContent = '';
        scoreForm.reset();
    };
    scoreForm.onsubmit = async (e) => {
        e.preventDefault();
        scoreError.textContent = '';
        const score = parseInt(document.getElementById('scoreValue').value, 10);
        const body = document.getElementById('scoreBody').value.trim();
        if (isNaN(score) || score < 0 || score > 300) {
            scoreError.textContent = 'Score must be 0-300.';
            return;
        }
        try {
            const res = await apiPost('/api/v1/posts', { type: 'score', score: { value: score }, body });
            if (res.success) {
                scoreModal.style.display = 'none';
                scoreForm.reset();
                loadFeed(true);
            } else {
                scoreError.textContent = res.error || 'Failed to post score.';
            }
        } catch {
            scoreError.textContent = 'Failed to post score.';
        }
    };
});
