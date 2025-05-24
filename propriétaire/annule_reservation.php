<?php
session_start();

if (!isset($_SESSION['utilisateur'])) {
    header("Location: ../index.php");
    exit();
}

$host = 'localhost';
$db   = 'location_immobiliere';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $reservation_id = intval($_GET['id']);

    // Vérification que la réservation appartient bien à un bien de ce propriétaire
    $utilisateur_id = $_SESSION['utilisateur']['id'];
    $sql_check = "
        SELECT r.id 
        FROM reservation r
        JOIN annonce a ON r.annonce_id = a.id
        WHERE r.id = ? AND a.proprietaire_id = ?
    ";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $reservation_id, $utilisateur_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        // Suppression de la réservation
        $stmt_delete = $conn->prepare("DELETE FROM reservation WHERE id = ?");
        $stmt_delete->bind_param("i", $reservation_id);
        $stmt_delete->execute();

        header("Location: profil.php?message=reservation_annulee#reservations");
        exit();
    } else {
        echo "Réservation introuvable ou non autorisée.";
    }
} else {
    echo "ID de réservation manquant.";
}
?>
