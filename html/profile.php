<?php
session_start();
require '../PHP/config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=gestionQuestionnaires;charset=utf8', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT NomUtilisateur, Nom, Prenom FROM Utilisateur WHERE NomUtilisateur = ?");
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur existe
if (!$user) {
    die("Utilisateur introuvable.");
}

// Débogage : voir ce que contient $user
// var_dump($user);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le profil</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h2>Modifier le profil</h2>
        <form method="POST" action="dashboard.php">
            <label>Nom d'utilisateur :</label>
            <input type="text" name="username" value="<?php echo !empty($user['NomUtilisateur']) ? htmlspecialchars($user['NomUtilisateur']) : ''; ?>" required>

            <label>Nom :</label>
            <input type="text" name="nom" value="<?php echo !empty($user['Nom']) ? htmlspecialchars($user['Nom']) : ''; ?>" required>

            <label>Prénom :</label>
            <input type="text" name="prenom" value="<?php echo !empty($user['Prenom']) ? htmlspecialchars($user['Prenom']) : ''; ?>" required>

            <label>Nouveau mot de passe (laisser vide pour ne pas changer) :</label>
            <input type="password" name="password">

            <button type="submit">Mettre à jour</button>
        </form>
    </div>
</body>
</html>