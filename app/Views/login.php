<?php
$title = 'Login';
ob_start();
?>
<div class="card">
  <h2>Login</h2>
  <form id="loginForm">
    <input type="text" name="identifier" placeholder="Username or Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <label><input type="checkbox" name="remember_me" value="1"> Remember me</label><br>
    <button type="submit">Login</button>
  </form>
  <div style="margin-top:10px;">
    <button id="btnGoogle" type="button">Continue with Google</button>
    <button id="btnFacebook" type="button">Continue with Facebook</button>
  </div>
  <div id="loginError" style="color:red;"></div>
</div>
<script src="/assets/js/auth.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/base.php';
