
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['brocanteur_id']); 
$userName = '';

if ($isLoggedIn) {
    global $pdo;
    require_once 'bdd.php';
    $stmt = $pdo->prepare("SELECT nom, prenom FROM brocanteurs WHERE id = ?");
    $stmt->execute([$_SESSION['brocanteur_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $userName = htmlspecialchars($user['prenom'] . ' ' . $user['nom']);
    }
}
?>

<nav>
    <a href="./" class="logo">FAP</a>

    <input type="checkbox" id="check-input" class="check-input">
    <label for="check-input" class="check-label">☰</label>
    <ul>
        <li><a href="./">Accueil</a></li>
        <li><a href="./brocanteurs.php">Brocanteurs</a></li>
        <li><a href="./objet.php">En Vente</a></li>
        <li><a href="./contact.php">Contact</a></li>
        <?php if ($isLoggedIn): ?>
            <li class="user-info">
                <span class="user-name">Bonjour, <?= $userName ?></span>
            </li>
            <li class="register">
                <button>
                    <a href="./brocanteurs_login.php">Dashboard</a>
                    <img src="../public/right-arrow.png" alt="Flèche droite" style="width: 24px; height: 25px;">
                </button>
            </li>
            <li class="logout">
                <form action="./deconnexion.php" method="POST" style="display: inline;">
                    <button type="submit" class="logout-btn">Déconnexion</button>
                </form>
            </li>
        <?php else: ?>
            <li class="register">
                <button>
                    <a href="./connexion.php">Connexion</a>
                    <img src="../public/right-arrow.png" alt="Flèche droite" style="width: 24px; height: 25px;">
                </button>
            </li>
        <?php endif; ?>
    </ul>
</nav>
