<?php
$title = 'StrikeCircle';
ob_start();
?>
<div class="card">
  <h1>Welcome to StrikeCircle</h1>
  <p>Connect. Compete. Bowl.</p>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/base.php';
