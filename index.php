<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MN Home DZ</title>
  <link rel="stylesheet" href="style.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

  <nav class="nav-barre">
    <div>
      <img class="Logo" src="images/Logo.png" alt="Logo" />
    </div>
    
    <div class="div-de-ul">
      <ul>
        <li><a href="#Accueil">Accueil</a></li>
        <li><a href="#Rechercher">Rechercher</a></li>
        <li><a href="#Propriétés">Propriétés</a></li>
      </ul>
    </div>
    
    <div>
  <?php if (isset($_SESSION['utilisateur']) && strtolower($_SESSION['utilisateur']['role']) === 'locataire'): ?>
    <a href="profil/locataire.php"><button class="button1"><i class="fas fa-user"></i></button></a>
  <?php else: ?>
    <?php if (isset($_SESSION['utilisateur']) && strtolower($_SESSION['utilisateur']['role']) === 'proprietaire'): ?>
    <a href="propriétaire/profil.php"><button class="button1"><i class="fas fa-user"></i></button></a>
  <?php else: ?>
    <a href="logins/connexion.php"><button class="button1">Connexion</button></a>
    <a href="logins/formulaire.php"><button class="button2">Créer un compte</button></a>
  <?php endif; ?>
  <?php endif; ?>

</div>

  </nav>

  <div class="image-fond">
    <div id="Rechercher" class="phrase">
      <h1 class="la-phrase">Trouvez votre logement <br />de location en Algérie.</h1>
      <h3 class="phrase2">Des milliers de Propriétés à louer dans toute l'Algérie</h3>
    </div>

    <div id="Accueil" class="barre-de-recherche">
    <form action="recherche.php" method="GET">
      <input class="bdr" type="text" name="adresse" placeholder="Rechercher une destination" />
      <input class="bdr" type="date" name="date_debut" placeholder="Arrivée" />
      <input class="bdr" type="date" name="date_fin" placeholder="Départ" />
      <select name="type_logement" class="bdr">
        <option value="" disabled selected hidden>type de logement</option>
        <option value="appartement">appartement</option>
        <option value="maison">maison</option>
      </select>
      <input class="bdr" type="number" name="nombre_personnes" min="1" placeholder="Nombre de Personnes" />
      <button class="button3">Rechercher</button>
      </form>
    </div>
  </div>

  
  <section id="Proprietes" class="proprietes-section">
    <h2>Nos Propriétés Disponibles</h2>
    <div class="propriete-list">
    
    <?php
    
$host = 'localhost';
$db   = 'location_immobiliere';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

$sql = "SELECT * FROM annonce WHERE valide = 1 ORDER BY id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0):
$favoris = [];
if (isset($_SESSION['utilisateur']) && strtolower($_SESSION['utilisateur']['role']) === 'locataire') {
    $locataireId = $_SESSION['utilisateur']['id'];
    $res = $conn->query("SELECT annonce_id FROM favoris WHERE locataire_id = $locataireId");
    while ($f = $res->fetch_assoc()) {
        $favoris[] = $f['annonce_id'];
    }
}
 ?>

    <?php while($row = $result->fetch_assoc()): 
        $photos = explode(',', $row['photos']);
        $photo = !empty($photos[0]) ? 'annonces/' . $photos[0] : 'images/default.jpg';

    ?>
        <div class="propriete-cart">
        <div class="image-container">
            <img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($row['titre']) ?>">
            <?php
            $isFavori = in_array($row['id'], $favoris);

            ?>

            <button class="favori-btn" data-annonce-id="<?= $row['id'] ?>" style="background: none; border: none; cursor: pointer;">
  <i class="fa<?= $isFavori ? 's' : 'r' ?> fa-heart <?= $isFavori ? 'favori-actif' : '' ?>"></i>
</button>


          </div>

            <div class="propriete-cont">
                <h3><?= htmlspecialchars($row['titre']) ?></h3>
                <p class="localisation">📍 <?= htmlspecialchars($row['adresse']) ?></p>
                <div class="details">
                    <span>🏠 <?= htmlspecialchars($row['supperficie']) ?>m²</span>
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
    <p>Aucune annonce disponible pour le moment.</p>
<?php endif;

