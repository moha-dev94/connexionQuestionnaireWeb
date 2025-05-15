<?php session_start(); ?>
<?php require '../PHP/config.php'; ?>

<?php
// Vérifier si l'utilisateur est admin
$isAdmin = (isset($_SESSION['niveau']) && $_SESSION['niveau'] == 1);

// Récupérer la liste des utilisateurs si admin
$usersList = [];
if ($isAdmin) {
    $stmtUsers = $pdo->query("SELECT ID, NomUtilisateur FROM utilisateur ORDER BY NomUtilisateur");
    $usersList = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
}

// ID utilisateur sélectionné (par défaut utilisateur connecté)
$utilisateurID = isset($_GET['utilisateurID']) && $isAdmin ? intval($_GET['utilisateurID']) : $_SESSION['user_id'];

// Récupération des scores de l'utilisateur
$sql = "SELECT Score, DateParticipation FROM score WHERE UtilisateurID = :utilisateurID ORDER BY DateParticipation ASC";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':utilisateurID', $utilisateurID, PDO::PARAM_INT);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$jsonData = json_encode($data);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Graphe du score utilisateur</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container">
    <?php if ($isAdmin): ?>
        <form method="get" action="graphes.php">
            <label for="utilisateurID">Choisir un utilisateur :</label>
            <select name="utilisateurID" id="utilisateurID" onchange="this.form.submit()">
                <?php foreach ($usersList as $user): ?>
                    <option value="<?= $user['ID'] ?>" <?= ($user['ID'] == $utilisateurID) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['NomUtilisateur']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    <?php endif; ?>

    <canvas id="grapheScore" style="max-width: 800px; margin-top: 20px;"></canvas>
</div>

<script>
// Données issues du PHP
const dataPHP = <?php echo $jsonData; ?>;
const dates = dataPHP.map(entry => entry.DateParticipation);
const scores = dataPHP.map(entry => entry.Score);

// Création du graphique
const ctx = document.getElementById('grapheScore').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: dates,
        datasets: [{
            label: `Évolution des scores pour <?= htmlspecialchars(($isAdmin && !empty($usersList)) ? $usersList[array_search($utilisateurID, array_column($usersList, 'ID'))]['NomUtilisateur'] : $_SESSION['username']); ?>`,
            data: scores,
            borderWidth: 2,
            tension: 0.2,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            pointRadius: 5,
            pointHoverRadius: 8
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                stepSize: 1,
                max: 10
            },
            x: {
                display: true,
                title: {
                    display: true,
                    text: 'Date de participation'
                }
            }
        }
    }
});
</script>
</body>
</html>