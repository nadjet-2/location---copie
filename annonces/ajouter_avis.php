<?php
session_start();
if (!isset($_SESSION['utilisateur']) || strtolower($_SESSION['utilisateur']['role']) !== 'locataire') {
    die("❌ Accès refusé.");
}

$host = 'localhost';
$db = 'location_immobiliere';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

$id_annonce = $_POST['id_annonce'];
$nom = $_POST['nom'];
$note = $_POST['note'];
$commentaire = $_POST['commentaire'];
$locataire_id = $_SESSION['utilisateur']['id']; // ✅ Récupération de l'ID du locataire

$sql = "INSERT INTO avis (locataire_id, annonce_id, nom, note, commentaire) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iisds", $locataire_id, $id_annonce, $nom, $note, $commentaire); // ✅ Correction du binding
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: detail-annonce.php?id=" . $id_annonce);
exit;
?>
