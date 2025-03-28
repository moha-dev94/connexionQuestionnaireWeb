<?php
include("config.php");

header("Content-Type: application/json; charset=UTF-8");

try {
    // Vérification de la méthode POST
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        echo json_encode(["status" => "error", "message" => "Requête invalide."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Vérifier si la connexion PDO est bien établie
    if (!isset($pdo)) {
        echo json_encode(["status" => "error", "message" => "Erreur de connexion à la base de données."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Récupération et nettoyage du nom d'utilisateur
    $username = isset($_POST["username"]) ? htmlspecialchars(trim($_POST["username"])) : null;

    if (empty($username)) {
        echo json_encode(["status" => "error", "message" => "Veuillez entrer un nom d'utilisateur."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Vérifier si l'utilisateur existe et récupérer son email
    $stmt = $pdo->prepare("SELECT email FROM Utilisateur WHERE NomUtilisateur = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "Utilisateur non trouvé."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Vérifier si un email est associé à cet utilisateur
    if (empty($user["email"])) {
        echo json_encode(["status" => "no_email", "message" => "Aucun email associé à ce nom d'utilisateur."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Générer un token unique et définir une date d'expiration (24h)
    $token = bin2hex(random_bytes(32));
    $date_limite = date("Y-m-d H:i:s", strtotime("+24 hours"));

    // Mettre à jour le token et la date limite dans la base de données
    $update = $pdo->prepare("UPDATE Utilisateur SET token = ?, date_limite = ? WHERE NomUtilisateur = ?");
    if (!$update->execute([$token, $date_limite, $username])) {
        error_log("Erreur SQL : " . implode(" | ", $update->errorInfo()));
        echo json_encode(["status" => "error", "message" => "Erreur lors de la mise à jour du token."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Préparer l'email
    $reset_link = "http://localhost/connexionQuestionnaireWeb/html/reinitMdp.html?token=" . $token;
    $message = "Bonjour,\n\nCliquez sur ce lien pour réinitialiser votre mot de passe :\n$reset_link\n\nCe lien expire dans 24 heures.";

    $headers = "From: no-reply@exemple.com\r\nContent-Type: text/plain; charset=UTF-8";

    // Envoyer l'email
    if (mail($user["email"], "Réinitialisation du mot de passe", $message, $headers)) {
        echo json_encode(["status" => "success", "message" => "Un email a été envoyé avec un lien de réinitialisation."], JSON_UNESCAPED_UNICODE);
    } else {
        error_log("Échec de l'envoi de l'email à : " . $user["email"]);
        echo json_encode(["status" => "error", "message" => "Erreur lors de l'envoi de l'email. Vérifiez les paramètres du serveur."], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    error_log("Exception attrapée : " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Une erreur interne est survenue."], JSON_UNESCAPED_UNICODE);
}
?>