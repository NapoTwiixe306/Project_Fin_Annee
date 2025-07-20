<?php
global $pdo;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php'; 
require_once 'bdd.php';

$nomComplet = '';
$photo = ''; // valeur par défaut

if (isset($_SESSION['brocanteur_id'])) {
    $id = $_SESSION['brocanteur_id'];
    $stmt = $pdo->prepare("SELECT nom, prenom, photo_profil, role FROM brocanteurs WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $nomComplet = htmlspecialchars($user['prenom'] . ' ' . $user['nom']);
        $role = $user['role'];

        if (!empty($user['photo_profil'])) {
            $photo = './uploads/' . htmlspecialchars($user['photo_profil']);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar utilisateur</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-left">
        <img src="<?= $photo ?>" alt="Photo de profil" class="avatar">
        <span class="username"><?= $nomComplet ?></span>
    </div>
    <input type="checkbox" id="nav-toggle" class="nav-toggle">
    <label for="nav-toggle" class="nav-toggle-label">&#9776;</label>
    <ul class="nav-links">
        <li><a href="/">Accueil</a></li>
        <li><a href="admin_dashboard.php?page=objet&action=create">Ajouter un objet</a></li>
        <li><a href="admin_dashboard.php?page=objet">Mes objets</a></li>
        <li><a href="admin_dashboard.php?page=settings">Paramètres</a></li>
        <?php if (isset($role) && $role === 'admin'): ?>
            <li><a href="admin_dashboard.php?page=admin">Administration</a></li>
        <?php endif; ?>
        <li>
            <form action="inc/logout.inc.php" method="POST">
                <button type="submit" class="logout-btn">Déconnexion</button>
            </form>
        </li>
    </ul>
</nav>
