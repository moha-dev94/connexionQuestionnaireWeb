<?php
session_start();
include("../php/config.php");

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID utilisateur manquant.");
}

$id = intval($_GET['id']);

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT ID, NomUtilisateur, niveau, groupe FROM Utilisateur WHERE ID = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Utilisateur introuvable.");
}

// Compter le nombre de quiz auxquels l'utilisateur a participé
$quizStmt = $pdo->prepare("SELECT COUNT(*) as total FROM Score WHERE utilisateurid = ?");
$quizStmt->execute([$id]);
$quizCount = $quizStmt->fetchColumn();

// Récupérer les groupes existants
$groupStmt = $pdo->query("SELECT NomGroupe FROM gestiongroupe");
$groupes = $groupStmt->fetchAll(PDO::FETCH_ASSOC);

// Mise à jour des informations
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nomUtilisateur = htmlspecialchars(trim($_POST['NomUtilisateur']));
    $niveau = intval($_POST['niveau']);
    $groupe = trim(htmlspecialchars($_POST['groupe']));

    if (empty($nomUtilisateur)) {
        $error = "Le nom d'utilisateur ne peut pas être vide.";
    } else {
        $updateStmt = $pdo->prepare("UPDATE Utilisateur SET NomUtilisateur = ?, niveau = ?, groupe = ? WHERE ID = ?");
        $updateStmt->execute([$nomUtilisateur, $niveau, $groupe, $id]);

        header("Location: AdminView.php?success=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Utilisateur</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; text-align: center; padding: 20px; }
        .container { width: 40%; margin: 50px auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h2 { color: #0a3d62; }
        label { font-weight: bold; }
        input, select { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; }
        .btn { padding: 10px; background-color: #0a3d62; color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px; }
        .btn:hover { background-color: #074b82; }
        .quiz-count { margin-top: 20px; font-size: 18px; color: #16a085; }
        .error { color: red; font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Modifier l'utilisateur</h2>

    <?php if (!empty($error)) { ?>
        <p class="error"> <?= $error ?> </p>
    <?php } ?>

    <form method="post">
        <label for="NomUtilisateur">Nom d'utilisateur :</label>
        <input type="text" id="NomUtilisateur" name="NomUtilisateur" value="<?= htmlspecialchars($user['NomUtilisateur']) ?>" required>

        <label for="niveau">Niveau :</label>
        <select id="niveau" name="niveau">
            <option value="1" <?= $user['niveau'] == 1 ? 'selected' : '' ?>>Admin</option>
            <option value="2" <?= $user['niveau'] == 2 ? 'selected' : '' ?>>Utilisateur</option>
        </select>

        <label for="groupe">Groupe :</label>
        <select id="groupe" name="groupe">
            <option value="">Aucun groupe</option>
            <?php
            $groupesStmt = $pdo->query("SELECT NomGroupe FROM gestionGroupe");
            foreach ($groupes = $groupesStmt->fetchAll() as $grp) {
                $selected = ($user['groupe'] == $grp['NomGroupe']) ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($grp['NomGroupe']) . "' $selected>" . htmlspecialchars($grp['NomGroupe']) . "</option>";
            }
            ?>
        </select>

        <button type="submit" class="btn">Mettre à jour</button>

        <div class="quiz-count">
            Nombre de quiz participés : <?= $quizCount ?>
        </div>
    </form>
</div>

</body>
</html>
