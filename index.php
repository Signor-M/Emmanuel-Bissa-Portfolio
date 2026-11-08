<?php
require_once __DIR__ . "/includes/config.php";
require_once __DIR__ . "/includes/functions.php";

$forwardedProtocol = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
$protocol = in_array($forwardedProtocol, ['http', 'https'], true)
    ? $forwardedProtocol
    : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
$baseUrl = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>Emmanuel Bissa | Développeur Full-Stack, Mobile &amp; Jeux Vidéo</title>
  <meta name="description" content="Portfolio officiel d'Emmanuel Bissa, ingénieur logiciel et développeur full-stack, mobile, jeux vidéo (Godot/GDevelop) et IoT. Découvrez mes projets et services.">
  <meta name="keywords" content="Emmanuel Bissa, Développeur Full Stack, Développeur Web Abidjan, Création application mobile, Godot Engine, IoT, Portfolio développeur">
  <meta name="author" content="Emmanuel Bissa">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="<?php echo htmlspecialchars($baseUrl . '/', ENT_QUOTES, 'UTF-8'); ?>">

  <!-- Open Graph / Partage réseaux sociaux -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?php echo htmlspecialchars($baseUrl . '/', ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:title" content="Emmanuel Bissa | Développeur Full-Stack, Mobile &amp; Jeux Vidéo">
  <meta property="og:description" content="Explorez mon univers numérique : du développement web et mobile aux jeux vidéo 2D/3D et à l'électronique IoT.">
  <meta property="og:image" content="<?php echo htmlspecialchars($baseUrl . '/assets/img/portfolio-banner.png', ENT_QUOTES, 'UTF-8'); ?>">

  <!-- Twitter/X Cards -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:url" content="<?php echo htmlspecialchars($baseUrl . '/', ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="twitter:title" content="Emmanuel Bissa | Développeur Full-Stack, Mobile &amp; Jeux Vidéo">
  <meta name="twitter:description" content="Découvrez le portfolio professionnel d'Emmanuel Bissa : Web, Mobile, IoT et Game Dev.">
  <meta name="twitter:image" content="<?php echo htmlspecialchars($baseUrl . '/assets/img/icons8-source-code-100.png', ENT_QUOTES, 'UTF-8'); ?>">

  <?php include __DIR__ . "/includes/structured-data.php"; ?>

  <!-- Custom Styles -->
  <link rel="stylesheet" href="assets/css/index.css?v=<?php echo time(); ?>"/>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>
  
  <?php include "includes/header.php"; ?>

  <!-- Section Accueil -->
  <?php include "includes/sections/accueil.php"; ?>

  <!-- Section Education -->
  <?php include "includes/sections/education.php"; ?>

  <!-- Section Compétences -->
  <?php include "includes/sections/skills.php"; ?>

  <!-- Section Services -->
  <?php include "includes/sections/services.php"; ?>

  <!-- Section Projets -->
  <?php include "includes/sections/projets.php"; ?>

  <!-- Section Certificats -->
  <?php include "includes/sections/certificats.php"; ?>

  <!-- Section Contact -->
  <?php include "includes/sections/contact.php"; ?>

  <!-- Footer -->
  <?php include "includes/sections/footer.php"; ?>

  <!-- Project JavaScript -->
  <script src="assets/js/index.js"></script>
</body>

</html>