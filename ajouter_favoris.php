<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un locataire
if (!isset($_SESSION['utilisateur']) || strtolower($_SESSION['utilisateur']['role']) !== 'locataire') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Vous devez être connecté en tant que locataire']);
    exit;
}

if (!isset($_POST['annonce_id']) || !is_numeric($_POST['annonce_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID de l\'annonce manquant ou invalide']);
    exit;
}

$annonce_id = intval($_POST['annonce_id']);
$locataire_id = $_SESSION['utilisateur']['id'];

$host = 'localhost';
$db   = 'location_immobiliere';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erreur de connexion à la base de données']);
    exit;
}

// Fonction pour vérifier si une annonce est dans les favoris
function isFavoris($conn, $locataire_id, $annonce_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM favoris WHERE locataire_id = ? AND annonce_id = ?");
    $stmt->bind_param("ii", $locataire_id, $annonce_id);
    $stmt->execute();
    $result = $stmt->get_result(); // Utiliser get_result pour récupérer le résultat
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['count'] > 0; // Retourner true si l'annonce est dans les favoris
}

// Fonction pour ajouter une annonce aux favoris
function addToFavorites($conn, $locataire_id, $annonce_id) {
    $stmt = $conn->prepare("INSERT INTO favoris (locataire_id, annonce_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $locataire_id, $annonce_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result ? ['status' => 'success', 'message' => 'Annonce ajoutée aux favoris', 'isFavorite' => true]
                   : ['status' => 'error', 'message' => 'Erreur lors de l\'ajout aux favoris'];
}

// Fonction pour supprimer une annonce des favoris
function removeFromFavorites($conn, $locataire_id, $annonce_id) {
    $stmt = $conn->prepare("DELETE FROM favoris WHERE locataire_id = ? AND annonce_id = ?");
    $stmt->bind_param("ii", $locataire_id, $annonce_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result ? ['status' => 'success', 'message' => 'Annonce supprimée des favoris', 'isFavorite' => false]
                   : ['status' => 'error', 'message' => 'Erreur lors de la suppression des favoris'];
}

// Vérifier si l'annonce est déjà dans les favoris
if (isFavoris($conn, $locataire_id, $annonce_id)) {
    // Supprimer des favoris
    $result = removeFromFavorites($conn, $locataire_id, $annonce_id);
} else {
    // Ajouter aux favoris
    $result = addToFavorites($conn, $locataire_id, $annonce_id);
}

// Retourner le résultat en JSON
echo json_encode($result);

$conn->close();
?>