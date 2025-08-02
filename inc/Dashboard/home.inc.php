<?php
// Vérifier l'authentification
if (!isset($_SESSION['brocanteur_id'])) {
    header('Location: connexion.php');
    exit();
}

// Récupérer les informations de l'utilisateur
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../../bdd.php';

$brocanteur_id = $_SESSION['brocanteur_id'];
$stmt = $pdo->prepare("SELECT b.*, e.numero as emplacement, e.zone 
                       FROM brocanteurs b 
                       LEFT JOIN emplacements e ON b.emplacement_id = e.id 
                       WHERE b.id = ?");
$stmt->execute([$brocanteur_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="dashboard-home">
    <h1>Espace brocanteur</h1>
    <p class="welcome-message">Bienvenue, <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?> !</p>
    
    <div class="status-section">
        <h2>Statut de votre inscription</h2>
        <?php if (!empty($user['emplacement'])): ?>
            <div class="status-card assigned">
                <div class="status-icon">✅</div>
                <div class="status-content">
                    <h3>Emplacement attribué</h3>
                    <p class="emplacement-number">N° <?= htmlspecialchars($user['emplacement']) ?></p>
                    <?php if (!empty($user['zone'])): ?>
                        <p class="zone-info">Zone: <?= htmlspecialchars($user['zone']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="status-card pending">
                <div class="status-icon">⏳</div>
                <div class="status-content">
                    <h3>En attente d'attribution</h3>
                    <p>Votre demande d'inscription est en cours de traitement. Vous recevrez une notification dès qu'un emplacement vous sera attribué.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="quick-actions">
        <h2>Actions rapides</h2>
        <div class="actions-grid">
            <a href="admin_dashboard.php?page=objet&action=create" class="action-card">
                <div class="action-icon">➕</div>
                <h3>Ajouter un objet</h3>
                <p>Publiez un nouvel objet à vendre</p>
            </a>
            <a href="admin_dashboard.php?page=objet&action=list" class="action-card">
                <div class="action-icon">📋</div>
                <h3>Gérer mes objets</h3>
                <p>Modifiez ou supprimez vos objets</p>
            </a>
            <a href="admin_dashboard.php?page=settings" class="action-card">
                <div class="action-icon">⚙️</div>
                <h3>Modifier mon profil</h3>
                <p>Mettez à jour vos informations</p>
            </a>
        </div>
    </div>
    
    <div class="dashboard-stats">
        <h2>Statistiques</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= count($user['objects'] ?? []) ?></div>
                <div class="stat-label">Objets en vente</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $user['visible'] ? 'Oui' : 'Non' ?></div>
                <div class="stat-label">Profil visible</div>
            </div>
        </div>
    </div>
</div>
