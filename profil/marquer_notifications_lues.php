<?php
session_start();
if (!isset($_SESSION['utilisateur'])) exit;

$id = $_SESSION['utilisateur']['id'];
$host = 'localhost';
$db = 'location_immobiliere';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) exit;

$stmt = $conn->prepare("UPDATE notification SET vu = 1 WHERE locataire_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

echo json_encode(['status' => 'ok']);
?>
