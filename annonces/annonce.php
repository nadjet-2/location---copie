<?php
session_start();
$proprietaire_id = $_SESSION['utilisateur']['id']; // Assurez-vous que l'ID de l'utilisateur est bien stocké en session
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="annonce.css"/>
    <title>Document</title>
</head>
<body>
    <form class="container" action="" method="post" enctype="multipart/form-data">
        <h2>Créer une Nouvelle Annonce</h2>
        <input type="text" id="titre" name="titre" placeholder="Titre de votre annonce" required>
        <input type="text" id="adresse" name="adresse" placeholder="Adresse complète" required>
        <select name="type_logement" required>
          <option value="" disabled selected hidden>Type de logement</option>
          <option class="type" value="appartement">Appartement</option>
          <option class="type" value="maison">Maison</option>
        </select>
        <input type="text" id="supperficie" name="supperficie" placeholder="La supperficie en m²" required>
        <input type="number" id="nombre_pieces" name="nombre_pieces" min="1" placeholder="Nombre de pièces" required>
        <input type="number" id="nombre_personnes" name="nombre_personnes" min="1" placeholder="Nombre de personnes autorisées" required>
        <div class="equipement-container">
            <label class="text" for="equipements">Équipements :</label>
            <div class="checkbox-grid">
                <label>
                    <input type="checkbox" name="equipement[]" value="wifi"> Wifi</label>
                    <label>
                    <input type="checkbox" name="equipement[]" value="television"> Télévision</label>
                <label>
                    <input type="checkbox" name="equipement[]" value="climatisation"> Climatisation</label>
                    <label>
                    <input type="checkbox" name="equipement[]" value="chauffage"> Chauffage</label>
                
                <label>
                    <input type="checkbox" name="equipement[]" value="lave-linge"> Lave linge</label>
                    <label>
                    <input type="checkbox" name="equipement[]" value="Sèche-linge"> Sèche linge</label>
                    <label>
                    <input type="checkbox" name="equipement[]" value="cuisine"> Cuisine</label>
                    <label>
                    <input type="checkbox" name="equipement[]" value="Suite-travail"> Suite pour travail</label>
            </div>
            <input type="text" class="other-input" name="autres_equipements" placeholder="Autres équipements...">
        </div>
        <label class="label" for="photos">Photos du local :</label>
        <input type="file" id="photos" name="photos[]" accept="image/*" multiple required>
    
        <input type="number" id="tarif" name="tarif" step="0.01" placeholder="Tarif proposé (DA par nuit) " required>
        <div class="disp-container">
            <label class="text" for="disponibilite">Disponibilité du local :</label>
            <input class="date" type="date" id="date_debut" name="date_debut" required>
            <input class="date" type="date" id="date_fin" name="date_fin" required>
        </div>
        <label class="label"  for="piece_identite">Pièce d’identité scannée (PDF ou image) :</label>
        <input type="file" id="piece_identite" name="piece_identite" accept=".pdf,image/*" required>
        <label class="label" for="acte_propriete">Acte de propriété (livre foncier scanné) :</label>
        <input type="file" id="acte_propriete" name="acte_propriete" accept=".pdf,image/*" required>
        <button class="button" type="submit">Soumettre l'annonce</button>
      </form>


<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


// Connexion à la base de données
$host = 'localhost';
$db   = 'location_immobiliere';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Fonction pour sécuriser les noms de fichiers
function sanitize_filename($filename) {
    $filename = iconv("UTF-8", "ASCII//TRANSLIT", $filename);
    return preg_replace('/[^A-Za-z0-9.\-_]/', '_', $filename);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Récupération des données du formulaire
    $titre = $_POST['titre'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $type_logement = $_POST['type_logement'] ?? '';
    $suppeficie = $_POST['supperficie'] ?? '';
    $nombre_pieces = $_POST['nombre_pieces'] ?? 0;
    $nombre_personnes = $_POST['nombre_personnes'] ?? 0;
    $tarif = $_POST['tarif'] ?? 0;
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin = $_POST['date_fin'] ?? '';
    $autres_equipements = $_POST['autres_equipements'] ?? '';
    $equipements = isset($_POST['equipement']) ? implode(',', $_POST['equipement']) : '';

    // 2. Enregistrement des photos
    $photos_names = [];
    if (isset($_FILES['photos']) && is_array($_FILES['photos']['tmp_name'])) {
        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            if (!empty($tmp_name)) {
                $file_name = sanitize_filename($_FILES['photos']['name'][$key]);
                $target_path = "uploads/photos/" . $file_name;

                if (move_uploaded_file($tmp_name, $target_path)) {
                    $photos_names[] = $target_path;
                }
            }
        }
    }
    $photos = implode(',', $photos_names);

    // 3. Pièce d'identité
    $piece_identite_name = '';
    if (!empty($_FILES['piece_identite']['tmp_name'])) {
        $file_name = sanitize_filename($_FILES['piece_identite']['name']);
        $target_path = "uploads/docs/" . $file_name;

        if (move_uploaded_file($_FILES['piece_identite']['tmp_name'], $target_path)) {
            $piece_identite_name = $target_path;
        }
    }

    // 4. Acte de propriété
    $acte_propriete_name = '';
    if (!empty($_FILES['acte_propriete']['tmp_name'])) {
        $file_name = sanitize_filename($_FILES['acte_propriete']['name']);
        $target_path = "uploads/docs/" . $file_name;

        if (move_uploaded_file($_FILES['acte_propriete']['tmp_name'], $target_path)) {
            $acte_propriete_name = $target_path;
        }
    }

    // 5. Insertion dans la base de données
    $sql = "INSERT INTO annonce (
        titre, adresse, type_logement, supperficie, nombre_pieces, nombre_personnes,
        equipements, autres_equipements, tarif,
        date_debut, date_fin, photos,
        piece_identite, acte_propriete, proprietaire_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssiissdsssssi",
        $titre, $adresse, $type_logement, $suppeficie, $nombre_pieces, $nombre_personnes,
        $equipements, $autres_equipements, $tarif,
        $date_debut, $date_fin, $photos,
        $piece_identite_name, $acte_propriete_name, $proprietaire_id
    );
    

    if ($stmt->execute()) {
        echo "<script>
    alert('✅ Annonce soumise avec succès !');
    window.location.href = '../propriétaire/profil.php';
</script>";


    } else {
        echo "❌ Erreur SQL : " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
</body>
</html>