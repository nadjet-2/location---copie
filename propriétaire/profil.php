<?php
session_start();

if (!isset($_SESSION['utilisateur'])) {
    header("Location: ../index.php"); // Redirection si non connecté
    exit();
}

$utilisateur = $_SESSION['utilisateur'];

// Connexion à la base de données
$host = 'localhost';
$db   = 'location_immobiliere';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}
$utilisateur_id = $_SESSION['utilisateur']['id'];
$sql_annonces = "SELECT * FROM annonce WHERE proprietaire_id = ?";
$stmt = $conn->prepare($sql_annonces);
$stmt->bind_param("i", $utilisateur_id);
$stmt->execute();
$result = $stmt->get_result();



if (isset($_GET['delete_annonce'])) {
    $id = intval($_GET['delete_annonce']);

    // Supprimer l'image du serveur
    $res = $conn->query("SELECT photos FROM annonce WHERE id = $id");
    if ($res && $row = $res->fetch_assoc()) {
        $images = explode(',', $row['photos']);
        foreach ($images as $image) {
            $imagePath = "../annonces/" . trim($image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
    }

    // Supprimer l'annonce de la base
    $conn->query("DELETE FROM annonce WHERE id = $id");

    // Redirection vers la même page
    header("Location: profil.php");
    exit;
}


// Traitement de la mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $tel = $_POST['tel'];

    $photoPath = $utilisateur['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $targetDir = "../logins/";
        $fileName = basename($_FILES["photo"]["name"]);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
            $photoPath = $fileName;
        }
    }

    $stmt = $conn->prepare("UPDATE utilisateur SET prenom=?, nom=?, email=?, tel=?, photo=? WHERE id=?");
    $stmt->bind_param("sssssi", $prenom, $nom, $email, $tel, $photoPath, $utilisateur_id);
    if ($stmt->execute()) {
        $_SESSION['utilisateur']['prenom'] = $prenom;
        $_SESSION['utilisateur']['nom'] = $nom;
        $_SESSION['utilisateur']['email'] = $email;
        $_SESSION['utilisateur']['tel'] = $tel;
        $_SESSION['utilisateur']['photo'] = $photoPath;

        echo "<script>alert('Profil mis à jour avec succès !'); window.location.href='profil.php';</script>";
        exit;
    } else {
        echo "<script>alert('Erreur lors de la mise à jour.');</script>";
    }
}


?>



<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>
    <div class="nav-barre">
        <nav>
        <div >
            <img  class="Logo" src="../images/Logo.png" alt="Logo">
        </div>
        
        <div class="btn">      <a href="../annonces/annonce.php"><button class="creer">Créer une annonce</button></a>



            <button class="notification"><i class="fas fa-bell"></i></button>
            <div class="notif-menu" id="notifMenu">
                <ul>
                    <li><a href=""> Nouveau message reçu</a></li>
                 
                </ul>
            </div>
            <button class="profil"><i class="fas fa-user"></i></button>
            
        </div>
        </nav>
        
    </div>

    <div class="tab-nav">
    <button class="tab-link" onclick="showTab('annonces', this)">Annonces</button>
    <button class="tab-link" onclick="showTab('reservations', this)">Réservations</button>
    <button class="tab-link" onclick="showTab('actualisations', this)">Actualisation des annonces </button>

    </div>

    <main class="content">
    <div id="annonces" class="tab">

    <div id="Rechercher" class="barre-de-recherche">
            <input class="bdr" type="text" placeholder="Rechercher" />
        </div>

        <div class="text">
            <div class="titre">
                <span class="ligne"></span>
                <h4>Mes annonces</h4>
                <span class="ligne"></span>
            </div>
        </div>




        <div class="ancs">

<?php while ($annonce = $result->fetch_assoc()): ?>
    <div class="annonc" onclick="window.location.href='detail_bien.php?id=<?= $annonce['id'] ?>';">
        <div class="image">
            <?php
            $images = explode(',', $annonce['photos']);
            $imagePath = $images[0] ?? '../images/default.jpg';
            ?>
            <img class="img" src="../annonces/<?= htmlspecialchars($imagePath) ?>" alt="img">
        </div>
        <p class="nom"><?= htmlspecialchars($annonce['titre']) ?></p>
        
        <a href="profil.php?delete_annonce=<?= $annonce['id'] ?>#annonces" class="btn-supp" onclick="return confirm('Supprimer cette annonce ?')">
    <i class="fas fa-trash-alt"></i>
        </a>
    </div>
