<?php session_start(); ?>
<?php require '../PHP/config.php'; ?>
<?php
// Récupérer l'ID utilisateur depuis la requête GET ou définir une valeur par défaut
$utilisateurID = isset($_GET['utilisateurID']) ? intval($_GET['utilisateurID']) : $_SESSION['user_id']; // Par défaut ID = 1

// Requête pour récupérer les scores triés par date
$sql = "SELECT Score, DateParticipation FROM score WHERE UtilisateurID = :utilisateurID ORDER BY DateParticipation ASC";
$stmt = $pdo->prepare($sql); // Ici, j'utilise $pdo si c'est ce qui est défini dans config.php
$stmt->bindParam(':utilisateurID', $utilisateurID, PDO::PARAM_INT);
$stmt->execute();

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Encoder les données en JSON pour le JavaScript
$jsonData = json_encode($data);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Graphe du score utilisateur</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<canvas id="grapheScore" style="max-width: 800px;"></canvas>

<script>
// Récupération des données PHP
const dataFromPHP = <?php echo $jsonData; ?>;

// Extraction des dates et scores
const dates = dataFromPHP.map(entry => entry.DateParticipation);
const scores = dataFromPHP.map(entry => entry.Score);
console.log(dates, scores); // Pour déboguer et voir les données
// Création du graphique avec Chart.js
const ctx = document.getElementById('grapheScore').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: dates,
        datasets: [{
            label: `Évolution des scores pour l'utilisateur : <?php echo $_SESSION['username']; ?>`,
            data: scores,
            borderWidth: 2,
            tension: 0.2,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            pointRadius: 5,
            pointHoverRadius: 8,
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