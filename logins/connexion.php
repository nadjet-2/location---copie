<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];

    function getConn(): mysqli {
        $serveur_mmc = "localhost";
        $nom_base_mmc = "location_immobiliere";
        $login_mmc = "root";
        $pwd_mmc = "";
        $conn = mysqli_connect($serveur_mmc, $login_mmc, $pwd_mmc, $nom_base_mmc)
            or die("Erreur de connexion");
        return $conn;
    }

    $conn = getConn();

    // Requête sécurisée
    $stmt = $conn->prepare('SELECT * FROM utilisateur WHERE email = ? AND mot_de_passe = ?');
    $stmt->bind_param('ss', $email, $mot_de_passe);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Si c'est l'admin
        if ($email === "mnhome.dz1@gmail.com" && $mot_de_passe === "amanmn2025home") {
            $_SESSION['admin'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'mot_de_passe' => $user['mot_de_passe']
            ];
            header('Location: ../administrateur/admin.php');
            exit();
        }

        // Stockage des données de l'utilisateur
        $_SESSION['utilisateur'] = [
            'id' => $user['id'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'] ?? '',
            'email' => $user['email'],
            'tel' => $user['tel'] ?? '',
            'adresse' => $user['adresse'] ?? '',
            'photo' => $user['photo'] ?? '',
            'role' => $user['role']
        ];

        // Redirection selon le rôle
        if ($user['role'] === 'Proprietaire') {
            header('Location: ../propriétaire/profil.php');
        } elseif ($user['role'] === 'locataire') {
            header('Location: ../profil/locataire.php');
        } else {
            header('Location: ../index.php'); // Redirection par défaut
        }
        exit();
    } else {
        header('Location: connexion.php?err3=1'); // Mauvais identifiants
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="connexion.css"/>
</head>
<body>
    <div class="container">
        <h2>Connexion</h2>
        <p>Bienvenue!</p>
        <form action="" method="POST">
            <input type="email" id="email" name="email" placeholder="Entrer votre E-mail" required />
            <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Entrer votre mot de passe" required />
            <button type="submit">Se connecter</button>
        </form>

        

        <a class="btn" href="../index.php">Retour</a>
    </div>

</body>
</html>

