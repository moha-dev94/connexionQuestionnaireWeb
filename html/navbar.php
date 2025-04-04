<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<div class="navbar">
    <div class="left">
        <a href="../HTML/dashboard.php" class="nav-link">Mes Questionnaires</a>
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
                <?php if (isset($_SESSION['niveau']) && $_SESSION['niveau'] == 1): ?>
                    <a href="../Admin/AdminView.php">AdminView</a>
                <?php endif; ?>
                <a href="logout.php">Déconnexion</a>
            </div>
        </div>
    </div>
</div>