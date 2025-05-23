<?php
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
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
</head>
<body>

<nav class="nav-barre">
    <div>
      <img class="Logo" src="../images/Logo.png" alt="Logo" />
    </div>
    <div>
    <a class="boutt" href="modifier_annonce.php?id=<?= $annonce['id'] ?>" >Modifier</a>
    <a class="boutt" href="profil.php"><i class="fas fa-user"></i></a>
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

<style>
    body {
  font-family: sans-serif;
  background: #f3f4f6;
  color: #1f2937;
  padding: 0;
  margin-top: 120px;
  margin-left:0;
  margin-right:0;

  margin-bottom:0;


}

nav.nav-barre {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 20px;
  padding: 0px 20px;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background-color:rgba(28, 42, 89, 0.89);
  height:65px;
}

.Logo {
  width: 113px;
  height: 80px;
  cursor: pointer;
  margin-top: 10px;

}
.boutt{
  
  border-radius: 20px;
  padding: 10px 20px;
  color: aliceblue;
  background-color: #5D76A9;
  cursor: pointer;
  border: none;
}

.boutt:hover {
  background-color: rosybrown;
  font-size:13.5px;

}
a{
  text-decoration: none;
  font-size:14px;

}

.container {
  max-width: 1100px;
  margin: auto;
  padding: 31px 16px;
}

.image-princ {
  height: 540px;
  overflow: hidden;
  border-radius: 16px;
  margin-bottom: 31px;
}

.image-princ img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.titre{
  margin-top:-45px;
  margin-bottom:-20px;
  display: flex;
  justify-content:space-around;
  align-items:center;
}
.titre h1 {
  font-size: 35px;
  font-weight: bold;
  margin-bottom: 16px;
}

.prix {
  font-size: 22px;
  font-weight: 700;
  display: flex;
  justify-content: space-around;
  align-items: center;

}



.details {
  display: grid;
  grid-template-columns: 1200px;
  gap: 16px;
  margin-bottom: 32px;
}

@media (min-width: 768px) {
  .details {
    grid-template-columns: repeat(3, 1fr);
  }
}

.detail,.prix {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  padding: 24px;
  margin-bottom: 24px;
}

.detail-cont {
  display: flex;
  align-items: flex-start;
  gap: 16px;
}

.detail h3,.prix h3 {
  font-weight: 600;
  margin: 0 0 0.25rem;
}

.detail p {
  color: #6b7280;
  margin: 0;
}

.icon {
  width: 1.5rem;
  height: 1.5rem;
  flex-shrink: 0;
  fill: currentColor;
  color: #4b5563;
}

.description {
  padding: 2rem;
}
.txt{
    margin:0;
    color:rgb(114, 137, 182);
    font-size:30px;


}
.btn-reserv{
    display: flex;
  justify-content: space-around;
  align-items: center;
}
.btn{
  background-color: #5D76A9;
  border-radius: 10px;
  padding: 10px 40px;
  color: aliceblue;
  border: none;
  cursor: pointer;
}
.btn:hover {
  background-color: rosybrown;
  font-size:13.5px;

}
footer {
  background-color: #222;
  color:#fff;
  padding: 100px 0;
  }
  .footer-div {
  display: flex;
  justify-content: space-between; 
  flex-wrap: wrap; 
  max-width: 1000px;
  margin: auto;
  text-align: left;
  }
  footer h3 {
    color:  #5d76a9df;
  }
  footer a{
    text-decoration:none;
    color:#fff;
}
  .contact:hover{
    font-size: 17px;

  }
  .contact:hover{
    font-size: 17px;
    color:  #5d76a9df;

  }
</style>
</html>
