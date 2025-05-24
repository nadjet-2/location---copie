<?php
session_start();
if (!isset($_SESSION['utilisateur']) || strtolower($_SESSION['utilisateur']['role']) !== 'locataire') {
    header("Location: ../index.php");
    exit();
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
        <div >
            <img  class="Logo" src="../images/Logo.png" alt="Logo">
        </div>
        <div class="auth">
            <a  href="../index.php"><button class="button1">Acceuil</button></a>
            <a href="../logout.php"><button class="button2">Déconnexion</button></a>

        </div>
    </div>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="user-welcome">
                <img src=" alt="class="user-avatar">
                <div class="user-info">
                    <h1>Bonjour,</h1>
                    <span class="user-type">
                     Ameeelll
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
                            <div class="empty-state">
                                <i class="fas fa-calendar-alt"></i>
                                <h3>Aucune réservation</h3>
                                <p>Vous n'avez pas encore effectué de réservation.</p>
                            </div>
                            <div class="booking-list">
                               

                                    <div class="booking-item">
                                        <img src="" alt="<" class="booking-image">

                                        <div class="booking-details">
                                            <div class="booking-title"></div>

                                            <div class="booking-location">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </div>

                                            <div class="booking-dates">
                                                <i class="fas fa-calendar"></i>
                                            </div>

                                            <div class="booking-price">
                                            </div>

                                            <span class="booking-status" style="margin-left: -40px;">En attente
                                            </span>
                                        </div>

                                        <div class="booking-actions" style="display: flex; flex-direction: column; justify-content: center; padding: 0 15px;">
                                            <a href="" class="btn-action btn-secondary" style="margin-bottom: 10px;">
                                                <i class="fas fa-eye"></i> Détails
                                            </a>
                                        </div>
                                    </div>
                                     
                            </div>
                             <div class="booking-list">
                               

                                    <div class="booking-item">
                                        <img src="" alt="<" class="booking-image">

                                        <div class="booking-details">
                                            <div class="booking-title"></div>

                                            <div class="booking-location">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </div>

                                            <div class="booking-dates">
                                                <i class="fas fa-calendar"></i>
                                            </div>

                                            <div class="booking-price">
                                            </div>

                                            <span class="booking-status" style="margin-left: -40px;">Refuser
                                            </span>
                                        </div>

                                        <div class="booking-actions" style="display: flex; flex-direction: column; justify-content: center; padding: 0 15px;">
                                            <a href="" class="btn-action btn-secondary" style="margin-bottom: 10px;">
                                                <i class="fas fa-eye"></i> Détails
                                            </a>
                                        </div>
                                    </div>
                                     
                            </div>
                             <div class="booking-list">
                               

                                    <div class="booking-item">
                                        <img src="" alt="<" class="booking-image">

                                        <div class="booking-details">
                                            <div class="booking-title"></div>

                                            <div class="booking-location">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </div>

                                            <div class="booking-dates">
                                                <i class="fas fa-calendar"></i>
                                            </div>

                                            <div class="booking-price">
                                            </div>

                                            <span class="booking-status" style="margin-left: -40px;">Accepter
                                            </span>
                                        </div>

                                        <div class="booking-actions" style="display: flex; flex-direction: column; justify-content: center; padding: 0 15px;">
                                            <a href="" class="btn-action btn-secondary" style="margin-bottom: 10px;">
                                                <i class="fas fa-eye"></i> Détails
                                            </a>
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
                            <div class="empty-state">
                                <i class="fas fa-star"></i>
                                <h3>Aucun avis</h3>
                                <p>Vous n'avez pas encore laissé d'avis.</p>
                            </div>
                            <div class="review-list">
                                
                                    <div class="review-item">
                                        <img src="" alt="" class="review-image">

                                        <div class="review-details">
                                            <div class="review-title"></div>

                                            <div class="review-location">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </div>

                                            <div class="review-date">
                                                <i class="fas fa-calendar"></i>
                                                Avis laissé le : 
                                            </div>

                                            <div class="review-rating">
                                               
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
                               

                                    <div class="saved-item" >
                                        <img src="" alt="" class="saved-image">

                                        <div class="saved-details">
                                            <div class="saved-title"></div>

                                            <div class="saved-location">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </div>

                                            <div class="saved-dates">
                                                <i class="fas fa-calendar"></i>
                                                annonce sauvegarder le : 
                                            </div>
                                        </div>

                                        <div class="saved-actions" style="display: flex; flex-direction: column; justify-content: center; padding: 0 15px;">
                                            <a href="" class="btn-action btn-secondary" style="margin-bottom: 10px;">
                                                <i class="fas fa-eye"></i> Détails
                                            </a>
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