<?php endwhile; ?>
</div>
</div>

 <div id="reservations" class="tab">
    <div class="ancs">
            <h3>Toutes les reservations</h3>
        <div class="annonc" onclick="window.location.href='detail_bien.php';">
             <div class="image">
                       <img class="img" src="../images/273818788.jpg" alt="img">
                    </div>
                <p class="nom">Nom de l'annonce</p>
                <p class="date">Date de l'annonce</p>
                <div class="btnn">
                <button class="act">Annuler</button>
                <button class="act">Valider</button>
                </div>

        </div>
    </div>
</div>

        <div id="actualisations" class="tab">
            <div class="ancs">
        <h3>Annonces à actualisés</h3>
                <div class="annonc">
                   <div class="image">
                       <img class="img" src="../images/273818788.jpg" alt="img">
                    </div>
                <p class="nom">Nom de l'annonce</p>
                <p class="date">Date de l'annonce</p>
                <button class="act">Actualiser</button>
                </div>
            </div>
        </div>


</main>



    <div class="panneau" id="panneau">
        <div class="panneau-header">
        
    
        <img class="photo_prfl" src="../logins/<?php echo htmlspecialchars($utilisateur['photo'] ?? 'default-avatar.png'); ?>" alt="photo">


            <h3>Mon Profil</h3>
            <button class="pann-ferm" id="pannfermbtn">✖️</button>
        </div>
       <div class="panneau-cont">
    <div id="profilInfos">
        <ul>
            <li><a href=""><?php echo htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']); ?></a></li>
            <li><a href=""><?php echo htmlspecialchars($utilisateur['email']); ?></a></li>
            <li><a href=""><?php echo isset($utilisateur['tel']) ? htmlspecialchars($utilisateur['tel']) : 'Non renseigné'; ?></a></li>
            <li><a href=""><?php echo htmlspecialchars($utilisateur['role']); ?></a></li>
            <li><a class="a" href="../logout.php">Déconnexion</a></li>
        </ul>
        <button class="act1" id="editBtn">Modifier</button>
    </div>

    <div id="editForm" style="display: none;">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="update_profile" value="1">

            <label>Prénom :</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($utilisateur['prenom']) ?>" required><br><br>

            <label>Nom :</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($utilisateur['nom']) ?>" required><br><br>

            <label>Email :</label>
            <input type="email" name="email" value="<?= htmlspecialchars($utilisateur['email']) ?>" required><br><br>

            <label>Téléphone :</label>
            <input type="text" name="tel" value="<?= htmlspecialchars($utilisateur['tel']) ?>"><br><br>

            <label>Photo :</label>
            <input type="file" name="photo"><br><br>

            <div class="modif">
            <button type="submit" class="act">Enregistrer</button>
            <button type="button" class="act" id="cancelEdit">Annuler</button>
            </div>
        </form>
    </div>
</div>



    </div>
   

    <div id="message-suppression" style="display: none;"></div>

    <div id="message-suppression" class="message" style="display: none;"></div>












 

<style>
    body{
       
        margin-top: 90px;
        font-family: serif;
        background-color: #f3f4f6;
        margin-left:0;
        margin-right:0;

    }
    nav{
        display: flex;
  justify-content: space-between;
  align-items: center;


    }
    .nav-barre{
  font-size: 20px;
  padding: 0px  20px;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background-color:rgba(28, 42, 89, 0.89);
  height: 65px;

    }
    .Logo{
        width: 113px;
        height: 80px;
        cursor: pointer;
        margin-top: -5.5px;

}
.tab-nav {
      display: flex;
      background-color:rgba(28, 42, 89, 0.69);
      overflow-x: auto;
      margin-top:-25px;
    }

    .tab-nav button {
      flex: 1;
      background-color:rgba(28, 42, 89, 0.62);
      border: none;
      color: white;
      padding: 5px;
      cursor: pointer;
      font-size: 14px;
      transition: background 0.3s;
      border-bottom: 3px solid transparent;
    }

    .tab-nav button:hover,
    .tab-nav button.active {
      background: #374151;
      border-bottom: 3px solid #3b82f6; 
    }
    .tab {
    display: none;
}

.tab.active {
    display: block;
}


.barre-de-recherche {
  margin: 10px auto 20px;
  background-color: #3a3a3ab9;
  border-radius: 50px;
  padding: 18px 90px;
  gap: 16px;
  max-width: 40%;
  display: flex;
  justify-content: space-around;
  align-items: center;
}


