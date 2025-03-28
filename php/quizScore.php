<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = new PDO('mysql:host=localhost;dbname=gestionQuestionnaires;charset=utf8', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Vérification du niveau utilisateur
    $stmt = $pdo->prepare("SELECT niveau FROM utilisateur WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_niveau = $stmt->fetchColumn();

    // Si admin et sélection utilisateur via POST
    if ($user_niveau == 1 && isset($_POST['selected_user'])) {
        $selected_user = $_POST['selected_user'];
    } else {
        $selected_user = $user_id;
    }

    // Récupération de tous les utilisateurs (pour admin)
    if ($user_niveau == 1) {
        $users_stmt = $pdo->query("SELECT id, NomUtilisateur FROM utilisateur");
        $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupération des réponses
    $query = $pdo->prepare("SELECT q.Libelle AS Questionnaire, qu.Libelle AS Question, ru.ReponseUtilisateur, 
                                    CASE 
                                        WHEN qu.VraiFaux IS NOT NULL THEN 
                                            CASE WHEN qu.VraiFaux = 1 THEN 'vrai' ELSE 'faux' END 
                                        ELSE ru.BonneReponse 
                                    END AS BonneReponse,
                                    ru.Score, ru.DateParticipation
                            FROM reponses_utilisateur ru
                            JOIN questionnaire q ON ru.QuestionnaireID = q.ID
                            JOIN question qu ON ru.QuestionID = qu.ID
                            WHERE ru.UtilisateurID = ?
                            ORDER BY ru.DateParticipation DESC");

    $query->execute([$selected_user]);
    $reponses = $query->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<p style='color: red; text-align: center;'>Erreur de base de données : " . $e->getMessage() . "</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des réponses</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; text-align: center; }
        .container {
            width: 80%; margin: 50px auto; background: white; padding: 20px;
            border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 { color: #0a3d62; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; }
        th { background-color: #0a3d62; color: white; }
        select, button { padding: 10px; margin-top: 15px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Historique des réponses</h2>

    <?php if ($user_niveau == 1) { ?>
        <form method="POST">
            <label for="selected_user">Choisir un utilisateur :</label>
            <select name="selected_user" id="selected_user">
                <?php foreach ($users as $user) { ?>
                    <option value="<?= $user['id'] ?>" <?= ($selected_user == $user['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['NomUtilisateur']) ?>
                    </option>
                <?php } ?>
            </select>
            <button type="submit">Afficher</button>
        </form>
    <?php } ?>

    <?php if (empty($reponses)) { ?>
        <p>Aucune réponse enregistrée pour cet utilisateur.</p>
    <?php } else { ?>
        <table>
            <tr>
                <th>Quiz</th>
                <th>Question</th>
                <th>Réponse Utilisateur</th>
                <th>Bonne Réponse</th>
                <th>Score</th>
                <th>Date</th>
            </tr>
            <?php foreach ($reponses as $r) { ?>
                <tr>
                    <td><?= htmlspecialchars($r['Questionnaire']) ?></td>
                    <td><?= htmlspecialchars($r['Question']) ?></td>
                    <td><?= htmlspecialchars($r['ReponseUtilisateur']) ?></td>
                    <td><?= htmlspecialchars($r['BonneReponse']) ?></td>
                    <td><?= htmlspecialchars($r['Score']) ?></td>
                    <td><?= htmlspecialchars($r['DateParticipation']) ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>

    <button id="retour" type="button">Retour à l'accueil</button>
</div>

<script>
    document.getElementById('retour').addEventListener('click', function () {
        window.location.href = '../HTML/dashboard.php';
    });
</script>

</body>
</html>
