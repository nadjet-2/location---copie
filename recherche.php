<?php
$host = 'localhost';
$db   = 'location_immobiliere';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Fonction pour normaliser une adresse
function normaliserAdresse($adresse) {
    $adresse = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $adresse); // Supprime ponctuation sauf lettres et chiffres
    $adresse = mb_strtolower($adresse, 'UTF-8'); // En minuscule
    $adresse = preg_replace('/\s+/', ' ', $adresse); // Réduit les espaces multiples
    return trim($adresse);
}

// Récupération des filtres utilisateur
$adresse = $_GET['adresse'] ?? '';
$type = $_GET['type_logement'] ?? '';
$nb_personnes = $_GET['nombre_personnes'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';

// Début de la requête
$sql = "SELECT * FROM annonce WHERE 1=1";
$conditions = [];
$params = [];
$types_str = "";

// Adresse (recherche souple)
if (!empty($adresse)) {
    $adresse = normaliserAdresse($adresse);
    $mots = explode(' ', $adresse);
    $adresse_conditions = [];

    foreach ($mots as $mot) {
        $adresse_conditions[] = "LOWER(REPLACE(REPLACE(REPLACE(adresse, ',', ' '), '-', ' '), '  ', ' ')) LIKE ?";
        $params[] = "%" . $mot . "%";
        $types_str .= "s";
    }

    if (!empty($adresse_conditions)) {
        $sql .= " AND (" . implode(" OR ", $adresse_conditions) . ")";
    }
}

// Type de logement
if (!empty($type)) {
    $sql .= " AND type_logement = ?";
    $params[] = $type;
    $types_str .= "s";
}

// Nombre de personnes
if (!empty($nb_personnes)) {
    $sql .= " AND nombre_personnes >= ?";
    $params[] = $nb_personnes;
    $types_str .= "i";
}

// Disponibilité
if (!empty($date_debut) && !empty($date_fin)) {
    $sql .= " AND date_debut <= ? AND date_fin >= ?";
    $params[] = $date_debut;
    $params[] = $date_fin;
    $types_str .= "ss";
}

$sql .= " ORDER BY id DESC";
$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param($types_str, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats de la recherche</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2 style="text-align:center;">Résultats de votre recherche</h2>
    <div class="propriete-list">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): 
            $photos = explode(',', $row['photos']);
            $photo = !empty($photos[0]) ? 'annonces/' . $photos[0] : 'images/default.jpg';



        ?>
            <div class="propriete-cart">
                <img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($row['titre']) ?>">
                <div class="propriete-cont">
                    <h3><?= htmlspecialchars($row['titre']) ?></h3>
                    <p class="localisation">📍 <?= htmlspecialchars($row['adresse']) ?></p>
                    <div class="details">
                        <span>🏠 <?= htmlspecialchars($row['supperficie']) ?> m²</span>
                        <span>🛏️ <?= htmlspecialchars($row['nombre_pieces']) ?> ch</span>
                    </div>
                    <div class="prix-row">
                        <span class="prix"><?= htmlspecialchars($row['tarif']) ?> DA/nuit</span>
                        <a href="annonces/detail-annonce.php?id=<?= $row['id'] ?>">
                     <button class="button4">Voir les détails</button>
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center;">Aucune annonce ne correspond à vos critères.</p>
    <?php endif; ?>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>









