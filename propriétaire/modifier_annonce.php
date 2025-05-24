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

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'];
    $adresse = $_POST['adresse'];
    $type_logement = $_POST['type_logement'];
    $supperficie = $_POST['supperficie'];
    $nombre_pieces = $_POST['nombre_pieces'];
    $nombre_personnes = $_POST['nombre_personnes'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $equipements = $_POST['equipements'];
    $autres_equipements = $_POST['autres_equipements'];
    $tarif = $_POST['tarif'];

    $sql = "UPDATE annonce SET 
        titre = ?, adresse = ?, type_logement = ?, supperficie = ?, 
        nombre_pieces = ?, nombre_personnes = ?, date_debut = ?, date_fin = ?, 
        equipements = ?, autres_equipements = ?, tarif = ? 
        WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiiissssdi", $titre, $adresse, $type_logement, $supperficie, $nombre_pieces, $nombre_personnes, $date_debut, $date_fin, $equipements, $autres_equipements, $tarif, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: detail_bien.php?id=" . $id);
    exit;
}

// Récupération des données existantes
$sql = "SELECT * FROM annonce WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$annonce = $result->fetch_assoc();
$photos = explode(',', $annonce['photos']);
$photo_principale = !empty($photos[0]) ? '../annonces/' . $photos[0] : '../images/default.jpg';

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Modifier Annonce</title>
  <link rel="stylesheet" href="detail_bien.css"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  
</head>
<body>

<nav class="nav-barre">
  <div>
    <img class="Logo" src="../images/Logo.png" alt="Logo" />
  </div>
  <a href="detail_bien.php?id=<?= $annonce['id'] ?>" class="boutt">Retour</a>
</nav>




<div class="container">
  <form method="POST">
    <div class="titrem">
      <h1>Modifier l'annonce : </h1> 
        <div class="detail" >
          <div >
          <h3>Titre</h3>
          <input type="text" name="titre" value="<?= htmlspecialchars($annonce['titre']) ?>" required />
          </div>
        </div>
    </div> <br>
    <div class="image-princ">
      <img src="<?= htmlspecialchars($photo_principale) ?>" alt="Photo" />
    </div>

    <div class="details">



      <div class="detail">
        <div class="detail-cont">
          <p class="icon"><i class="fas fa-map-marker-alt"></i></p>
          <div>
            <h3>Adresse</h3>
            <input type="text" name="adresse" value="<?= htmlspecialchars($annonce['adresse']) ?>" />
          </div>
        </div>
      </div>

      <div class="detail">
        <div class="detail-cont">
          <p class="icon"><i class="fas fa-house"></i></p>
          <div>
            <h3>Type de bien</h3>
            <input type="text" name="type_logement" value="<?= htmlspecialchars($annonce['type_logement']) ?>" />
          </div>
        </div>
      </div>

      <div class="detail">
        <div class="detail-cont">
          <p class="icon"><i class="fas fa-ruler-combined"></i></p>
          <div>
            <h3>Superficie</h3>
            <input type="number" name="supperficie" value="<?= htmlspecialchars($annonce['supperficie']) ?>" />
          </div>
        </div>
      </div>

      <div class="detail">
        <div class="detail-cont">
          <p class="icon"><i class="fas fa-bed"></i></p>
          <div>
            <h3>Nombre de pièces</h3>
            <input type="number" name="nombre_pieces" value="<?= htmlspecialchars($annonce['nombre_pieces']) ?>" />
          </div>
        </div>
      </div>

      <div class="detail">
        <div class="detail-cont">
          <p class="icon"><i class="fas fa-user-friends"></i></p>
          <div>
            <h3>Nombre de personnes autorisées</h3>
            <input type="number" name="nombre_personnes" value="<?= htmlspecialchars($annonce['nombre_personnes']) ?>" />
          </div>
        </div>
      </div>

      <div class="detail">
        <div class="detail-cont">
          <p class="icon"><i class="fas fa-calendar-alt"></i></p>
          <div>
            <h3>Disponibilité</h3>
            <input type="date" name="date_debut" value="<?= htmlspecialchars($annonce['date_debut']) ?>" />
            <input type="date" name="date_fin" value="<?= htmlspecialchars($annonce['date_fin']) ?>" />
          </div>
        </div>
      </div>
    </div>

    <div class="detail description">
      <div class="detail-cont">
        <p class="icon"><i class="fas fa-cogs"></i></p>
        <div>
          <h3>Équipements</h3>
          <textarea name="equipements"><?= htmlspecialchars($annonce['equipements']) ?></textarea>
          <p><strong>Autres :</strong> <input type="text" name="autres_equipements" value="<?= htmlspecialchars($annonce['autres_equipements']) ?>"></p>
        </div>
      </div>
    </div>

    <div class="prix">
      <div class="detail-cont">
        <p class="icon"><i class="fas fa-coins"></i></p>
        <div>
          <h3>Tarif (par nuit)</h3>
          <input type="number" name="tarif" value="<?= htmlspecialchars($annonce['tarif']) ?>" /> DA
        </div>
      </div>
    </div>

    <div class="btn-reserv">
      <button class="btn" type="submit"> Enregistrer les modifications</button>
    </div>
  </form>
</div>

</body>


</html>
