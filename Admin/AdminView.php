<?php
include("../php/config.php");

// Traitement ajout utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['NomUtilisateur'], $_POST['email'], $_POST['MotDePasse'], $_POST['niveau'], $_POST['groupe'])) {
    $NomUtilisateur = $_POST['NomUtilisateur'];
    $email = $_POST['email'];
    $MotDePasse = password_hash($_POST['MotDePasse'], PASSWORD_DEFAULT);
    $niveau = $_POST['niveau'];
    $groupe = $_POST['groupe'];

    $stmt = $pdo->prepare("INSERT INTO Utilisateur (NomUtilisateur, email, MotDePasse, niveau, groupe) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$NomUtilisateur, $email, $MotDePasse, $niveau, $groupe]);

    header("Location: AdminView.php?user_added=1");
    exit();
}

// Récupérer les utilisateurs
$query = $pdo->query("SELECT ID, NomUtilisateur, niveau, groupe FROM Utilisateur");
$users = $query->fetchAll();

// Récupérer les groupes depuis gestiongroupe
$groupQuery = $pdo->query("SELECT ID, NomGroupe FROM gestiongroupe");
$groupes = $groupQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des utilisateurs</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; text-align: center; }
        h2 { color: #0a3d62; }
        table { width: 80%; margin: 20px auto; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #0a3d62; color: white; }
        tr:hover { background: #ecf0f1; }
        td a { text-decoration: none; color: #e74c3c; font-weight: bold; }
        td a:hover { color: #c0392b; }
        .admin { color: green; font-weight: bold; }
        .user { color: blue; font-weight: bold; }
        .success { background-color: #2ecc71; color: white; padding: 10px; border-radius: 5px; font-weight: bold; text-align: center; margin-bottom: 15px; }
        .form-container { margin-top: 20px; padding: 20px; background: #fff; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); text-align: left; width: 50%; margin: 20px auto; }
        input, select { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; }
        .btn-submit { margin-top: 10px; padding: 10px; background-color: #16a085; color: white; border: none; border-radius: 5px; font-size: 18px; cursor: pointer; width: 100%; }
        .btn-submit:hover { background-color: #138d75; }
        button { background-color: #28a745; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; width: 80%; }
        button:hover { background-color: #218838; }
    </style>
</head>
<body>

<h2>Gestion des utilisateurs</h2>

<table>
    <tr>
        <th>Utilisateur</th>
        <th>Niveau</th>
        <th>Groupe</th>
        <th>Action</th>
    </tr>
    <?php foreach ($users as $u) { ?>
        <tr>
            <td><?= htmlspecialchars($u['NomUtilisateur']) ?></td>
            <td class="<?= $u['niveau'] == 1 ? 'admin' : 'user' ?>">
                <?= $u['niveau'] == 1 ? 'Admin' : 'Utilisateur' ?>
            </td>
            <td><?= htmlspecialchars($u['groupe'] ?? 'Aucun groupe') ?></td>
            <td><a href="editUser.php?id=<?= $u['ID'] ?>">Modifier</a></td>
        </tr>
    <?php } ?>
</table>

<button id="retour" type="button">Retour à l'accueil</button>
<script>
    document.getElementById('retour').addEventListener('click', function () {
        window.location.href = '../HTML/dashboard.php';
    });
</script>

<div class="form-container">
    <h2>Ajouter un nouvel utilisateur</h2>
    <form method="post">
        <label>Nom d'utilisateur :</label>
        <input type="text" name="NomUtilisateur" required>

        <label>Email :</label>
        <input type="email" name="email" required>

        <label>Mot de passe :</label>
        <input type="password" name="MotDePasse" required>

        <label>Niveau :</label>
        <select name="niveau">
            <option value="1">Admin</option>
            <option value="2" selected>Utilisateur</option>
        </select>

        <label for="groupe">Groupe :</label>
<select id="groupe" name="groupe" required>
    <option value="">Sélectionner un groupe</option>
    <?php foreach ($groupes as $grp) { ?>
        <option value="<?= htmlspecialchars($grp['NomGroupe']) ?>"><?= htmlspecialchars($grp['NomGroupe']) ?></option>
    <?php } ?>
</select>



        <button type="submit" class="btn-submit">Ajouter</button>
    </form>

</div>

<button id="grp" type="button">Gestion des groupes</button>
<script>
    document.getElementById('grp').addEventListener('click', function () {
        window.location.href = '../Admin/creerGroupe.php';
    });
</script>
</body>
</html>
