<?php
session_start();

if (!isset($_SESSION['utilisateur'])) {
    header("Location: ../index.php");
    exit();
}

$utilisateur_id = $_SESSION['utilisateur']['id'];

if (!isset($_GET['id'])) {
    header("Location: profil.php#reservations");
    exit();
}

$id = intval($_GET['id']);

// Connexion à la base
$conn = new mysqli("localhost", "root", "", "location_immobiliere");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Vérifier si la réservation appartient à une annonce du propriétaire connecté
$sql = "SELECT r.id 
        FROM reservation r
        JOIN annonce a ON r.annonce_id = a.id
        WHERE r.id = ? AND a.proprietaire_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $utilisateur_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Mise à jour du statut
    $update = $conn->prepare("UPDATE reservation SET statut = 'valide' WHERE id = ?");
    $update->bind_param("i", $id);
    $update->execute();
}
$update = $conn->prepare("UPDATE reservation SET statut = ?, notif = 1 WHERE id = ?");
$update->bind_param("si", $statut, $idReservation);
$update->execute();

$conn->close();
header("Location: profil.php#reservations");
exit();
?>
