<?php
include("config.php");

header("Content-Type: application/json; charset=UTF-8"); // Assurer une réponse JSON correcte

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérification des champs
    $nomUtilisateur = isset($_POST["username"]) ? htmlspecialchars(trim($_POST["username"])) : null;
    $email = isset($_POST["email"]) ? filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL) : null;
    $motDePasse = isset($_POST["password"]) ? trim($_POST["password"]) : null;
    $nom = isset($_POST["nom"]) ? trim($_POST["nom"]) : null;
    $prenom = isset($_POST["prenom"]) ? trim($_POST["prenom"]) : null;

    if (empty($nomUtilisateur) || empty($email) || empty($motDePasse)) {
        echo json_encode(["status" => "error", "message" => "Tous les champs sont obligatoires."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Vérification de la validité de l'email
    if (!$email) {
        echo json_encode(["status" => "error", "message" => "L'adresse email n'est pas valide."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Vérifier la force du mot de passe
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{8,}$/', $motDePasse)) {
        echo json_encode(["status" => "error", "message" => "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un caractère spécial."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Hash du mot de passe sécurisé
    $motDePasseHashe = password_hash($motDePasse, PASSWORD_BCRYPT);

    // Vérifier si le nom d'utilisateur ou l'email existe déjà
    $check = $pdo->prepare("SELECT * FROM Utilisateur WHERE NomUtilisateur = ? OR email = ?");
    $check->execute([$nomUtilisateur, $email]);

    if ($check->rowCount() > 0) {
        echo json_encode(["status" => "error", "message" => "Ce nom d'utilisateur ou cet email est déjà pris."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Insérer l'utilisateur en base de données
    $stmt = $pdo->prepare("INSERT INTO Utilisateur (NomUtilisateur, email, MotDePasse, nom, prenom) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$nomUtilisateur, $email, $motDePasseHashe, $nom, $prenom])) {
        echo json_encode(["status" => "success", "message" => "Inscription réussie ! Redirection..."], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["status" => "error", "message" => "Erreur lors de l'inscription."], JSON_UNESCAPED_UNICODE);
    }
}
?>