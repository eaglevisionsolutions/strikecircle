<?php
$title = 'Login';
ob_start();
?>
<div class="card">
  <h2>Login</h2>
  <form id="loginForm">
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Login</button>
  </form>
  <div id="loginError" style="color:red;"></div>
</div>
<script src="/assets/js/auth.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/base.php';
