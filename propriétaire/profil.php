<?php
session_start();

if (!isset($_SESSION['utilisateur'])) {
    header("Location: ../index.php"); // Redirection si non connecté
    exit();
}

$utilisateur = $_SESSION['utilisateur'];

// Connexion à la base de données
$host = 'localhost';
$db   = 'location_immobiliere';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}
$utilisateur_id = $_SESSION['utilisateur']['id'];
$sql_annonces = "SELECT * FROM annonce WHERE proprietaire_id = ?";
$stmt = $conn->prepare($sql_annonces);
$stmt->bind_param("i", $utilisateur_id);
$stmt->execute();
$result = $stmt->get_result();



if (isset($_GET['delete_annonce'])) {
    $id = intval($_GET['delete_annonce']);

    // Supprimer l'image du serveur
    $res = $conn->query("SELECT photos FROM annonce WHERE id = $id");
    if ($res && $row = $res->fetch_assoc()) {
        $images = explode(',', $row['photos']);
        foreach ($images as $image) {
            $imagePath = "../annonces/" . trim($image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
    }

// Supprimer les favoris associés à l'annonce
$conn->query("DELETE FROM favoris WHERE annonce_id = $id");

// Puis supprimer l'annonce
$conn->query("DELETE FROM annonce WHERE id = $id");

    // Redirection vers la même page
    header("Location: profil.php");
    exit;
}


// Traitement de la mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $tel = $_POST['tel'];

    $photoPath = $utilisateur['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $targetDir = "../logins/";
        $fileName = basename($_FILES["photo"]["name"]);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
            $photoPath = $fileName;
        }
    }

    $stmt = $conn->prepare("UPDATE utilisateur SET prenom=?, nom=?, email=?, tel=?, photo=? WHERE id=?");
    $stmt->bind_param("sssssi", $prenom, $nom, $email, $tel, $photoPath, $utilisateur_id);
    if ($stmt->execute()) {
        $_SESSION['utilisateur']['prenom'] = $prenom;
        $_SESSION['utilisateur']['nom'] = $nom;
        $_SESSION['utilisateur']['email'] = $email;
        $_SESSION['utilisateur']['tel'] = $tel;
        $_SESSION['utilisateur']['photo'] = $photoPath;

        echo "<script>alert('Profil mis à jour avec succès !'); window.location.href='profil.php';</script>";
        exit;
    } else {
        echo "<script>alert('Erreur lors de la mise à jour.');</script>";
    }
}

// Requête pour récupérer les réservations des annonces du propriétaire connecté
$sql_reservations = "
    SELECT r.*, a.titre, a.photos, u.prenom AS loc_prenom, u.nom AS loc_nom, u.role AS loc_role
    FROM reservation r
    JOIN annonce a ON r.annonce_id = a.id
    JOIN utilisateur u ON r.locataire_id = u.id
    WHERE a.proprietaire_id = ?
    AND r.statut != 'annule'
    ORDER BY r.date_reservation DESC

";


$stmt_res = $conn->prepare($sql_reservations);
$stmt_res->bind_param("i", $utilisateur_id);
$stmt_res->execute();
$result_reservations = $stmt_res->get_result();






// Annonces à actualiser (créées il y a plus de 5 minutes)
$sql_annonces_a_actualiser = "
    SELECT * FROM annonce 
    WHERE proprietaire_id = ? 
    AND DATE_ADD(date_creation, INTERVAL 5 MINUTE) <= NOW()
";
$stmt_actualisation = $conn->prepare($sql_annonces_a_actualiser);
$stmt_actualisation->bind_param("i", $utilisateur_id);
$stmt_actualisation->execute();
$result_actualisation = $stmt_actualisation->get_result();



if (isset($_GET['actualiser_annonce'])) {
    $id = intval($_GET['actualiser_annonce']);
    $stmt = $conn->prepare("UPDATE annonce SET date_creation = NOW() WHERE id = ? AND proprietaire_id = ?");
    $stmt->bind_param("ii", $id, $utilisateur_id);
    if ($stmt->execute()) {
        header("Location: profil.php#actualisations");
        exit;
    }
}



$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $sql_annonces = "
        SELECT * FROM annonce 
        WHERE proprietaire_id = ?
        AND (
            SOUNDEX(adresse) = SOUNDEX(?) 
            OR adresse LIKE ? 
            OR titre LIKE ?
        )
    ";
    $like = '%' . $search . '%';
    $stmt = $conn->prepare($sql_annonces);
    $stmt->bind_param("isss", $utilisateur_id, $search, $like, $like);
} else {
    $sql_annonces = "SELECT * FROM annonce WHERE proprietaire_id = ? ORDER BY date_creation DESC";
    $stmt = $conn->prepare($sql_annonces);
    $stmt->bind_param("i", $utilisateur_id);
}

$stmt->execute();
$result = $stmt->get_result();

?>



