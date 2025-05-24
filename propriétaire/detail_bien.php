<?php
session_start();

$host = 'localhost';
$db = 'location_immobiliere';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "❌ ID non spécifié.";
    exit;
}

$sql = "SELECT * FROM annonce WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "❌ Aucune annonce trouvée.";
    exit;
}

$annonce = $result->fetch_assoc();
$photos = explode(',', $annonce['photos']);
$photo_principale = !empty($photos[0]) ? '../annonces/'. $photos[0] : '../images/default.jpg';








$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Détails du Bien </title>
  <link rel="stylesheet" href="detail_bien.css"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
</head>
<body>

<nav class="nav-barre">
    <div>
      <img class="Logo" src="../images/Logo.png" alt="Logo" />
    </div>
    <div>
    <a class="boutt" href="modifier_annonce.php?id=<?= $annonce['id'] ?>" >Modifier</a>
    <a class="boutt" href="profil.php"><i class="fas fa-arrow-right"></i></a>
    </div>

  </nav>

       
  <div class="titre">
  <h1><?= htmlspecialchars($annonce['titre']) ?></h1>
</div>

<div class="container">

<div class="image-princ">
<img src="<?= htmlspecialchars($photo_principale) ?>" alt="<?= htmlspecialchars($annonce['titre']) ?>" />

</div>


<div class="details">
  <div class="detail">
    <div class="detail-cont">
      <p class="icon"><i class="fas fa-map-marker-alt"></i></p>
      <div>
        <h3>Adresse</h3>
        <p><?= htmlspecialchars($annonce['adresse']) ?></p>
      </div>
    </div>
  </div>

  <div class="detail">
    <div class="detail-cont">
      <p class="icon"><i class="fas fa-house"></i></p>
      <div>
        <h3>Type de bien</h3>
        <p><?= htmlspecialchars($annonce['type_logement']) ?></p>
      </div>
    </div>
  </div>

  <div class="detail">
    <div class="detail-cont">
      <p class="icon"><i class="fas fa-ruler-combined"></i></p>
      <div>
        <h3>Superficie</h3>
        <p><?= htmlspecialchars($annonce['supperficie']) ?> m²</p>
      </div>
    </div>
  </div>

  <div class="detail">
    <div class="detail-cont">
      <p class="icon"><i class="fas fa-bed"></i></p>
      <div>
        <h3>Nombre de pièces</h3>
        <p><?= htmlspecialchars($annonce['nombre_pieces']) ?></p>
      </div>
    </div>
  </div>

  <div class="detail">
    <div class="detail-cont">
      <p class="icon"><i class="fas fa-user-friends"></i></p>
      <div>
        <h3>Nombre de personnes autorisées</h3>
        <p><?= htmlspecialchars($annonce['nombre_personnes']) ?></p>
      </div>
    </div>
  </div>

  <div class="detail">
    <div class="detail-cont">
      <p class="icon"><i class="fas fa-calendar-alt"></i></p>
      <div>
        <h3>Disponibilité</h3>
        <p>Du <?= htmlspecialchars($annonce['date_debut']) ?> au <?= htmlspecialchars($annonce['date_fin']) ?></p>
      </div>
    </div>
  </div>
  </div>


  <div class="detail description">
      <div class="detail-cont">
      <p class="icon"><i class="fas fa-cogs"></i></p>
      <div>
        <h3>Équipements</h3>
        <p><?= nl2br(htmlspecialchars($annonce['equipements'])) ?></p>
        <?php if (!empty($annonce['autres_equipements'])): ?>
        <p><strong>Autres : </strong><?= htmlspecialchars($annonce['autres_equipements']) ?></p>
        <?php endif; ?>
      </div>
    </div>
  </div>


  <div class="prix">
    <div class="detail-cont">
      <p class="icon"><i class="fas fa-coins"></i></p>
      <div>
        <h3>Tarif (par nuit)</h3>
        <p class="txt"><?= htmlspecialchars($annonce['tarif']) ?> DA</p>
      </div>
    </div>
  </div>
    </div>

  

 
  
</body>


</html>
