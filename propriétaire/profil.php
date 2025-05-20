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
                    <li><a href=""> Actualiser une annonce</a></li>
                    <li><a href=""> Valider une réservation</a></li>
                    <li><a href=""> Annuler une réservation</a></li>


                </ul>
            </div>
            <button class="profil"><i class="fas fa-user"></i></button>
            <div class="profil-menu" id="profilMenu">
                <ul>
                    <li><a href="">Mon profil</a></li>
                    <li><a href="../logout.php">Déconnexion</a>
                    </li>
                </ul>
            </div>
        </div>
        </nav>
        
    </div>
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



    <div class="panneau" id="panneau">
        <div class="panneau-header">
        
    
        <img class="photo_prfl" src="../logins/<?php echo htmlspecialchars($utilisateur['photo'] ?? 'default-avatar.png'); ?>" alt="photo">


            <h3>Mon Profil</h3>
            <button class="pann-ferm" id="pannfermbtn">✖️</button>
        </div>
        <div class="panneau-cont">
    <ul>
        <li><a href=""><?php echo htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']); ?></a></li>
        <li><a href=""><?php echo htmlspecialchars($utilisateur['email']); ?></a></li>
        <li><a href=""><?php echo isset($utilisateur['tel']) ? htmlspecialchars($utilisateur['tel']) : 'Non renseigné'; ?></a></li>
        <li><a href=""><?php echo htmlspecialchars($utilisateur['role']); ?></a></li>
    </ul>
</div>

    </div>
   

    <div id="message-suppression" style="display: none;"></div>

    <div id="message-suppression" class="message" style="display: none;"></div>












 

<style>
    body{
       
        margin-top: 150px;
        font-family: serif;
        background-color: #f3f4f6;
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
.barre-de-recherche {
  margin: -65px auto 30px;
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
   
    .annonc{
        display: flex;
        justify-content: space-between;
        align-items: center;
        border:1px solid #3a3a3ab9;
        border-radius:10px;
        gap:20px;
        cursor:pointer;
        transition: transform 0.3s ease;
    }
    .annonc:hover{
        transform: translateY(-2px);
        box-shadow: 0 5px 15px #5D76A9;

    }
    .image{
        border:1px solid rgba(91, 90, 90, 0.72);
        border-radius:10px;
        padding:auto;
        margin-left:3px;

    }
    .img{
        width: 60px;
        height:45px;
        border-radius:8px;

        
    }
    .nom{
        margin-left:-400px;
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

    .pann-ferm {
        background: none;
        border: none;
        font-size: 15px;
        cursor: pointer;
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
    padding: 0;

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


@keyframes fadeinout {
    0% { opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { opacity: 0; }
}

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

// Ouvrir le panneau latéral quand on clique sur "Mon profil"
    document.querySelector('#profilMenu ul li a').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('panneau').classList.add('open');
        document.getElementById('profilMenu').style.display = 'none';
    });

// Fermer le panneau latéral
    document.getElementById('pannfermbtn').addEventListener('click', function() {
        document.getElementById('panneau').classList.remove('open');
        });




   
    
    
</script>







</body>
</html>