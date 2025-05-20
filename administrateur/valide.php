<?php
$conn = new mysqli('localhost', 'root', '', 'location_immobiliere');
if ($conn->connect_error) {
    die("Erreur : " . $conn->connect_error);
}

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $conn->prepare("UPDATE annonce SET valide = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header('Location: admin.php'); // Redirection vers le tableau admin
exit;
?>
