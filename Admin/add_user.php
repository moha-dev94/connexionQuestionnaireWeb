<?php
include("../php/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nomUtilisateur = htmlspecialchars(trim($_POST["NomUtilisateur"]));
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $motDePasse = password_hash($_POST["MotDePasse"], PASSWORD_BCRYPT);
    $niveau = intval($_POST["niveau"]);

    // Vérifier si l'email existe déjà
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM Utilisateur WHERE email = ?");
    $checkStmt->execute([$email]);
    $emailExists = $checkStmt->fetchColumn();

    if ($emailExists > 0) {
        // Redirection avec un message d'erreur si l'email est déjà utilisé
        header("Location: AdminView.php?error=email_exists");
        exit();
    }

    // Vérifier que tous les champs sont remplis
    if (!empty($nomUtilisateur) && !empty($email) && !empty($_POST["MotDePasse"])) {
        $stmt = $pdo->prepare("INSERT INTO Utilisateur (NomUtilisateur, email, MotDePasse, niveau) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nomUtilisateur, $email, $motDePasse, $niveau]);

        // Redirection avec message de succès
        header("Location: AdminView.php?user_added=1");
        exit();
    } else {
        header("Location: AdminView.php?error=missing_fields");
        exit();
    }
}
?>