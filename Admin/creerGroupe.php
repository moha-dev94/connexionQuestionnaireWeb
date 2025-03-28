<?php
session_start();
include("../php/config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Vérification du niveau utilisateur
$stmt = $pdo->prepare("SELECT niveau FROM Utilisateur WHERE ID = ?");
$stmt->execute([$user_id]);
$niveau = $stmt->fetchColumn();

// Restreindre l'accès aux admins
if ($niveau != 1) {
    header("Location: ../HTML/dashboard.php");
    exit();
}

// Traitement du formulaire pour ajouter un groupe
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['groupe'])) {
    $nomGroupe = trim(htmlspecialchars($_POST['groupe']));

    if (!empty($nomGroupe)) {
        // Vérifier si le groupe existe déjà
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM gestionGroupe WHERE NomGroupe = ?");
        $checkStmt->execute([$nomGroupe]);
        if ($checkStmt->fetchColumn() == 0) {
            // Insertion dans gestionGroupe
            $stmt = $pdo->prepare("INSERT INTO gestionGroupe (NomGroupe) VALUES (?)");
            $stmt->execute([$nomGroupe]);

            header("Location: AdminView.php?group_added=1");
            exit();
        } else {
            $erreur = "Ce groupe existe déjà.";
        }
    } else {
        $erreur = "Veuillez saisir un nom de groupe valide.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un groupe</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; text-align: center; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 10px; width: 50%; margin: 50px auto; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        input { padding: 10px; margin-top: 10px; width: 100%; border-radius: 5px; border: 1px solid #ccc; }
        .btn-submit { padding: 10px; background-color: #16a085; color: white; border-radius: 5px; cursor: pointer; width: 100%; border: none; }
        .btn-submit:hover { background-color: #138d75; }
        button { padding: 10px; background-color: #16a085; color: white; border-radius: 5px; cursor: pointer; margin-top: 10px; }
        button:hover { background-color: #138d75; }
        .error { color: red; margin-top: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Créer un nouveau groupe</h2>
    <?php if (isset($erreur)) { ?>
        <p class="error"> <?= htmlspecialchars($erreur) ?> </p>
    <?php } ?>

    <form method="POST">
        <label for="groupe">Nom du groupe :</label>
        <input type="text" id="groupe" name="groupe" required>

        <button type="submit">Créer le groupe</button>
    </form>
</div>

<button onclick="window.location.href='AdminView.php'">Retour à la gestion des utilisateurs</button>

</body>
</html>