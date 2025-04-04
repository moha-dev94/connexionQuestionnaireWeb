
<?php session_start(); ?>
<?php require '../PHP/config.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
   
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2>Bienvenue sur votre tableau de bord</h2>
        <p>Ici, vous pouvez gérer vos questionnaires et consulter vos résultats.</p>

        <?php
        if (!isset($_SESSION['username'])) {
            echo "<p>Veuillez vous connecter pour voir vos questionnaires.</p>";
        }
        if (!isset($_SESSION['user_id'])) {
            echo "<p>Erreur : utilisateur non connecté.</p>";
            exit();
        } else {
            $user_id = $_SESSION['user_id'];
            $pdo = new PDO('mysql:host=localhost;dbname=gestionQuestionnaires;charset=utf8', 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $themesStmt = $pdo->query("SELECT ID, Nom FROM theme ORDER BY Nom");
            $themes = $themesStmt->fetchAll(PDO::FETCH_ASSOC);

            $selectedTheme = isset($_GET['theme']) ? (int) $_GET['theme'] : 0;
            $query = "SELECT q.ID, q.Libelle, q.ThemeID, t.Nom as ThemeNom FROM questionnaire q 
                      JOIN theme t ON q.ThemeID = t.ID";
            if ($selectedTheme) {
                $query .= " WHERE q.ThemeID = :themeID";
            }
            $query .= " ORDER BY q.ThemeID";

            $stmt = $pdo->prepare($query);
            if ($selectedTheme) {
                $stmt->bindParam(':themeID', $selectedTheme, PDO::PARAM_INT);
            }
            $stmt->execute();
            $questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo '<div class="filter">';
            echo '<form method="GET" action="">';
            echo '<label for="theme">Filtrer par catégorie :</label>';
            echo '<select name="theme" id="theme" onchange="this.form.submit()">';
            echo '<option value="0">Toutes</option>';
            foreach ($themes as $theme) {
                $selected = ($theme['ID'] == $selectedTheme) ? 'selected' : '';
                echo "<option value='{$theme['ID']}' $selected>{$theme['Nom']}</option>";
            }
            echo '</select>';
            echo '</form>';
            echo '</div>';

            echo "<h3>Mes Questionnaires</h3>";
            if (empty($questionnaires)) {
                echo "<p>Aucun questionnaire trouvé pour cette catégorie.</p>";
            } else {
                $currentTheme = null;
                foreach ($questionnaires as $q) {
                    if ($currentTheme !== $q['ThemeNom']) {
                        if ($currentTheme !== null) echo "</ul>";
                        echo "<h4>{$q['ThemeNom']}</h4><ul>";
                        $currentTheme = $q['ThemeNom'];
                    }
                    echo "<li><a href='quiz.php?id={$q['ID']}'>{$q['Libelle']}</a></li>";
                }
                echo "</ul>";
            }
        }
        ?>
    </div>
</body>
</html>