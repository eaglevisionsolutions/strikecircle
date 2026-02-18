<?php
// app/Views/dashboard.php
$title = 'Dashboard';
?>
<div class="dashboard-container">
  <div class="composer-card">
    <form id="postComposer">
      <textarea name="body" id="composerBody" maxlength="1000" placeholder="What's on your mind?" required></textarea>
      <div class="composer-actions">
        <button type="button" id="addScoreBtn">Add Score</button>
        <button type="submit" id="postBtn">Post</button>
      </div>
      <div id="composerError" class="error-msg"></div>
    </form>
    <div id="scoreModal" class="modal" style="display:none">
      <div class="modal-content">
        <span class="close-modal" id="closeScoreModal">&times;</span>
        <h3>Add Score</h3>
        <form id="scoreForm">
          <!-- Score fields (simplified for MVP) -->
          <input type="number" name="score" id="scoreValue" min="0" max="300" placeholder="Score" required>
          <input type="text" name="body" id="scoreBody" maxlength="1000" placeholder="Add a message (optional)">
          <button type="submit">Post Score</button>
        </form>
        <div id="scoreError" class="error-msg"></div>
      </div>
    </div>
  </div>
  <div id="feedList"></div>
  <button id="loadMoreBtn">Load more</button>
</div>
<script type="module" src="/assets/js/feed.js"></script>
<script type="module" src="/assets/js/posts.js"></script>
<script type="module" src="/assets/js/comments.js"></script>