.bdr {
  background-color: #131212;
  padding: 14px 100px;
  border-radius: 20px;
  color: rgba(240, 248, 255, 0.75);
  border: 0px solid black;
  cursor: pointer;
  
}
.bdr::placeholder{
    text-align:center;

}
    .creer{
        background-color: #5D76A9;
        border-radius: 20px;
        padding: 10px 20px;
        color: aliceblue;
        border: none;
        cursor: pointer;
    }
    .notification{
        background-color: #5D76A9;
        border-radius: 20px;
        padding: 10px 20px;
        color: aliceblue;
        border: none;
        cursor: pointer;
    }
    .notif-menu {
        position: absolute;
        top: 75px;
        right: 50px;
        background-color: rgba(205, 167, 167, 0.95);
        border: 1px solid #ccc;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.76);
        width: auto;
        display: none;
        z-index: 1;
    }
    
    .profil{
        background-color: #5D76A9;
        border-radius: 20px;
        padding: 10px 20px;
        color: aliceblue;
        border: none;
        cursor: pointer;
    }
    
    .profil-menu {
        position: absolute;
        top: 75px;
        right: 10px;
        background-color: rgba(205, 167, 167, 0.95);
        border: 1px solid #ccc;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.76);
        width: auto;
        display: none;
        z-index: 1;
    }

    .profil-menu ul ,.notif-menu ul{
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .profil-menu ul li,.notif-menu ul li{
        border-bottom: 1px solid #eee;
        font-size: 17px;
        color:black;
    }

    .profil-menu ul li:last-child,.notif-menu ul li:last-child  {
        border-bottom: none;
    }

    .profil-menu ul li a, .notif-menu ul li a {
        display: block;
        padding: 12px;
        color: black;
        text-decoration: none;
    }

    .profil-menu ul li a:hover,.notif-menu ul li a:hover {
        background-color:rgba(93, 118, 169, 0.42);
        border-radius: 5px;
 
    }
    
.text{
    margin:0;
 

}
.titre{
  display: flex;
  justify-content: space-between;
  display: flex;
  align-items: center;
  gap: 10px;
}
.titre .ligne{
  width: 42%;
  height: 1.5px;
  background-color: #5D76A9;
  
}
.titre h4{
  margin: 0;
  font-size: 30px;
  color: #354464;
}
.text h2{
  font-size: 30px;

}
    
    .ancs{
        display: grid;
        gap:7px;
        max-width: 98%;
        margin:1%;
    }
   
   
    .annonc:hover{
        transform: translateY(-2px);
        box-shadow: 0 5px 15px #5D76A9;

    }
    .annonc {
  display: flex;
  align-items: center;
  border: 1px solid #3a3a3ab9;
  border-radius: 10px;
  gap: 15px;
  padding: 5px;
  cursor: pointer;
  transition: transform 0.3s ease;
  background-color: white;
}

.image {
  flex-shrink: 0;
}

.img {
  width: 80px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
}

.nom {
  font-size: 16px;
  font-weight: bold;
  flex-grow: 1;
}

    .btn-supp{
        padding:10px;
        border-radius:50px;
        border:none;
        margin-right:15px;
    }
    .creer:hover {
        cursor: pointer;
        background:rgba(206, 155, 155, 0.63);
        font-size:13.5px;
        border-radius:20px;
    }
    .profil:hover, .notification:hover {
        cursor: pointer;
        background:rgba(206, 155, 155, 0.63);
        font-size:15px;
        border-radius:20px;
    }
    
    .btn-supp:hover{
        cursor: pointer;
        background-color:rgba(255, 0, 0, 0.86);
        border:1px solid black;
        font-size:15px;
    }
    .panneau {
        position: fixed;
        top: 7px;
        right: -400px; 
        width: 300px;
        height: 98%;
        background: rgba(255, 255, 255, 0.53);
        backdrop-filter: blur(10px);
        box-shadow: 0 5px 15px #5D76A9;
        transition: right 0.3s ease-in-out;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.18);


    }

    .panneau.open {
        right: 2;
        z-index: 1000;
    }

    .panneau-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #ddd;
    }

    .panneau-cont {
        padding: 20px;
    }
    .panneau-cont form input {
    width: 100%;
    padding: 8px;
    border-radius: 8px;
    border: 1px solid #ccc;
    margin-bottom: 10px;
}

.panneau-cont form label {
    font-weight: bold;
}

.panneau-cont form button {
    width: 100%;
    padding: 10px;
    background-color: #5D76A9;
    border: none;
    border-radius: 10px;
    color: white;
    cursor: pointer;
}