$conn->close();
?>


    </div>
    <div class="btn-aut">

        <button id="plus" class="button5">Autres</button>
    </div>
  </section>

  <hr>

  
  <section>
    <div class="nous">
        <div class="text">
            <div class="titre">
                <span class="ligne"></span>
                <h4>Qui nous sommes</h4>
            </div>
        
        <h2>Decouvrer avec nous des vacances de luxe .</h2>
        <p>MN Home DZ est votre plateforme de confiance pour découvrir et louer des biens immobiliers en Algérie. Que vous cherchiez un appartement ou une maison nous <br> vous offrons une interface simple, rapide et intuitive pour trouver le bien qui vous correspond. Avec MN Home DZ, l'immobilier devient plus accessible, plus clair et <br> plus efficace.</p>
        </div>
        <img class="img" src="images/comment-proteger-sa-maison-sans-alarme (1).jpg" alt="">

    </div>
  </section>
  <div class="mission">
        <div class="text2">
            <div class="titre2">
                <span class="ligne"></span>
                <h4>Notre mission</h4>
            </div>
        
        <h2>Bienvenue sur MN HOME – Notre mission, votre confort.</h2>
        <p>Chez MN Home DZ, nous avons pour mission de faciliter la recherche de logements <br>adaptés aux familles, en mettant en avant des biens fiables, confortables et bien situés. <br> Notre plateforme vise à créer un lien de confiance entre les locataires et les propriétaires,<br> en simplifiant chaque étape de la location.</p>
        </div>

    </div>
    
</div>





  

<footer id="Propriétés">
  <div class="footer-div">
    
    <div>
      <h3>CONTACTS</h3>
      <p class="contact"><i class="fas fa-phone"></i> +213 712 35 46 78</p>
      <p class="contact"><i class="fas fa-envelope"></i>
  <a class="contact" href="https://mail.google.com/mail/?view=cm&to=mnhome.dz1@gmail.com" >mnhome.dz1@gmail.com </a></p>
  




      <p class="contact"><i class="fab fa-facebook"></i> <a class="contact" href="https://www.facebook.com/profile.php?id=61575951081216" >facebook.com/MN Home Dzz</a></p>
    </div>

    <div>
      <h3>PROPRIÉTÉS</h3>
      <p>● © 2025 NotreStartup</p>
      <p>● pour la location immobiliere</p>
     
    </div>

    <div>
      <h3>CONDITIONS</h3>
      <p><a href="conditions.php" >Conditions Générales</a></p>
      <p><a href="confidentialite.php" >Politique de Confidentialité</a></p>
     
    </div>

  </div>
</footer>

<script>

  const isLocataire = <?= isset($_SESSION['utilisateur']) && strtolower($_SESSION['utilisateur']['role']) === 'locataire' ? 'true' : 'false' ?>;

  




  const annonces = document.querySelectorAll('.propriete-cart');
  const loadMoreBtn = document.getElementById('plus');
  let annoncesAffichees = 6; // 6 au départ

  function afficherAnnonces() {
    for (let i = 0; i < annonces.length; i++) {
      if (i < annoncesAffichees) {
        annonces[i].style.display = 'block';
      } else {
        annonces[i].style.display = 'none';
      }
    }

    // Si toutes les annonces sont affichées, cacher le bouton
    if (annoncesAffichees >= annonces.length) {
      loadMoreBtn.style.display = 'none';
    }
  }

  afficherAnnonces(); 

  loadMoreBtn.addEventListener('click', () => {
    annoncesAffichees += 3; 
    afficherAnnonces();
  });
  
</script>
<script>
document.querySelectorAll('.favori-btn').forEach(button => {
  button.addEventListener('click', async (e) => {
    e.preventDefault();

    const annonceId = button.getAttribute('data-annonce-id');
    const icon = button.querySelector('i');

    try {
      const response = await fetch('ajouter_favoris.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `annonce_id=${annonceId}`
      });

      const result = await response.json();

      if (result.status === 'success') {
        // Toggle icône cœur pleine / vide
        if (result.isFavorite) {
          icon.classList.remove('far');
          icon.classList.add('fas', 'favori-actif');
        } else {
          icon.classList.remove('fas', 'favori-actif');
          icon.classList.add('far');
        }
      } else {
        alert(result.message);
      }

    } catch (error) {
      console.error('Erreur AJAX :', error);
      alert('Une erreur est survenue. Veuillez réessayer.');
    }
  });
});
</script>


</body>
</html>