<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="profil.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>
    <div class="nav-barre">
        <nav>
        <div >
            <img  class="Logo" src="../images/Logo.png" alt="Logo">
        </div>
        
        <div class="btn">      
            <a  href="../index.php"><button class="creer">Acceuil</button></a>
            <a href="../annonces/annonce.php"><button class="creer">Créer une annonce</button></a>


            <button class="profil"><i class="fas fa-user"></i></button>
            
        </div>
        </nav>
        
    </div>

    <div class="tab-nav">
    <button class="tab-link" onclick="showTab('annonces', this)">Annonces</button>
    <button class="tab-link" onclick="showTab('reservations', this)">Réservations</button>
    <button class="tab-link" onclick="showTab('actualisations', this)">Actualisation des annonces </button>

    </div>

    <main class="content">
    <div id="annonces" class="tab">

    <div id="Rechercher" class="barre-de-recherche">
           <input class="bdr" type="text" id="searchInput" placeholder="Rechercher par adresse..." />

        </div>

        <div class="text">
            <div class="titre">
                <span class="ligne"></span>
                <h4>Mes annonces</h4>
                <span class="ligne"></span>
            </div>
        </div>




        <div class="ancs">

<?php while ($annonce = $result->fetch_assoc()): ?>
    <div class="annonc" onclick="window.location.href='detail_bien.php?id=<?= $annonce['id'] ?>';">
        <div class="image">
            <?php
            $images = explode(',', $annonce['photos']);
            $imagePath = $images[0] ?? '../images/default.jpg';
            ?>
            <img class="img" src="../annonces/<?= htmlspecialchars($imagePath) ?>" alt="img">
        </div>
        <p class="nom"><?= htmlspecialchars($annonce['titre']) ?></p>
        <?php
    $statut = $annonce['statut'];
    $classeStatut = $statut === 'publie' ? 'statut-publie' : 'statut-attente';
    $texteStatut = $statut === 'publie' ? 'Publié' : 'En attente';
?>
<p class="statut <?= $classeStatut ?>"><?= $texteStatut ?></p>

        
        <a href="profil.php?delete_annonce=<?= $annonce['id'] ?>#annonces" class="btn-supp" onclick="return confirm('Supprimer cette annonce ?')">
    <i class="fas fa-trash-alt"></i>
        </a>
    </div>
<?php endwhile; ?>
</div>
</div>

<div id="reservations" class="tab">
    <div class="ancs">
        <h3>Toutes les réservations</h3>

        <?php while ($reservation = $result_reservations->fetch_assoc()): ?>
            <?php
                $images = explode(',', $reservation['photos']);
                $imagePath = $images[0] ?? '../images/default.jpg';
            ?>
            <div class="annonc" onclick="window.location.href='detail_bien.php?id=<?= $reservation['annonce_id'] ?>';">
                <div class="image">
                    <img class="img" src="../annonces/<?= htmlspecialchars($imagePath) ?>" alt="img">
                </div>
                <div class="det">
                <div class="det-reser">
                <p class="nom" style="margin-top: 0;"><?= htmlspecialchars($reservation['titre']) ?></p>
                <p class="nom1">Demande de réservations par : <?= htmlspecialchars($reservation['loc_nom']) ?> <?= htmlspecialchars($reservation['loc_prenom']) ?> </p>
                <p class="nom1" style="margin-bottom: 0;">Du <?= htmlspecialchars($reservation['date_debut']) ?> au <?= htmlspecialchars($reservation['date_fin']) ?></p>

                </div>
                <div class="v-a">
                <p class="date"><?= htmlspecialchars($reservation['date_reservation']) ?></p>
                <div class="btnn" style="margin-left: 15px;">
              
                    <?php if ($reservation['statut'] !== 'valide'): ?>
    <button class="act" onclick="event.stopPropagation(); if(confirm('Valider cette réservation ?')) { window.location.href='changer-statut-reservation.php?id=<?= $reservation['id'] ?>&action=valide'; }">Valider</button>
    <button class="act"  onclick="event.stopPropagation(); if(confirm('Annuler cette réservation ?')) { window.location.href='changer-statut-reservation.php?id=<?= $reservation['id'] ?>&action=annule'; }">Annuler</button>
<?php else: ?>
    <span class="badge-validée">✔ Réservation validée</span>
<?php endif; ?>

                </div>
                </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>


    <div id="actualisations" class="tab">
    <div class="ancs">
        <h3>Annonces à actualiser</h3>

        <?php while ($annonce = $result_actualisation->fetch_assoc()): ?>
            <?php
                $images = explode(',', $annonce['photos']);
                $imagePath = $images[0] ?? '../images/default.jpg';
            ?>
            <div class="annonc" onclick="window.location.href='detail_bien.php?id=<?= $annonce['id'] ?>';">
                <div class="image">
                    <img class="img" src="../annonces/<?= htmlspecialchars($imagePath) ?>" alt="img">
                </div>
                <p class="nom"><?= htmlspecialchars($annonce['titre']) ?></p>
                <p class="date">Créée le : <?= htmlspecialchars(date('d/m/Y', strtotime($annonce['date_creation']))) ?></p>
                <a href="profil.php?actualiser_annonce=<?= $annonce['id'] ?>#actualisations" class="act" onclick="event.stopPropagation(); return confirm('Actualiser cette annonce ?');"style="text-decoration: none; ">Actualiser</a>
            </div>
        <?php endwhile; ?>
    </div>
