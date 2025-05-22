<?php
  session_start();
  
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "location_immobiliere";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $tel = $_POST['tel'];
    $rib = $_POST['rib'];
    $role = $_POST['role'];
    $mot_de_passe = $_POST['mot_de_passe'];

    // Vérifier si l'utilisateur existe déjà avec le même email ET mot de passe
$check_sql = "SELECT * FROM utilisateur WHERE email = ? AND mot_de_passe = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ss", $email, $mot_de_passe);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo "<script>alert('Un compte avec cet e-mail et ce mot de passe existe déjà.');</script>";
    exit();
}
$check_stmt->close();


   if (strlen($mot_de_passe) < 6) {
    echo "<script>alert('Le mot de passe doit contenir au moins 6 caractères.'); window.history.back();</script>";
    exit();
}





    // Récupération de la photo (si fournie)
    $photo_path = '';
    if (!empty($_FILES['photo']['tmp_name'])) {
        $filename = basename($_FILES["photo"]["name"]);
        $target_dir = "uploads/photos/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $photo_path = $target_dir . $filename;
        move_uploaded_file($_FILES["photo"]["tmp_name"], $photo_path);
    }
    
     { 




    $sql = "INSERT INTO utilisateur (nom, prenom, email, tel, photo, rib, role, mot_de_passe) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssssss", $nom, $prenom, $email, $tel, $photo_path, $rib, $role, $mot_de_passe);

        
        
        if ($stmt->execute()) {
          $user_id = $conn->insert_id; // Récupérer l'ID inséré

          // Démarrage de session
          $_SESSION['utilisateur'] = [
              'id' => $user_id,
              'nom' => $nom,
              'prenom' => $prenom,
              'email' => $email,
              'tel' => $tel,
              'photo' => $photo_path,
              'rib' => $rib,
              'role' => $role,
              'mot_de_passe' => $mot_de_passe,
          ];
            //  Redirection selon le rôle
            if (strtolower($role) === "proprietaire") {
                header("Location: ../propriétaire/profil.php");
                exit(); 
            } else {
              echo "<script>
              alert('✅ compte crée avec succès !');
              window.location.href = '../index.php';
          </script>";
            }
        } else {
            echo "Erreur : " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Erreur de préparation : " . $conn->error;
    }
}
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Inscription</title>
  <link rel="stylesheet" href="formulaire.css"/>


</head>
<body class="formulaire">
  <div class="container">
    <h2>Créer un compte</h2>
    <p>Bienvenue!</p>
    <form class="form" action="formulaire.php" method="post" enctype="multipart/form-data">
      <input type="text" id="nom" name="nom" placeholder="Nom" required />
      <input type="text" id="prenom" name="prenom"placeholder="Prénom" required />
      <input type="email" id="email" name="email" placeholder="E-mail" required />
      <input type="tel" id="tel" name="tel" placeholder="Téléphone" pattern="0\d{9}" maxlength="10" title="Entrez un numéro commencant par 0 et contanant 10 chiffres"   required />
      <label class="label" for="photo">Photo pesonnel</label>
      <input type="file" id="photo" name="photo" accept="image/*" />
      <input type="text" id="rib" name="rib" placeholder="RIB" required />
      <select  name="role" >
        <option value="" disabled selected hidden>Choisissez votre role</option>
        <option class="role" value="Proprietaire">Proprietaire</option>
        <option class="role" value="Locataire">Locataire</option>
      </select>
      <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Créer un mot de passe" required />
      <button type="submit">S'inscrire</button>
    </form>
    <a class="btn" href="../index.php">Retour</a>
  </div>


</body>
</html>
