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
    $conn->query("UPDATE annonce SET valide = 1 WHERE id = $id");
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

    $conn->query("DELETE FROM annonce WHERE id = $id");
    header("Location: admin.php#annonces");
    exit;
}
// Supprimer un compte utilisateur
if (isset($_GET['delete_id'])) {
  $id = intval($_GET['delete_id']);
  $conn->query("DELETE FROM utilisateur WHERE id = $id");
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
  <h2>Tous les comptes</h2>
<div class="ancs">
    <?php while ($row = $result->fetch_assoc()) : ?>
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
  $sql = "SELECT * FROM annonce WHERE valide = 0 ORDER BY id DESC";
  $result = $conn->query($sql);

  if ($result->num_rows > 0): ?>
    <div class="ancs">
      <?php while ($row = $result->fetch_assoc()): ?>
    
        <div class="annonc" onclick="window.location.href='detailannc.php?id=<?= $row['id'] ?>';">
          <div class="image">
            <img class="img" src="../annonces/<?= htmlspecialchars($row['photos']) ?>" alt="img">
          </div>
          <p class="nom1"><?= htmlspecialchars($row['titre']) ?></p>
          <a class="button1" href="admin.php?valider_id=<?= $row['id'] ?>#annonces" >Valider</a>
<a href="admin.php?delete_annonce=<?= $row['id'] ?>#annonces" class="btn-supp" onclick="return confirm('Supprimer cette annonce ?')">
    <i class="fas fa-trash-alt"></i>
</a>


        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p>Aucune annonce en attente de validation.</p>
  <?php endif; ?>


 
  <h2 style="margin-top: 40px;">Toutes les annonces validées</h2>
  <?php
  $sql2 = "SELECT * FROM annonce WHERE valide = 1 ORDER BY id DESC";
  $result2 = $conn->query($sql2);

  if ($result2->num_rows > 0): ?>
    <div class="ancs">
      <?php while ($row = $result2->fetch_assoc()): ?>
        <div class="annonc" onclick="window.location.href='detailannc.php?id=<?= $row['id'] ?>';">
          <div class="image">
            <img class="img" src="../annonces/<?= htmlspecialchars($row['photos']) ?>" alt="img">
          </div>
          <p class="nom1"><?= htmlspecialchars($row['titre']) ?></p>
<a href="admin.php?delete_annonce=<?= $row['id'] ?>#annonces" class="btn-supp" onclick="return confirm('Supprimer cette annonce ?')">
    <i class="fas fa-trash-alt"></i>
</a>

        </div>
 

      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p>Aucune annonce validée pour le moment.</p>
  <?php endif;

  $conn->close();
  ?>
</div>

</div>
  </div>



       
    <div id="reservations" class="tab">
    <h2 >Valider le payement</h2>
        <div class="annonc" >
                   <div class="image">
                       <img class="img" src="../images/273818788.jpg" alt="img">
                    </div>
                <p class="nom">Nom de l'annonce</p>
                <p class="date">Date de l'annonce</p>
                <button class="button1">Valider</button>
                </div>
            </div>
    </div>
  </main>

  
  <div id="message-suppression" style="display: none;"></div>

<div id="message-suppression" class="message" style="display: none;"></div>

  <style>
    body {
      margin: 0;
      font-family: sans-serif;
      background: #f8fafc;
      color: #111827;
    }

    .tab-nav {
      display: flex;
      background-color:rgba(28, 42, 89, 0.89);
      overflow-x: auto;
    }

    .tab-nav button {
      flex: 1;
      background-color:rgba(28, 42, 89, 0.89);
      border: none;
      color: white;
      padding: 16px;
      cursor: pointer;
      font-size: 16px;
      transition: background 0.3s;
      border-bottom: 3px solid transparent;
    }

    .tab-nav button:hover,
    .tab-nav button.active {
      background: #374151;
      font-size: 18px;

    
    }

    .content {
      padding: 2rem;
    }

    .tab {
      display: none;
    }

    .tab.active {
      display: block;
    }
    .ancs{
        display: grid;
        gap:7px;
        max-width: 98%;
        margin:1%;
    }
   
   
    .annonc:hover{
        transform: translateY(-2px);
        box-shadow: 0 5px 15px #5D76A9;

    }
    .annonc {
  display: flex;
  align-items: center;
  border: 1px solid #3a3a3ab9;
  border-radius: 10px;
  gap: 15px;
  padding: 5px;
  cursor: pointer;
  transition: transform 0.3s ease;
  background-color: white;
}

.image {
  flex-shrink: 0;
}

.img {
  width: 80px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
}

.nom {
  font-size: 16px;
  font-weight: bold;
  flex-grow: 1;
}

    .nom1{
      font-size: 16px;
      font-weight: bold;
      flex-grow: 1;

    }
    .btn-supp{
        padding:15px;
        border-radius:50px;
        border:none;
        margin-right:15px;

    }
    .btn-supp:hover{
        cursor: pointer;
        background-color:rgba(255, 0, 0, 0.86);
        border:1px solid black;
        font-size:15px;
    }
    .button1 {
 border-radius: 20px;
  padding: 10px 20px;
  color: aliceblue;
  background-color: #5D76A9;
  cursor: pointer;
  border: none;
  text-decoration: none;


    }
    button:hover {
  background-color: rosybrown;
  font-size:16px;

    }
    .button1:hover {
  background-color: rosybrown;
  font-size:16px;

    }

    #message-suppression {
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%); 
    background-color: rgba(5, 85, 5, 0.84);
    color: white;
    padding: 15px 25px;
    border-radius: 15px;
    font-size: 16px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.42);
    animation: fadeinout 3s ease forwards; 
    }
    
  </style>


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

</body>
</html>
