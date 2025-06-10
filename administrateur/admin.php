<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php"); // Redirection si non connecté
    exit();
}

$admin = $_SESSION['admin'];


$conn = new mysqli('localhost', 'root', '', 'location_immobiliere');
if ($conn->connect_error) die("Erreur de connexion : " . $conn->connect_error);

// Valider une annonce
if (isset($_GET['valider_id'])) {
    $id = intval($_GET['valider_id']);
    $conn->query("UPDATE annonce SET valide = 1, statut = 'publie' WHERE id = $id");
    header("Location: admin.php#annonces");
    exit;
}

// Supprimer une annonce
if (isset($_GET['delete_annonce'])) {
    $id = intval($_GET['delete_annonce']);

    // Supprimer l'image du serveur (optionnel mais recommandé)
    $result = $conn->query("SELECT photos FROM annonce WHERE id = $id");
    if ($result && $row = $result->fetch_assoc()) {
        $imagePath = "../annonces/" . $row['photos'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // 1. Supprimer les favoris liés à cette annonce (éviter erreur clé étrangère)
    $stmt1 = $conn->prepare("DELETE FROM favoris WHERE annonce_id = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    $stmt1->close();

    // 2. Supprimer l'annonce
    $stmt2 = $conn->prepare("DELETE FROM annonce WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $stmt2->close();

    header("Location: admin.php#annonces");
    exit;
}

// Supprimer un compte utilisateur
if (isset($_GET['delete_id'])) {
  $id = intval($_GET['delete_id']);
if (isset($_GET['delete_id'])) {
  $id = intval($_GET['delete_id']);

  // Supprimer d'abord les favoris liés à ce locataire
  $stmt1 = $conn->prepare("DELETE FROM favoris WHERE locataire_id = ?");
  $stmt1->bind_param("i", $id);
  $stmt1->execute();
  $stmt1->close();

  // Supprimer ses réservations (optionnel mais souvent nécessaire)
  $stmt2 = $conn->prepare("DELETE FROM reservation WHERE locataire_id = ?");
  $stmt2->bind_param("i", $id);
  $stmt2->execute();
  $stmt2->close();

  // Enfin, supprimer le compte utilisateur
  $stmt3 = $conn->prepare("DELETE FROM utilisateur WHERE id = ?");
  $stmt3->bind_param("i", $id);
  $stmt3->execute();
  $stmt3->close();

  echo "<script>
      alert('Compte supprimé avec succès');
      window.location.href = 'admin.php#comptes';
  </script>";
}
  header("Location: admin.php#comptes");
  exit;
}

// Suppression utilisateur
if (isset($_GET['delete_id'])) {
  $id = intval($_GET['delete_id']);
  $conn->query("DELETE FROM utilisateur WHERE id = $id");
  echo "<script>
      alert('Compte supprimé avec succès');
      window.location.href = 'admin.php';
  </script>";
}



// Afficher les comptes  

$sql = "SELECT id, nom, prenom, photo FROM utilisateur
      WHERE email != ('mnhome.dz1@gmail.com')";
$result = $conn->query($sql);


$reservations = $conn->query("
    SELECT r.*, a.titre, a.photos,
           loc.nom AS loc_nom, loc.prenom AS loc_prenom,
           prop.nom AS prop_nom, prop.prenom AS prop_prenom
    FROM reservation r
    JOIN annonce a ON r.annonce_id = a.id
    JOIN utilisateur loc ON r.locataire_id = loc.id
    JOIN utilisateur prop ON a.proprietaire_id = prop.id
    WHERE r.statut = 'valide'
    AND TIMESTAMPDIFF(MINUTE, r.date_debut, NOW()) >= 2
    ORDER BY r.id DESC
");

$proprietaires = $conn->query("SELECT id, nom, prenom, photo FROM utilisateur WHERE role = 'proprietaire' AND email != 'mnhome.dz1@gmail.com'");
$locataires = $conn->query("SELECT id, nom, prenom, photo FROM utilisateur WHERE role = 'locataire'");




?>


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="admin.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <title>Administrateur</title>
  
</head>
<body>

  <div class="tab-nav">
    <button class="tab-link" onclick="showTab('comptes', this)">Comptes</button>
    <button class="tab-link" onclick="showTab('annonces', this)">Annonces</button>
    <button class="tab-link" onclick="showTab('reservations', this)">Réservations</button>
    <a href="../logout.php"><button class="tab-link" onclick="showTab('retour', this)"><i class="fas fa-sign-out-alt"></i></button></a>
   
  </div>

  <main class="content">
    <div id="comptes" class="tab">
  <h2>Comptes Propriétaires :</h2>
  <div class="ancs">
    <?php while ($row = $proprietaires->fetch_assoc()) : ?>
      <div class="annonc">
        <div class="image">
          <img class="img" src="../logins/<?= htmlspecialchars($row['photo']) ?>" alt="img">
        </div>
        <p class="nom"><?= htmlspecialchars($row['nom'] . ' ' . $row['prenom']) ?></p>
        <a href="?delete_id=<?= $row['id'] ?>" class="btn-supp" onclick="return confirm('Supprimer ce compte ?')">
          <i class="fas fa-trash-alt"></i>
        </a>
      </div>
    <?php endwhile; ?>
  </div>

  <h2 style="margin-top: 40px;">Comptes Locataires :</h2>
  <div class="ancs">
    <?php while ($row = $locataires->fetch_assoc()) : ?>
      <div class="annonc">
        <div class="image">
          <img class="img" src="../logins/<?= htmlspecialchars($row['photo']) ?>" alt="img">
        </div>
        <p class="nom"><?= htmlspecialchars($row['nom'] . ' ' . $row['prenom']) ?></p>
        <a href="?delete_id=<?= $row['id'] ?>" class="btn-supp" onclick="return confirm('Supprimer ce compte ?')">
          <i class="fas fa-trash-alt"></i>
        </a>
      </div>
    <?php endwhile; ?>
  </div>
</div>


  <div id="annonces" class="tab">

  <!-- Annonces à valider -->
  <h2>Annonces à valider</h2>
  <?php
$sql = "
SELECT a.*, u.nom AS prop_nom, u.prenom AS prop_prenom 
FROM annonce a
JOIN utilisateur u ON a.proprietaire_id = u.id
WHERE a.valide = 0
ORDER BY a.id DESC
";
  $result = $conn->query($sql);
  

  if ($result->num_rows > 0): ?>
    <div class="ancs">
      <?php while ($row = $result->fetch_assoc()): ?>
    
        <div class="annonc" onclick="window.location.href='detailannc.php?id=<?= $row['id'] ?>';">
          <div class="image">
            <img class="img" src="../annonces/<?= htmlspecialchars($row['photos']) ?>" alt="img">
          </div>
          <div class="det">
            <div class="det-reser">
          <p class="nom"><?= htmlspecialchars($row['titre']) ?></p>
          <p class="nom1" style="margin-bottom: 0px;">Propriétaire : <?= htmlspecialchars($row['prop_nom'] . ' ' . $row['prop_prenom']) ?></p>
          </div>
          <a class="button1" href="admin.php?valider_id=<?= $row['id'] ?>#annonces"  >Valider</a>
          <a href="admin.php?delete_annonce=<?= $row['id'] ?>#annonces" class="btn-supp" onclick="return confirm('Supprimer cette annonce ?')">
            <i class="fas fa-trash-alt"></i>
          </a>
          </div>



        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p>Aucune annonce en attente de validation.</p>
  <?php endif; ?>


 
  <h2 style="margin-top: 40px;">Toutes les annonces validées</h2>
  <?php
$sql2 = "
    SELECT a.*, u.nom AS prop_nom, u.prenom AS prop_prenom 
    FROM annonce a
    JOIN utilisateur u ON a.proprietaire_id = u.id
    WHERE a.valide = 1
    ORDER BY a.id DESC
";
  $result2 = $conn->query($sql2);

  if ($result2->num_rows > 0): ?>
    <div class="ancs">
      <?php while ($row = $result2->fetch_assoc()): ?>
        <div class="annonc" onclick="window.location.href='detailannc.php?id=<?= $row['id'] ?>';">
          <div class="image">
            <img class="img" src="../annonces/<?= htmlspecialchars($row['photos']) ?>" alt="img">
          </div>
          <div class="det">
            <div class="det-reser">
          <p class="nom" style="margin-top: 1px;"><?= htmlspecialchars($row['titre']) ?></p>
          <p class="nom1" style="margin-bottom: 0px;">Propriétaire : <?= htmlspecialchars($row['prop_nom'] . ' ' . $row['prop_prenom']) ?></p>
          </div>

          <a href="admin.php?delete_annonce=<?= $row['id'] ?>#annonces" class="btn-supp" onclick="return confirm('Supprimer cette annonce ?')">
            <i class="fas fa-trash-alt"></i>
          </a>
          </div>

        </div>
 

      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p>Aucune annonce validée pour le moment.</p>
  <?php endif;

 
  ?>
</div>

</div>
  </div>



       
    <div id="reservations" class="tab">
    <h2>Réservations à validées le payement</h2>
    <?php
   


    if ($reservations && $reservations->num_rows > 0): ?>
        <div class="ancs">
            <?php while ($res = $reservations->fetch_assoc()): ?>
                <div class="annonc">
                    <div class="image">
                        <img class="img" src="../annonces/<?= htmlspecialchars($res['photos']) ?>" alt="img">
                    </div>
                    <div class="det">
                      <div class="det-reser">
                        <p class="nom" style="margin-top: 0px;"><?= htmlspecialchars($res['titre']) ?></p>
                        <p class="nom1">Locataire : <?= htmlspecialchars($res['loc_nom'] . ' ' . $res['loc_prenom']) ?></p>
                        <p class="nom1" style="margin-bottom: 0px;">Propriétaire : <?= htmlspecialchars($res['prop_nom'] . ' ' . $res['prop_prenom']) ?></p>

                      </div>

                    <span class="button1" style="pointer-events: none; margin-right:-7px;">Valider</span>
                    <span class="button1" style="pointer-events: none; margin-right:12px;">Refuser</span>

                  </div>
                </div>

            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>Aucune réservation validée pour le moment.</p>
    <?php endif; ?>
</div>

    </div>
  </main>

  
  <div id="message-suppression" style="display: none;"></div>

<div id="message-suppression" class="message" style="display: none;"></div>

  <script>
    function showTab(tabId, btn) {
      // Masquer tous les contenus
      document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));

      // Afficher l'onglet actif
      document.getElementById(tabId).classList.add('active');

      // Mettre à jour les boutons d'onglets
      document.querySelectorAll('.tab-link').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    }

    
  </script>
<script>
window.addEventListener("load", () => {
  if (window.location.hash === "#annonces") {
    const btn = document.querySelector('button[onclick*="annonces"]');
    if (btn) showTab("annonces", btn);
  }
});
</script>
<script>
window.addEventListener("load", () => {
  const hash = window.location.hash;
  if (hash) {
    const tabId = hash.replace('#', '');
    const btn = document.querySelector(`.tab-link[onclick*="${tabId}"]`);
    if (btn) showTab(tabId, btn);
  }
});
</script>
 <?php 

  $conn->close();
  ?>
</body>
</html>
