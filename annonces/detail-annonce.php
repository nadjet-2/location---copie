<?php
session_start();
?>
<?php if (isset($_SESSION['reservation_message'])): ?>
  <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px auto; max-width: 800px; text-align: center; font-weight: bold;">
    <?= htmlspecialchars($_SESSION['reservation_message']) ?>
  </div>
  <?php unset($_SESSION['reservation_message']); ?>
<?php endif; ?>

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

$reservation = null;

if (isset($_SESSION['utilisateur']) && strtolower($_SESSION['utilisateur']['role']) === 'locataire') {
    $sqlRes = "SELECT * FROM reservation WHERE annonce_id = ? AND statut = 'valide' LIMIT 1";
    $stmtRes = $conn->prepare($sqlRes);
    $stmtRes->bind_param("i", $id);
    $stmtRes->execute();
    $resultRes = $stmtRes->get_result();
    $reservation = $resultRes->fetch_assoc();
    $stmtRes->close();
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
  <link rel="stylesheet" href="detail-annonce.css"/>
</head>
<body>

<nav class="nav-barre">
    <div>
      <img class="Logo" src="../images/Logo.png" alt="Logo" />
    </div>
        <a style="font-size: 14px;"  class="btnn" href="../index.php"><i class="fas fa-arrow-right"></i></a>

  </nav>

        <?php if ($reservation): ?>
  <?php
    $now = date('Y-m-d');
    if ($reservation['date_fin'] >= $now):
  ?>
    <div class="reservation-dates" style="margin: -40px 0px 50px 0px; padding: 10px; background: #f0f0f0; border-radius: 5px;">
      <strong>Ce logement est réservé du <?= htmlspecialchars($reservation['date_debut']) ?> au <?= htmlspecialchars($reservation['date_fin']) ?>.</strong>
    </div>
  <?php endif; ?>
<?php endif; ?>
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
<?php
  $canReserve = true;
  if ($reservation && $reservation['date_fin'] >= date('Y-m-d')) {
      $canReserve = false;
  }
?>
<?php if ($canReserve): ?>
  <button class="btn" id="ouvrirModal">Réserver</button>
<?php else: ?>
  <button class="btn" disabled style="opacity: 0.6;">Déjà réservé</button>
<?php endif; ?>
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
    <input type="text" name="nom" placeholder="Nom" value="<?= htmlspecialchars($_SESSION['utilisateur']['nom'] . ' ' . $_SESSION['utilisateur']['prenom']) ?>" required readonly />
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
    <?php if (isset($_GET['message'])): ?>
  <p style="color: green; text-align: center;"><?= htmlspecialchars($_GET['message']) ?></p>
<?php endif; ?>

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




</html> 