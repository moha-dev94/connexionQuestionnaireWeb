<?php
//Conn Distante
 $host = '104.40.137.99:22260'; // Adresse du serveur MySQL
 $dbname = 'mohamedamine_ppedesktop'; // Remplace par le nom de ta base de données
 $username = 'developer'; // Remplace si tu as un autre utilisateur MySQL
 $password = 'cerfal1313'; // Mets ton mot de passe MySQL si nécessaire
//Conn Locale
//  $host = 'localhost'; // Adresse du serveur MySQL
//  $dbname = 'gestionquestionnaires'; // Remplace par le nom de ta base de données
//  $username = 'root'; // Remplace si tu as un autre utilisateur MySQL
//  $password = ''; // Mets ton mot de passe MySQL si nécessaire
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>