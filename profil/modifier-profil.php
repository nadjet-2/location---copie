<?php
session_start();

if (!isset($_SESSION['utilisateur']) || strtolower($_SESSION['utilisateur']['role']) !== 'locataire') {
    header("Location: ../index.php");
    exit();
}

$host = 'localhost';
$db = 'location_immobiliere';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$id = $_SESSION['utilisateur']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom']);
    $nom = trim($_POST['nom']);

    // Gestion de l'upload photo
    $photo = $_SESSION['utilisateur']['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $newName = uniqid() . '.' . $ext;
        $uploadPath = '../logins/' . $newName;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
            $photo = $newName;
        }
    }

    $stmt = $conn->prepare("UPDATE utilisateur SET prenom = ?, nom = ?, photo = ? WHERE id = ?");
    $stmt->bind_param("sssi", $prenom, $nom, $photo, $id);
    $stmt->execute();

    // Mettre à jour la session
    $_SESSION['utilisateur']['prenom'] = $prenom;
    $_SESSION['utilisateur']['nom'] = $nom;
    $_SESSION['utilisateur']['photo'] = $photo;

    header("Location: locataire.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM utilisateur WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$locataire = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le profil</title>
    <link rel="stylesheet" href="locataire.css">
</head>
<body style="background: rgba(19, 18, 18, 0.1);">
    <div class="center-container">
    <div class="form-container">
        <h2>Modifier mon profil</h2>
        <form method="post" enctype="multipart/form-data">
            <label>Nom :</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($locataire['nom']) ?>" required>
            
            <label>Prénom :</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($locataire['prenom']) ?>" required>

            <label>Photo de profil :</label>
            <input type="file" name="photo" accept="image/*">

            <button class="button1" type="submit">Enregistrer</button>
            <a href="locataire.php">Annuler</a>
        </form>
    </div>
    </div>
</body>
</html>
