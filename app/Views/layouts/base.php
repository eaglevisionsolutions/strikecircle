<?php
// Base layout for all pages
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'StrikeCircle') ?></title>
  <link rel="manifest" href="/manifest.json">
  <link rel="stylesheet" href="/assets/css/app.css">
  <link rel="icon" href="/assets/img/favicon.png">
  <meta name="theme-color" content="#2F80ED">
  <meta name="description" content="Connect. Compete. Bowl. StrikeCircle is the social PWA for bowlers.">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
  <?php include __DIR__ . '/../../Partials/nav_public.php'; ?>
  <main>
    <?= $content ?? '' ?>
  </main>
  <?php include __DIR__ . '/../../Partials/footer.php'; ?>
</body>
</html>
