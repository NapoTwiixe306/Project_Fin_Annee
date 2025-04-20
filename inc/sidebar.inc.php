<?php
global $pdo;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php'; 
require_once 'bdd.php';

$nom = $email = '';

if (isset($_SESSION['brocanteur_id'])) {
    $id = $_SESSION['brocanteur_id'];
    $stmt = $pdo->prepare("SELECT nom, prenom FROM brocanteurs WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $nom = htmlspecialchars($user['prenom'] . ' ' . $user['nom']);
    }
}
?>

<aside class="sidebar">
    <section class="info">
        <article class="avatar"></article>
        <article>
            <h1><?= $nom ?></h1>
        </article>
    </section>
    <ul>
        <li><a href="/">Accueil</a></li>
        <li><a href="?page=dashboard">Dashboard</a></li>
        <li><a href="?page=modifier">Modifier un objet</a></li>
        <li><a href="?page=objet">Afficher</a></li>
        <li><a href="?page=settings">Settings</a></li>
    </ul>
    <ul>
        <li>
            <form action="logout.inc.php" method="POST">
                <button type="submit">Déconnexion</button>
            </form>
        </li>
    </ul>
</aside>

