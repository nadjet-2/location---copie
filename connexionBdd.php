<?php
function getDbConnection() {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db   = 'location_immobiliere';

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Erreur de connexion : " . $conn->connect_error);
    }

    return $conn;
}

?>