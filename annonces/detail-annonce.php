<?php
session_start();
?>

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
$photo_principale = !empty($photos[0]) ?  $photos[0] : '../images/default.jpg';



$sqlAvis = "SELECT nom, note, commentaire FROM avis WHERE annonce_id = ?";
$stmtAvis = $conn->prepare($sqlAvis);
$stmtAvis->bind_param("i", $id);
$stmtAvis->execute();
$resultAvis = $stmtAvis->get_result();

$avis = [];
while ($row = $resultAvis->fetch_assoc()) {
    $avis[] = $row;
}




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
        <a style="font-size: 14px;"  class="btnn" href="../index.php"><i class="fas fa-user"></i></a>

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
  <div class="btn-reserv">
      
       
  <?php if (isset($_SESSION['utilisateur']) && strtolower($_SESSION['utilisateur']['role']) === 'locataire'): ?>
    <button class="btn" id="ouvrirModal">Réserver</button>
  <?php else: ?>
    <button class="btn" onclick="alert('S’il vous plaît créez un compte ou connectez-vous comme locataire pour réserver.');">Réserver</button>
  <?php endif; ?>


        
    </div>
    </div>

   <section class="testimonial-section">
    <h2>Que disent nos clients ?</h2>
    <p>Écoutez nos clients satisfaits.</p>
<div class="testimonial-wrapper">
    <div class="testimonial-container" id="testimonial-list">
  <?php foreach ($avis as $avisItem): ?>
    <div class="testimonial">
      <div class="name"><?= htmlspecialchars($avisItem['nom']) ?></div>
      <div class="rating">
        <?php
          $note = floatval($avisItem['note']);
          echo str_repeat("★", floor($note));
          if ($note - floor($note) >= 0.5) echo "½";
        ?>
      </div>
      <div class="comment"><?= nl2br(htmlspecialchars($avisItem['commentaire'])) ?></div>
    </div>
  <?php endforeach; ?>
</div>
</div>

<br>
<div style="text-align: center;">
  <button class="scroll-btn" onclick="scrollTestimonials('left')"><</button>
  <button class="scroll-btn" onclick="scrollTestimonials('right')">></button>
</div>

<br>

    <?php if (isset($_SESSION['utilisateur']) && strtolower($_SESSION['utilisateur']['role']) === 'locataire'): ?>
  <form id="testimonial-form" method="post" action="ajouter_avis.php">
    <input type="hidden" name="id_annonce" value="<?= htmlspecialchars($annonce['id']) ?>">
    <input type="text" name="nom" placeholder="Nom" required />
    <input type="number" name="note" min="1" max="5" step="0.1" placeholder="Note (1-5)" required />
    <textarea name="commentaire" placeholder="Votre avis" required></textarea>
    <button  type="submit">Publier l'avis</button>
  </form>
<?php else: ?>
  <p style="color:  #5d76a9df;"> Veuillez <a style="text-decoration: none;color:  #5d76a9df; " href="/connexion.php">vous connecter</a> pour laisser un avis.</p>
<?php endif; ?>


  </section>

<br><br>
  <footer id="Propriétés">
  <div class="footer-div">
    
  <div>
      <h3>CONTACTS</h3>
      <p class="contact"><i class="fas fa-phone"></i> +213 712 35 46 78</p>
      <p class="contact"><i class="fas fa-envelope"></i>
  <a class="contact" href="https://mail.google.com/mail/?view=cm&to=mnhome.dz1@gmail.com" >mnhome.dz1@gmail.com</a></p>
    




      <p class="contact"><i class="fab fa-facebook"></i> <a class="contact" href="https://www.facebook.com/profile.php?id=61575951081216" >facebook.com/MN Home Dzz</a></p>
    </div>

    <div>
      <h3>PROPRIÉTÉS</h3>
      <p>● © 2025 NotreStartup</p>
      <p>● pour la location immobiliere</p>
     
    </div>

   

  </div>
