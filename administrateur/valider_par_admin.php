<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'location_immobiliere');
if ($conn->connect_error) die("Erreur : " . $conn->connect_error);

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("UPDATE reservation SET valide_admin = 1 WHERE id = $id");
}

header("Location: admin.php#reservations");
exit;
