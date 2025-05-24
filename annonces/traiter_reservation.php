<?php
session_start();

$host = 'localhost';
$db = 'location_immobiliere';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Vérifier que l'utilisateur est connecté et qu'il a le rôle locataire
if (!isset($_SESSION['utilisateur']) || strtolower($_SESSION['utilisateur']['role']) !== 'locataire') {
    die("❌ Vous devez être connecté en tant que locataire pour réserver.");
}

// Récupérer les données postées
$annonce_id = $_POST['annonce_id'] ?? null;
$date_debut = $_POST['date_debut'] ?? null;
$date_fin = $_POST['date_fin'] ?? null;
$locataire_id = $_SESSION['utilisateur']['id']; // On suppose que l'id de l'utilisateur est stocké en session

// Vérifications basiques
if (!$annonce_id || !$date_debut || !$date_fin) {
    die("❌ Tous les champs sont obligatoires.");
}

// Vérifier que les dates sont valides
if (strtotime($date_fin) <= strtotime($date_debut)) {
    die("❌ La date de fin doit être après la date de début.");
}

// Préparer la requête d'insertion
$sql = "INSERT INTO reservation (annonce_id, locataire_id, date_debut, date_fin, statut, date_reservation) VALUES (?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Erreur de préparation : " . $conn->error);
}

$statut = 'en attente'; // ou 'validée', selon ta logique métier

$stmt->bind_param("iisss", $annonce_id, $locataire_id, $date_debut, $date_fin, $statut);

if ($stmt->execute()) {
    $_SESSION['reservation_message'] = "🎉 Votre réservation a été enregistrée avec succès !";
header("Location: detail-annonce.php?id=" . $annonce_id);
exit;

    // Succès : rediriger ou afficher un message
    header("Location: detail-annonce.php?id=" . $annonce_id . "&message=Réservation effectuée avec succès !");
    exit;
} else {
    echo "Erreur lors de l'enregistrement de la réservation : " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