</footer>
<div id="modalReservation" class="modal">
  <div class="modal-content">
    <span class="close" id="fermerModal">&times;</span>
    <h2>Réserver ce logement</h2>
    <form action="traiter_reservation.php" method="post">
      <input type="hidden" name="annonce_id" value="<?= htmlspecialchars($annonce['id']) ?>">
      <label for="date_debut">Date d'arrivée :</label>
      <input type="date" name="date_debut" required>
      <label for="date_fin">Date de départ :</label>
      <input type="date" name="date_fin" required>
      <button type="submit" class="btn">Valider la réservation</button>
    </form>
  </div>
</div>
<script>
  const modal = document.getElementById("modalReservation");
  const btn = document.getElementById("ouvrirModal");
  const span = document.getElementById("fermerModal");

  if (btn) {
    btn.onclick = function () {
      modal.style.display = "block";
    }
  }

  if (span) {
    span.onclick = function () {
      modal.style.display = "none";
    }
  }

  window.onclick = function (event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }

  function scrollTestimonials(direction) {
    const container = document.getElementById('testimonial-list');
    const scrollAmount = 300; // ajustable pour correspondre à un avis

    if (direction === 'left') {
      container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    } else {
      container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
  }



</script>

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
.btnn{
  background-color: #5D76A9;
        border-radius: 20px;
        padding: 10px 20px;
        color: aliceblue;
        border: none;
        cursor: pointer;
}
.btnn:hover{
  background-color: rosybrown;
  font-size:13.5px;
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
  .modal {
  display: none;
  position: fixed;
  z-index: 999;
  left: 0; top: 0;
  width: 100%; height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
  background-color: #fff;
  margin: 10% auto;
  padding: 30px;
  border-radius: 10px;
  width: 90%;
  max-width: 500px;
  position: relative;
  
}

.close {
  color: #aaa;
  font-size: 28px;
  position: absolute;
  right: 20px;
  top: 10px;
  cursor: pointer;
}

.close:hover {
  color: black;
}
.modal-content form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.modal-content label {
  font-weight: bold;
  margin-bottom: 5px;
}

.modal-content input[type="date"] {
  padding: 8px;
  border-radius: 5px;
  border: 1px solid #ccc;
}

.modal-content .btn {
  margin-top: 10px;
  align-self: flex-end; 
}
.testimonial-wrapper {
  overflow: hidden;
  max-width: 880px; /* 3 * 280px + gap (~20px * 2) */
  margin: auto;
}
.testimonial-section {
  text-align: center;
  max-width: 900px;
  margin: auto;
}

.testimonial-container {
  display: flex;
  overflow-x: auto;
  scroll-behavior: smooth;
  gap: 20px;
  padding: 10px 0;
  margin: 20px auto;
  max-width: 100%;
}

.testimonial-container::-webkit-scrollbar {
  height: 8px;
}
.testimonial-container::-webkit-scrollbar-thumb {
  background-color: #ccc;
  border-radius: 5px;
}


.testimonial {
  background: #ffffff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  display: flex;
  flex-direction: column;
  gap: 10px;
   min-width: 280px;
  flex: 0 0 auto;
}

.testimonial:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
}

.testimonial .name {
  font-weight: bold;
  color: #2c3e50;
  font-size: 1.1em;
}

.testimonial .rating {
  color: #f39c12;
  font-size: 1.2em;
}

.testimonial .comment {
  font-size: 0.95em;
  color: #444;
  line-height: 1.4;
}


.testimonial .name {
  font-weight: bold;
  color: #222;
}

.testimonial .rating {
  color: #f39c12;
}

form {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-width: 400px;
  margin: auto;
}

form input, form textarea {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  text-decoration: none;
}

form button {
  background-color: #5D76A9;
  border-radius: 10px;
  padding: 10px 40px;
  color: aliceblue;
  border: none;
  cursor: pointer;
}

form button:hover {
   background-color: rosybrown;
  font-size:13.5px;
}

.scroll-btn {
  background-color:rgba(101, 103, 108, 0.58);
  color: black;
  border: none;
  padding: 10px 20px;
  margin: 10px;
  border-radius: 50px 50px;
  cursor: pointer;
  font-size: 18px;
  transition: background-color 0.3s ease;
}

.scroll-btn:hover {
  background-color:rgb(101, 103, 108);
}

</style>


</html>
