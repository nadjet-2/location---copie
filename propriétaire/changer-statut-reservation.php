<?php
session_start();

if (!isset($_SESSION['utilisateur'])) {
    header("Location: ../index.php");
    exit();
}

$utilisateur_id = $_SESSION['utilisateur']['id'];

// Vérifie la présence des bons paramètres
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    die("Paramètres manquants.");
}

$id = intval($_GET['id']);
$action = $_GET['action'];

if (!in_array($action, ['valide', 'annule'])) {
    die("Action invalide.");
}

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "location_immobiliere");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Vérifie que la réservation appartient à une annonce du propriétaire
$sql = "SELECT r.id 
        FROM reservation r
        JOIN annonce a ON r.annonce_id = a.id
        WHERE r.id = ? AND a.proprietaire_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $utilisateur_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Réservation introuvable ou non autorisée.");
}

if ($action === 'annule') {
    // Mettre à jour le statut à 'annule' (au lieu de supprimer)
    $stmt_update = $conn->prepare("UPDATE reservation SET statut = ?, notif = 1 WHERE id = ?");
    $stmt_update->bind_param("si", $action, $id);
    $stmt_update->execute();

    // Message de notification
    $messageNotif = "Une réservation a été annulée pour votre annonce.";
}else {
    // Mettre à jour le statut si validée
    $stmt_update = $conn->prepare("UPDATE reservation SET statut = ?, notif = 1 WHERE id = ?");
    $stmt_update->bind_param("si", $action, $id);
    $stmt_update->execute();

    // Message de notification
    $messageNotif = "Une réservation a été validée pour votre annonce.";
}



$conn->close();

// Redirige vers la section Réservations
header("Location: profil.php#reservations");
exit();
?>
