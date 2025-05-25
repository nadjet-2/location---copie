<?php
session_start();
if (!isset($_SESSION['utilisateur']) || strtolower($_SESSION['utilisateur']['role']) !== 'locataire') {
    header("Location: ../index.php");
    exit();
}

// Connexion à la base de données
$host = 'localhost';
$db   = 'location_immobiliere';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

$id = $_SESSION['utilisateur']['id'];

$stmt = $conn->prepare("SELECT * FROM utilisateur WHERE id = ?");
$stmt->bind_param("i", $id); // Liaison de paramètre (i = integer)
$stmt->execute();

$result = $stmt->get_result();
$locataire = $result->fetch_assoc();

if (!$locataire) {
    echo "Utilisateur non trouvé.";
    exit();
}


$reservations = [];

$sql = "SELECT r.*, a.titre, a.photos, a.adresse
        FROM reservation r
        JOIN annonce a ON r.annonce_id = a.id
        WHERE r.locataire_id = ?
        ORDER BY r.date_reservation DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $reservations[] = $row;
}

$avis = [];

$sqlAvis = "SELECT av.*, a.titre, a.photos, a.adresse
            FROM avis av
            JOIN annonce a ON av.annonce_id = a.id
            WHERE av.locataire_id = ?
            ORDER BY av.date_avis DESC";

$stmtAvis = $conn->prepare($sqlAvis);
$stmtAvis->bind_param("i", $id);
$stmtAvis->execute();
$resultAvis = $stmtAvis->get_result();