</div>



</main>



    <div class="panneau" id="panneau">
        <div class="panneau-header">
        
    
        <img class="photo_prfl" src="../logins/<?php echo htmlspecialchars($utilisateur['photo'] ?? 'default-avatar.png'); ?>" alt="photo">


            <h3>Mon Profil</h3>
            <button class="pann-ferm" id="pannfermbtn">✖️</button>
        </div>
       <div class="panneau-cont">
    <div id="profilInfos">
        <ul>
            <li><a href=""><?php echo htmlspecialchars($utilisateur['nom'] . ' ' . $utilisateur['prenom']); ?></a></li>
            <li><a href=""><?php echo htmlspecialchars($utilisateur['email']); ?></a></li>
            <li><a href=""><?php echo isset($utilisateur['tel']) ? htmlspecialchars($utilisateur['tel']) : 'Non renseigné'; ?></a></li>
            <li><a href=""><?php echo htmlspecialchars($utilisateur['role']); ?></a></li>
            <li><a class="a" href="../logout.php">Déconnexion</a></li>
        </ul>
        <button class="act1" id="editBtn">Modifier</button>
    </div>

    <div id="editForm" style="display: none;">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="update_profile" value="1">

            <label>Nom :</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($utilisateur['nom']) ?>" required><br><br>

            <label>Prénom :</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($utilisateur['prenom']) ?>" required><br><br>

            <label>Email :</label>
            <input type="email" name="email" value="<?= htmlspecialchars($utilisateur['email']) ?>" required><br><br>

            <label>Téléphone :</label>
            <input type="text" name="tel" value="<?= htmlspecialchars($utilisateur['tel']) ?>"><br><br>

            <label>Photo :</label>
            <input type="file" name="photo"><br><br>

            <div class="modif">
            <button type="submit" class="act">Enregistrer</button>
            <button type="button" class="act" id="cancelEdit">Annuler</button>
            </div>
        </form>
    </div>
</div>



    </div>
   

    <div id="message-suppression" style="display: none;"></div>

    <div id="message-suppression" class="message" style="display: none;"></div>












 



<script>
    const profilBtn = document.querySelector('.profil');
    const profilMenu = document.getElementById('profilMenu');
    profilBtn.addEventListener('click', () => {
        profilMenu.style.display = (profilMenu.style.display === 'block') ? 'none' : 'block';
    });
    document.addEventListener('click', function(e) {
        if (!profilBtn.contains(e.target) && !profilMenu.contains(e.target)) {
            profilMenu.style.display = 'none';
        }
    });

   

// Ouvrir le panneau latéral quand on clique sur le bouton "profil"
document.querySelector('.profil').addEventListener('click', function (e) {
    e.stopPropagation();
    document.getElementById('panneau').classList.add('open');

    // Optionnel : cacher le menu si jamais il s'affiche encore
    document.getElementById('profilMenu').style.display = 'none';
});


// Fermer le panneau latéral
    document.getElementById('pannfermbtn').addEventListener('click', function() {
        document.getElementById('panneau').classList.remove('open');
        });



// Supprimer une annonce et afficher le message
    document.querySelectorAll('.btn-supp').forEach((btn) => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation(); //empecher la redirection
            const annonce = this.closest('.annonc');
            if (!annonce) return;
            const confirmation =confirm("Es-tu sur de vouloir supprimer cette annonce?"); 
            if (confirmation) {
                annonce.remove();

// Affiche le message
                const msg = document.getElementById('message-suppression');
                msg.textContent = " Annonce supprimée avec succès";
                msg.style.display = 'block';

// Cache le message après 3 secondes
                setTimeout(() => {
                    msg.style.display = 'none';
                }, 5000);
            }
        });
    });
    
    

    function showTab(tabId, btn) {
      // Masquer tous les contenus
      document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));

      // Afficher l'onglet actif
      document.getElementById(tabId).classList.add('active');

      // Mettre à jour les boutons d'onglets
      document.querySelectorAll('.tab-link').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    }

    window.onload = function () {
  showTab('annonces', document.querySelector('.tab-link')); 
};

document.getElementById('editBtn').addEventListener('click', function () {
    document.getElementById('profilInfos').style.display = 'none';
    document.getElementById('editForm').style.display = 'block';
});

document.getElementById('cancelEdit').addEventListener('click', function () {
    document.getElementById('editForm').style.display = 'none';
    document.getElementById('profilInfos').style.display = 'block';
});


document.getElementById('searchInput').addEventListener('input', function() {
    const keyword = this.value;

    fetch('profil.php?search=' + encodeURIComponent(keyword))
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const annonces = doc.querySelector('#annonces .ancs').innerHTML;
            document.querySelector('#annonces .ancs').innerHTML = annonces;
        });
});






window.onload = function () {
    const hash = window.location.hash || '#annonces';
    const tabId = hash.replace('#', '');
    const btn = document.querySelector(`.tab-link[onclick*="${tabId}"]`);
    if (btn) showTab(tabId, btn);
};

</script>





</body>
</html>