<?php session_start(); ?>
<?php require '../PHP/config.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #0a3d62;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar .left a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 18px;
        }
        .navbar .right {
            font-size: 16px;
        }
        .container {
            width: 80%;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h2 {
            color: #0a3d62;
        }
        h3 {
            color: #2980b9;
            margin-top: 20px;
        }
        h4 {
            color: #16a085;
            margin-top: 15px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            padding: 10px;
            background: #ecf0f1;
            margin: 5px 0;
            border-radius: 5px;
        }
        li a {
            text-decoration: none;
            color: #2c3e50;
            font-weight: bold;
        }
        li a:hover {
            color: #e74c3c;
        }
        .dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    background-color: white;
    min-width: 200px;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
    z-index: 999;
    border-radius: 5px;
    white-space: nowrap;
    padding: 5px 0;
}

.dropdown-content a {
    display: block;
    color: black;
    padding: 10px 16px;
    text-decoration: none;
}

.dropdown-content a:hover {
    background-color: #f1f1f1;
}

.dropdown:hover .dropdown-content {
    display: block;
}

    </style>
</head>
<body>
    <div class="navbar">
        <div class="left">
            <a href="#" class="nav-link">Mes Questionnaires</a>
        </div>
        <div class="right">
            <div class="dropdown">
                <span style="color: white; cursor: pointer; font-weight: bold;"> 
                    <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Invité'; ?>
                </span>
                <div class="dropdown-content">
    <a href="../HTML/profile.php">Profil</a>
    <a href="../PHP/quizScore.php">Historique des réponses</a>
    <a href="../HTML/graphes.php">Score</a>
    <?php if (isset($_SESSION['niveau']) && $_SESSION['niveau'] == 1) { ?>
        <a href="../Admin/AdminView.php">AdminView</a>
    <?php } ?>
    <a href="logout.php">Déconnexion</a>
</div>
            </div>
        </div>
    </div>
    
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
            
            // Récupération des thèmes
            $themesStmt = $pdo->query("SELECT ID, Nom FROM theme ORDER BY Nom");
            $themes = $themesStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Vérification de la catégorie sélectionnée
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