<?php
session_start();

try {
    // Connexion à la base de données
    $pdo = new PDO('mysql:host=localhost;dbname=gestionQuestionnaires;charset=utf8', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Exception $e) {
    die(json_encode(["status" => "error", "message" => "Erreur de connexion à la base de données."]));
}

header("Content-Type: application/json; charset=UTF-8"); // Définir la réponse en JSON

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = isset($_POST["username"]) ? htmlspecialchars(trim($_POST["username"])) : null;
    $password = isset($_POST["password"]) ? $_POST["password"] : null;

    if (empty($username) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Tous les champs sont obligatoires."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Vérifier si l'utilisateur existe
    $stmt = $pdo->prepare("SELECT ID, NomUtilisateur, MotDePasse, niveau FROM Utilisateur WHERE NomUtilisateur = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user["MotDePasse"])) {
        // Stocker l'utilisateur en session
        $_SESSION["user_id"] = $user["ID"]; // Stocke l'ID utilisateur
        $_SESSION["username"] = $user["NomUtilisateur"];
        $_SESSION["niveau"] = $user["niveau"]; // Stockage du niveau de l'utilisateur

        echo json_encode(["status" => "success", "message" => "Connexion réussie. Redirection en cours..."], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["status" => "error", "message" => "Nom d'utilisateur ou mot de passe incorrect."], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée."], JSON_UNESCAPED_UNICODE);
}
?>