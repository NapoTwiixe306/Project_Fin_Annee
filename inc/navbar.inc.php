
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['brocanteur_id']); 
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation sémantique</title>
</head>
<body>
<nav>
    <a href="../index.php" class="logo">FAP</a>

    <input type="checkbox" id="check-input" class="check-input">
    <label for="check-input" class="check-label">☰</label>
    <ul>
        <li><a href="../index.php">Accueil</a></li>
        <li><a href="./brocanteurs.php">Brocanteurs</a></li>
        <li><a href="./objet.php">En Vente</a></li>
        <li><a href="./contact.php">Contact</a></li>
        <li class="register">
            <?php if ($isLoggedIn): ?>
                <button>
                    <a href="./brocanteurs_login.php">Dashboard</a>
                    <img src="../public/right-arrow.png" alt="Flèche droite" style="width: 24px; height: 25px;">
                </button>
            <?php else: ?>
                <button>
                    <a href="./connexion.php">Connexion</a>
                    <img src="../public/right-arrow.png" alt="Flèche droite" style="width: 24px; height: 25px;">
                    </button>
            <?php endif; ?>
        </li>

    </ul>
</nav>
</body>
</html>
