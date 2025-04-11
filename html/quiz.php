<?php
session_start();
require '../PHP/config.php';

if (!isset($_SESSION['username']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Utilisateur non connecté."]);
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=gestionQuestionnaires;charset=utf8', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$questionnaireID = intval($_GET['id']);

// Toujours récupérer les questions depuis la base pour avoir les mises à jour
$stmt = $pdo->prepare("SELECT * FROM question WHERE QuestionnaireID = ?");
$stmt->execute([$questionnaireID]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$questions) {
    echo "<script>alert('Aucune question trouvée pour ce questionnaire.'); window.location.href = 'dashboard.php';</script>";
    exit();
}

// Ne plus stocker les questions en session pour éviter les données obsolètes
$_SESSION['quiz_index'] = 0;
$_SESSION['quiz_score'] = 0;
$_SESSION['quiz_data'] = $questions;
$_SESSION['questionnaire_id'] = $questionnaireID; // Stocke l'ID du questionnaire

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Quiz</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h2 id="question-title">Question</h2>
        <p class="timer">Temps restant : <span id="question-timer">10</span> secondes</p>
        <form id="quiz-form">
            <div id="answers-container"></div>
            <br>
            <button type="button" id="next-btn" class="btn" disabled>Suivant</button>
        </form>
    </div>

    <script>
        let questions = <?php echo json_encode($questions); ?>;
        let currentQuestionIndex = 0;
        let questionTime = 10; // Temps par question
        let questionTimer;

        console.log("Questions chargées :", questions);

        function showQuestion() {
            if (currentQuestionIndex >= questions.length) {
                submitQuiz();
                return;
            }

            let questionData = questions[currentQuestionIndex];
            document.getElementById('question-title').innerText = questionData.Libelle;

            let answersHTML = "";
            if (parseInt(questionData.VraiFaux) === 1 || parseInt(questionData.FauxVrai) === 1) {
                answersHTML = `
                    <label>
                        <input type="radio" name="answer" value="Vrai" onclick="enableNextButton()"> Vrai
                    </label><br>
                    <label>
                        <input type="radio" name="answer" value="Faux" onclick="enableNextButton()"> Faux
                    </label>`;
            } else {
                for (let i = 1; i <= 3; i++) {
                    if (questionData["Reponse" + i]) {
                        answersHTML += `
                            <label>
                                <input type="radio" name="answer" value="${questionData["Reponse" + i]}" onclick="enableNextButton()"> 
                                ${questionData["Reponse" + i]}
                            </label><br>`;
                    }
                }
            }

            document.getElementById("answers-container").innerHTML = answersHTML;
            document.getElementById("next-btn").disabled = true;

            resetQuestionTimer();
        }

        function enableNextButton() {
            document.getElementById("next-btn").disabled = false;
        }

        function resetQuestionTimer() {
    clearInterval(questionTimer); // Arrêter tout timer existant
    let timeLeft = questionTime;
    
    // Mise à jour immédiate de l'affichage du temps
    document.getElementById("question-timer").innerText = timeLeft;

    // Lancer un nouvel intervalle
    questionTimer = setInterval(() => {
        timeLeft--;
        document.getElementById("question-timer").innerText = timeLeft;

        if (timeLeft <= 0) {
            clearInterval(questionTimer);
            nextQuestion();
        }
    }, 1000);
}

function nextQuestion() {
    let selectedAnswer = document.querySelector("input[name='answer']:checked");

    if (selectedAnswer) {
        let answerValue = selectedAnswer.value.trim().toLowerCase();
        let questionID = questions[currentQuestionIndex].ID;

        console.log("Envoi réponse :", { answer: answerValue, question_id: questionID });

        fetch('quizScoreNow.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'answer=' + encodeURIComponent(answerValue) + '&question_id=' + questionID
        })
        .then(response => response.text())
        .then(data => console.log("Réponse reçue du serveur :", data));
    }

    currentQuestionIndex++;

    if (currentQuestionIndex >= questions.length) {
        submitQuiz();
    } else {
        showQuestion();
        resetQuestionTimer();
    }
}

function submitQuiz() {
    clearInterval(questionTimer);
    console.log("Soumission du quiz en cours...");

    fetch('quizScoreNow.php?end=1')
        .then(response => response.json())
        .then(data => {
            console.log("Réponse reçue du serveur :", data);

            if (data.status === "success") {
                document.body.innerHTML = `
                    <div style="text-align:center; margin-top: 50px;">
                        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); display: inline-block;">
                            <h2 style="color: #0a3d62;">Quiz Terminé !</h2>
                            <p style="font-size: 18px; font-weight: bold;">Votre score : ${data.score} / ${data.total}</p>
                            <a href="dashboard.php" style="margin-top: 20px; padding: 10px 20px; font-size: 16px; background-color: green; color: white; text-decoration: none; border-radius: 5px;">Retour au Dashboard</a>
                        </div>
                    </div>`;
            } else {
                console.error("Erreur du serveur :", data.message);
            }
        })
        .catch(error => {
            console.error("Erreur lors de la soumission du quiz :", error);
        });
}
        document.getElementById("next-btn").addEventListener("click", nextQuestion);
        showQuestion();
    </script>
</body>
</html>