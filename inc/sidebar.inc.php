<?php
global $pdo;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php'; 
require_once 'bdd.php';

$nom = $email = '';
$role = 'brocanteur'; // valeur par défaut

if (isset($_SESSION['brocanteur_id'])) {
    $id = $_SESSION['brocanteur_id'];
    $stmt = $pdo->prepare("SELECT nom, prenom, photo_profil, role FROM brocanteurs WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $nom = htmlspecialchars($user['prenom'] . ' ' . $user['nom']);
        $role = $user['role'];
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<aside class="sidebar">
    <section class="info">
        <article>
            <h1><?= $nom ?></h1>
        </article>
    </section>
    <ul>
        <li><a href="/index.php">Accueil</a></li>
        <li><a href="?page=modifier">Ajouter un objet</a></li>
        <li><a href="?page=objet">Afficher</a></li>
        <li><a href="?page=settings">Settings</a></li>

        <?php if ($role === 'admin'): ?>
            <li><a href="?page=admin">Administration</a></li>
        <?php endif; ?>
    </ul>
    <ul>
        <li>
            <form action="logout.inc.php" method="POST">
                <button type="submit">Déconnexion</button>
            </form>
        </li>
    </ul>
</aside>
</body>
</html>