.panneau-cont form button:hover {
    background-color: #3a4e7c;
}


    .pann-ferm {
        background: none;
        border: none;
        font-size: 15px;
        cursor: pointer;
    }
    .pann-ferm:hover{
        background: none;
        font-size: 16px;

    }
    h3{
        margin-bottom: 10px;
    }
    #message-suppression {
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%); 
    background-color: rgba(5, 85, 5, 0.84);
    color: white;
    padding: 15px 25px;
    border-radius: 15px;
    font-size: 16px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.42);
    animation: fadeinout 3s ease forwards; 
}
.photo_prfl{
    width:50px;
    height:50px;
    border-radius:30px;
}
.panneau-cont{
    padding: 10;

}
.panneau-cont ul {
    list-style: none;

        padding: 0;
        display: block;

    }
    .panneau-cont ul li{
        border-bottom: 1px solid #eee;
        font-size: 18px;
        color:black;
        text-align:center;

    }
    .panneau-cont ul li a {
        color: black;
        text-decoration: none;
        display:block;
        padding:10px;
    }
    .act{
        background-color: #5D76A9;
        border-radius: 20px;
        padding: 10px 20px;
        color: aliceblue;
        border: none;
        cursor: pointer;
        margin-right:20px;
    }
    button:hover {
  background-color: rosybrown;
  font-size:13.5px;

}
.act{
    margin-right:5px;

}
.a:hover{
  background-color: rgba(188, 143, 143, 0.37);
  font-size:19px;

}
.act1{
        
  display: block;
  margin: 20px auto;
  background-color: #5D76A9;
  border-radius: 20px;
  padding: 10px 20px;
  color: white;
  border: none;
  cursor: pointer;
}
.modif{
    display: grid;
    grid-template-columns: auto;
    gap: 5px;
}



        
    

@keyframes fadeinout {
    0% { opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { opacity: 0; }
}

</style>

</style>

<script>
    const profilBtn = document.querySelector('.profil');
    const profilMenu = document.getElementById('profilMenu');
    profilBtn.addEventListener('click', () => {
        profilMenu.style.display = (profilMenu.style.display === 'block') ? 'none' : 'block';
    });
    document.addEventListener('click', function(e) {
        if (!profilBtn.contains(e.target) && !profilMenu.contains(e.target)) {
            profilMenu.style.display = 'none';
        }
    });

    const notifBtn = document.querySelector('.notification');
    const notifMenu = document.getElementById('notifMenu');

// Toggle affichage des notifications
    notifBtn.addEventListener('click', () => {
        const isVisible = notifMenu.style.display === 'block';
        notifMenu.style.display = isVisible ? 'none' : 'block';
        profilMenu.style.display = 'none'; // cacher le menu profil si ouvert
    });

// Fermer si on clique ailleurs
    document.addEventListener('click', function(e) {
        if (!notifBtn.contains(e.target) && !notifMenu.contains(e.target)) {
            notifMenu.style.display = 'none';
        }
    });

// Ouvrir le panneau latéral quand on clique sur le bouton "profil"
document.querySelector('.profil').addEventListener('click', function (e) {
    e.stopPropagation();
    document.getElementById('panneau').classList.add('open');

    // Optionnel : cacher le menu si jamais il s'affiche encore
    document.getElementById('profilMenu').style.display = 'none';
});


// Fermer le panneau latéral
    document.getElementById('pannfermbtn').addEventListener('click', function() {
        document.getElementById('panneau').classList.remove('open');
        });



// Supprimer une annonce et afficher le message
    document.querySelectorAll('.btn-supp').forEach((btn) => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation(); //empecher la redirection
            const annonce = this.closest('.annonc');
            if (!annonce) return;
            const confirmation =confirm("Es-tu sur de vouloir supprimer cette annonce?"); 
            if (confirmation) {
                annonce.remove();

// Affiche le message
                const msg = document.getElementById('message-suppression');
                msg.textContent = " Annonce supprimée avec succès";
                msg.style.display = 'block';

// Cache le message après 3 secondes
                setTimeout(() => {
                    msg.style.display = 'none';
                }, 3000);
            }
        });
    });
    
    

    function showTab(tabId, btn) {
      // Masquer tous les contenus
      document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));

      // Afficher l'onglet actif
      document.getElementById(tabId).classList.add('active');

      // Mettre à jour les boutons d'onglets
      document.querySelectorAll('.tab-link').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    }

    window.onload = function () {
  showTab('annonces', document.querySelector('.tab-link')); 
};

document.getElementById('editBtn').addEventListener('click', function () {
    document.getElementById('profilInfos').style.display = 'none';
    document.getElementById('editForm').style.display = 'block';
});

document.getElementById('cancelEdit').addEventListener('click', function () {
    document.getElementById('editForm').style.display = 'none';
    document.getElementById('profilInfos').style.display = 'block';
});

    
  
</script>







</body>
</html>