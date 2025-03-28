<?php
include("config.php");

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST["token"] ?? null;
    $password = $_POST["password"] ?? null;
    $confirm_password = $_POST["confirm_password"] ?? null;

    if (!$token || !$password || !$confirm_password) {
        echo json_encode(["status" => "error", "message" => "Tous les champs sont obligatoires."]);
        exit;
    }

    // Vérifier le token et la date limite
    $stmt = $pdo->prepare("SELECT NomUtilisateur FROM Utilisateur WHERE token = ? AND date_limite >= NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "Lien invalide ou expiré."]);
        exit;
    }

    // Mise à jour du mot de passe
    $password_hashed = password_hash($password, PASSWORD_BCRYPT);
    $update = $pdo->prepare("UPDATE Utilisateur SET MotDePasse = ?, token = NULL, date_limite = NULL WHERE token = ?");
    $update->execute([$password_hashed, $token]);

    echo json_encode(["status" => "success", "message" => "Mot de passe mis à jour."]);
}
?>