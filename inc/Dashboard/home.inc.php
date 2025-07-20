<?php
require_once __DIR__ . '/../../src/autoload.php';
use Controllers\BrocanteurController;

$controller = new BrocanteurController();
$controller->requireAuth();

$user = $controller->getCurrentUser();
?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Espace brocanteur</title>
</head>
<body>
    <h1>Espace brocanteur</h1>
    <p>Bienvenue, <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?> !</p>
    
    <div class="status-section">
        <h2>Statut de votre inscription</h2>
        <?php if (!empty($user['emplacement'])): ?>
            <p class="status-assigned">✅ <strong>Emplacement attribué n° <?= htmlspecialchars($user['emplacement']) ?></strong></p>
            <?php if (!empty($user['zone'])): ?>
                <p>Zone: <?= htmlspecialchars($user['zone']) ?></p>
            <?php endif; ?>
        <?php else: ?>
            <p class="status-pending">⏳ <strong>Emplacement pas encore attribué</strong></p>
            <p>Votre demande d'inscription est en cours de traitement. Vous recevrez une notification dès qu'un emplacement vous sera attribué.</p>
        <?php endif; ?>
    </div>
    
    <div class="quick-actions">
        <h2>Actions rapides</h2>
        <ul>
            <li><a href="admin_dashboard.php?page=objet&action=create">Ajouter un objet</a></li>
            <li><a href="admin_dashboard.php?page=objet&action=list">Gérer mes objets</a></li>
            <li><a href="admin_dashboard.php?page=settings">Modifier mon profil</a></li>
        </ul>
    </div>
</body>
</html>
