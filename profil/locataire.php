<?php
session_start();
if (!isset($_SESSION['utilisateur']) || strtolower($_SESSION['utilisateur']['role']) !== 'locataire') {
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Profil Locataire</title>
</head>
<body>
  <h1>Bienvenue, <?= htmlspecialchars($_SESSION['utilisateur']['prenom']) ?> !</h1>
  <p>Voici votre espace personnel de locataire.</p>
  <a href="../logout.php">Se déconnecter</a>
</body>
</html>