while ($row = $resultAvis->fetch_assoc()) {
    $avis[] = $row;
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>profil - MN Home DZ</title>
      <link rel="stylesheet" href="locataire.css"/>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>
    
       
    <div class="nav-barre">
        <div>
          <img class="Logo" src="../images/Logo.png" alt="Logo" />
        </div>
        <div class="auth">
            <a  href="../index.php"><button class="button1">Acceuil</button></a>
            <a href="../logout.php"><button class="button2">Déconnexion</button></a>

        </div>
    </div>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="user-welcome">
                <img src="../logins/<?php echo htmlspecialchars($locataire['photo']); ?>" alt="Avatar" class="user-avatar">

                <div class="user-info">
                   <h1>Bonjour, <?php echo htmlspecialchars($locataire['prenom']) . '&nbsp;' . htmlspecialchars($locataire['nom']); ?> 👋</h1>
                   <span class="user-type">
                   <?php echo htmlspecialchars($locataire['role']); ?>
                    </span>

                </div>
            </div>

            <div class="dashboard-actions">
                <a href="" class="btn-action btn-secondary">
                    <i class="fas fa-user-edit"></i> Modifier le profil
                </a>

                    <a href="" class="btn-action btn-primary">
                        <i class="fas fa-bell"></i> Notifications
                    </a>

                   
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-main">
                

                <div class="dashboard-card" id="Reservations">
                    <div class="card-header" >
                        <h2 class="card-title" >Mes réservations</h2>
                        <a href="javascript:void(0);" class="card-action" id="showAllBookings">Voir tout</a>
                    </div>

                    <div class="card-content">                          
                            <div class="booking-list">
                            <?php if (empty($reservations)) : ?>
                                <div class="empty-state">
                                    <i class="fas fa-calendar-alt"></i>
                                    <h3>Aucune réservation</h3>
                                    <p>Vous n'avez pas encore effectué de réservation.</p>
                                </div>
                                <?php else : ?>
                                <div class="booking-list">
                                    <?php foreach ($reservations as $res) : ?>
                                    <div class="booking-item">
                                        <img src="../annonces/<?php echo htmlspecialchars($res['photos'] ?? 'placeholder.jpg'); ?>" alt="Annonce" class="booking-image">

                                        <div class="booking-details">
                                            <div class="booking-title"><?php echo htmlspecialchars($res['titre']); ?></div>

                                                <div class="booking-dates">
                                                    <i class="fas fa-calendar"></i>
                                                    Du <?php echo date('d/m/Y', strtotime($res['date_debut'])); ?>
                                                    au <?php echo date('d/m/Y', strtotime($res['date_fin'])); ?>
                                                </div>
                                                <?php 
                                                $statutClasse = '';
                                                $statutAffiche = '';
                                                switch (strtolower($res['statut'])) {
                                                    case 'annulé':
                                                    case 'annule':
                                                        $statutAffiche = 'Refusé';
                                                        $statutClasse = 'Refuse';
                                                    break;
                                                    case 'en attente':
                                                        $statutAffiche = 'En attente';
                                                        $statutClasse = 'EnAttente';
                                                    break;
                                                    case 'validé':  
                                                    case 'valide':
                                                        $statutAffiche = 'Validé';
                                                        $statutClasse = 'Valide'; 
                                                    break;
                                                    default:
                                                        $statutAffiche = htmlspecialchars($res['statut']);
                                                        $statutClasse = '';
                                                        break;
                                                    }
                                                ?>
                                                <span class="booking-status <?= $statutClasse ?>"><?= $statutAffiche ?></span>
                                            </div>

                                            <div class="booking-actions" style="display: flex; flex-direction: column; justify-content: center; padding: 0 15px;">
                                                <a href="../annonces/detail-annonce.php?id=<?= $res['annonce_id'] ?>" class="btn-action btn-secondary" style="margin-bottom: 10px;">
                                                <i class="fas fa-eye"></i> Détails
                                                </a>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>                                    
                                </div>   
                            </div>
                                     
                    </div>
                </div>
            </div>
               

                <div class="dashboard-card" id="Reviews">
                    <div class="card-header">
                        <h2 class="card-title">Mes avis</h2>
                        <a href="javascript:void(0);" class="card-action" id="showAllReviews">Voir tout</a>
                    </div>

                    <div class="card-content">
                            
                            <div class="review-list">
                                 <?php if (empty($avis)) : ?>
                                <div class="empty-state">
                                    <i class="fas fa-star"></i>
                                    <h3>Aucun avis</h3>
                                    <p>Vous n'avez pas encore laissé d'avis</p>
                                </div>
                                <?php else : ?>
                                    <div class="review-list">
                                    <?php foreach ($avis as $res) : ?>
                                    <div class="review-item">
                                        <img src="../annonces/<?php echo htmlspecialchars($res['photos'] ?? 'placeholder.jpg'); ?>" alt="Annonce" class="review-image">

                                        <div class="review-details">
                                            <div class="review-title"><?php echo htmlspecialchars($res['titre']); ?></div>
                                                <div class="review-location">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?php echo htmlspecialchars($res['adresse']); ?>
                                                </div>

                                            <div class="review-date">
                                                <i class="fas fa-calendar"></i>
                                                Avis laissé le : <?php echo date('d/m/Y', strtotime($res['date_avis'])); ?>
                                            </div>

                                            <div class="review-rating">
                                                <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                <i class="fas fa-star<?= $i <= $res['note'] ? '' : '-o' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>

                                        <div class="review-actions" style="display: flex; flex-direction: column; justify-content: center; padding: 0 15px;">
                                            <a href="../annonces/detail-annonce.php?id=<?= $res['annonce_id'] ?>" class="btn-action btn-secondary" style="margin-bottom: 10px;">

                                                <i class="fas fa-eye"></i> Voir
                                             </a>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>                                    
                                </div>   
                            </div>
                                     
                    </div>
                </div>
            </div>

                
                 <div class="dashboard-card" id="Reservations">
                    <div class="card-header" >
                        <h2 class="card-title" >Annonces sauvgardées</h2>
                        <a href="javascript:void(0);" class="card-action" id="showAllsauv">Voir tout</a>
                    </div>

                    <div class="card-content">
                            <div class="empty-state">
                                <i class="fas fa-calendar-alt"></i>
                                <h3>Aucune annonces</h3>
                                <p>Vous n'avez pas encore sauvgarder des annonces.</p>
                            </div>
                            <div class="saved-list" >
                                <div class="review-list">
                                
                                    <div class="review-item">
                                        <img src="" alt="" class="review-image">

                                        <div class="review-details">
                                            <div class="review-title">Titre annonce</div>

                                            <div class="review-location">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </div>

                                            <div class="review-date">
                                                <i class="fas fa-calendar"></i>
                                                Annonce sauvegarder le :  
                                            </div>

                                            
                                        </div>

                                        <div class="review-actions" style="display: flex; flex-direction: column; justify-content: center; padding: 0 15px;">
                                            <a href="" class="btn-action btn-secondary">
                                                <i class="fas fa-eye"></i> Voir
                                            </a>
                                        </div>
                                    </div>
                                    
                            </div>
                            

                                   
                                     
                            </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <p>&copy; <?php echo date('Y'); ?> MN Home DZ. Tous droits réservés.</p>
        </div>
    </footer>
</body>
 
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Fonction pour cacher les éléments après le 2ème
    function limitItems(containerSelector, itemSelector, buttonSelector) {
        const items = document.querySelectorAll(containerSelector + " " + itemSelector);
        const button = document.querySelector(buttonSelector);

        if (items.length <= 2) {
            if (button) button.style.display = "none"; // Pas besoin du bouton
            return;
        }

        // Cacher tous sauf les deux premiers
        for (let i = 2; i < items.length; i++) {
            items[i].style.display = "none";
        }

        // Afficher tous au clic
        if (button) {
            button.addEventListener("click", function () {
                items.forEach(item => item.style.display = "flex");
                button.style.display = "none"; // Cacher le bouton
            });
        }
    }

    // Appliquer aux réservations
    limitItems(".booking-list", ".booking-item", "#showAllBookings");

    // Appliquer aux avis
    limitItems(".review-list", ".review-item", "#showAllReviews");

    // Appliquer aux sauvgarder
    limitItems(".saved-list", ".saved-item", "#showAllsauv");
});
</script>

</html>
