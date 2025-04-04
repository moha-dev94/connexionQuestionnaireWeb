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
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../HTML/navbar.php'; ?>
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
