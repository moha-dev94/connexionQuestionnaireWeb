<?php
session_start();
require '../PHP/config.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "Utilisateur non connecté."]);
    exit();
}

if (!isset($_SESSION['quiz_data'])) {
    echo json_encode(["status" => "error", "message" => "Aucune donnée de quiz trouvée."]);
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=gestionQuestionnaires;charset=utf8', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Initialiser le score global à 0 si pas déjà initialisé
if (!isset($_SESSION['quiz_score'])) {
    $_SESSION['quiz_score'] = 0;
}

$user_id = $_SESSION['user_id'];
$questionnaire_id = $_SESSION['questionnaire_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['answer'], $_POST['question_id'])) {
        $questionID = intval($_POST['question_id']);
        $userAnswer = trim(strtolower($_POST['answer']));

        // Récupérer la bonne réponse et vérifier si vrai/faux
        $stmt = $pdo->prepare("SELECT BonneReponse, VraiFaux FROM question WHERE ID = ?");
        $stmt->execute([$questionID]);
        $questionData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($questionData) {
            if (!is_null($questionData['VraiFaux'])) {
                // Traitement spécifique pour questions vrai/faux
                $correctAnswer = $questionData['VraiFaux'] == 1 ? 'vrai' : 'faux';
            } else {
                // Traitement pour les questions normales
                $correctAnswer = trim(strtolower($questionData['BonneReponse']));
            }

            $isCorrect = ($userAnswer === $correctAnswer);

            // Incrémenter le score global si réponse correcte
            if ($isCorrect) {
                $_SESSION['quiz_score'] += 1;
                $scoreAttribue = 1;
            } else {
                $scoreAttribue = 0;
            }

            // Enregistrer la réponse en base de données
            $stmt = $pdo->prepare("INSERT INTO reponses_utilisateur 
                (UtilisateurID, QuestionnaireID, QuestionID, ReponseUtilisateur, BonneReponse, Score, DateParticipation) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())");

            $stmt->execute([
                $user_id,
                $questionnaire_id,
                $questionID,
                $userAnswer,
                $correctAnswer,
                $scoreAttribue
            ]);

            // Log pour debug
            file_put_contents('debug_log.txt', "ID Question: $questionID | Réponse utilisateur: $userAnswer | Bonne réponse: $correctAnswer | Score attribué: $scoreAttribue\n", FILE_APPEND);
        }
    }
}

// Vérifier la fin du quiz pour enregistrer le score total
if (isset($_GET['end']) && $_GET['end'] === '1') {
    $totalScore = $_SESSION['quiz_score'];

    // Enregistrer le score final dans la base de données
    $stmt = $pdo->prepare("INSERT INTO score (UtilisateurID, QuestionnaireID, Score, DateParticipation) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$user_id, $questionnaire_id, $totalScore]);

    // Réinitialiser le score pour le prochain quiz
    $_SESSION['quiz_score'] = 0;

    echo json_encode(["status" => "success", "score" => $totalScore, "total" => count($_SESSION['quiz_data'])]);
}
?>