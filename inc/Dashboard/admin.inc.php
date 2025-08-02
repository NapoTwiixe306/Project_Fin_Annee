<?php
global $pdo;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Connexion à la base
require_once __DIR__ . '/../config.php';
require_once 'bdd.php';

$message = '';
$error = '';

// Attribuer un emplacement
if (isset($_POST['assign_emplacement'])) {
    $brocanteur_id = intval($_POST['brocanteur_id']);
    $emplacement_id = intval($_POST['emplacement_id']);

    if ($emplacement_id > 0) {
        $stmt = $pdo->prepare("UPDATE brocanteurs SET emplacement_id = ? WHERE id = ?");
        $stmt->execute([$emplacement_id, $brocanteur_id]);
        $message = "Emplacement attribué avec succès.";
    } else {
        $error = "Veuillez choisir un emplacement valide.";
    }
}

// Annuler un emplacement
if (isset($_POST['cancel_emplacement'])) {
    $brocanteur_id = intval($_POST['brocanteur_id']);

    $stmt = $pdo->prepare("UPDATE brocanteurs SET emplacement_id = NULL WHERE id = ?");
    $stmt->execute([$brocanteur_id]);
    $message = "Emplacement annulé avec succès.";
}

// Supprimer un brocanteur
if (isset($_POST['delete_brocanteur'])) {
    $brocanteur_id = intval($_POST['brocanteur_id']);
    
    // Vérifier que le brocanteur n'a pas d'emplacement attribué
    $stmt = $pdo->prepare("SELECT emplacement_id FROM brocanteurs WHERE id = ?");
    $stmt->execute([$brocanteur_id]);
    $brocanteur = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($brocanteur && $brocanteur['emplacement_id'] === null) {
        try {
            // Commencer une transaction pour assurer l'intégrité des données
            $pdo->beginTransaction();
            
            // Supprimer d'abord les tokens de réinitialisation
            $stmt = $pdo->prepare("DELETE FROM reset_tokens WHERE brocanteur_id = ?");
            $stmt->execute([$brocanteur_id]);
            
            // Supprimer explicitement les objets du brocanteur
            $stmt = $pdo->prepare("DELETE FROM objets WHERE brocanteur_id = ?");
            $stmt->execute([$brocanteur_id]);
            $objetsSupprimes = $stmt->rowCount();
            
            // Supprimer le brocanteur
            $stmt = $pdo->prepare("DELETE FROM brocanteurs WHERE id = ?");
            $stmt->execute([$brocanteur_id]);
            
            if ($stmt->rowCount() > 0) {
                // Valider la transaction
                $pdo->commit();
                $message = "Brocanteur supprimé avec succès. " . $objetsSupprimes . " objet(s) supprimé(s).";
            } else {
                // Annuler la transaction
                $pdo->rollBack();
                $error = "Erreur lors de la suppression du brocanteur.";
            }
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $pdo->rollBack();
            $error = "Erreur lors de la suppression : " . $e->getMessage();
        }
    } else {
        $error = "Impossible de supprimer un brocanteur qui a un emplacement attribué.";
    }
}


$stmt = $pdo->query("SELECT * FROM brocanteurs ORDER BY nom");
$brocanteurs = $stmt->fetchAll();


$stmt = $pdo->query("SELECT id, numero FROM emplacements ORDER BY numero")->fetchAll(PDO::FETCH_ASSOC);
$emplacements = $pdo->query("SELECT id, numero FROM emplacements ORDER BY numero")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="admin-dashboard">
    <h1>Gestion des Brocanteurs</h1>

    <?php if (!empty($message)): ?>
        <div class="success-message">
            <strong>✅ Succès !</strong> <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="error-message">
            <strong>❌ Erreur :</strong> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="admin-stats">
        <h2>Statistiques</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= count($brocanteurs) ?></div>
                <div class="stat-label">Total brocanteurs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count(array_filter($brocanteurs, fn($b) => $b['emplacement_id'])) ?></div>
                <div class="stat-label">Avec emplacement</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count(array_filter($brocanteurs, fn($b) => !$b['emplacement_id'])) ?></div>
                <div class="stat-label">Sans emplacement</div>
            </div>
        </div>
    </div>

    <div class="brocanteurs-list">
        <?php foreach ($brocanteurs as $brocanteur): ?>
            <div class="brocanteur-card">
                <div class="brocanteur-header">
                    <h3><?= htmlspecialchars($brocanteur['prenom']) ?> <?= htmlspecialchars($brocanteur['nom']) ?></h3>
                    <div class="brocanteur-status <?= $brocanteur['emplacement_id'] ? 'assigned' : 'pending' ?>">
                        <?= $brocanteur['emplacement_id'] ? '✅ Emplacement attribué' : '⏳ En attente' ?>
                    </div>
                </div>
                
                <div class="brocanteur-info">
                    <p><strong>Email :</strong> <?= htmlspecialchars($brocanteur['email']) ?></p>
                    <p><strong>Emplacement actuel :</strong>
                        <?php if ($brocanteur['emplacement_id']): ?>
                            <?php
                            // Récupérer le numéro de l'emplacement
                            $stmt = $pdo->prepare("SELECT numero FROM emplacements WHERE id = ?");
                            $stmt->execute([$brocanteur['emplacement_id']]);
                            $emplacement = $stmt->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <span class="emplacement-number">
                                <?= htmlspecialchars($emplacement['numero'] ?? 'Emplacement ' . $brocanteur['emplacement_id']) ?>
                            </span>
                        <?php else: ?>
                            <span class="no-emplacement">Aucun emplacement attribué</span>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="brocanteur-actions">
                    <!-- Formulaire d'attribution d'emplacement -->
                    <form method="post" class="emplacement-form">
                        <input type="hidden" name="brocanteur_id" value="<?= $brocanteur['id'] ?>">
                        <div class="form-group">
                            <label for="emplacement_<?= $brocanteur['id'] ?>">Attribuer un emplacement :</label>
                            <select name="emplacement_id" id="emplacement_<?= $brocanteur['id'] ?>">
                                <option value="">-- Aucun emplacement --</option>
                                <?php foreach ($emplacements as $emp): ?>
                                    <option value="<?= $emp['id'] ?>" 
                                        <?= $brocanteur['emplacement_id'] == $emp['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($emp['numero']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="assign_emplacement" class="btn btn-primary">Attribuer</button>
                            <button type="submit" name="cancel_emplacement" class="btn btn-secondary">Annuler l'emplacement</button>
                        </div>
                    </form>

                    <!-- Formulaire de suppression -->
                    <?php if ($brocanteur['emplacement_id'] === null): ?>
                        <form method="post" class="delete-form" onsubmit="return confirm('⚠️ ATTENTION : Cette action est irréversible !\n\nÊtes-vous sûr de vouloir supprimer ce brocanteur ?\n\nCette action supprimera :\n• Le compte du brocanteur\n• Tous ses objets en vente\n• Ses tokens de réinitialisation\n\nCliquez sur OK pour confirmer la suppression.')">
                            <input type="hidden" name="brocanteur_id" value="<?= $brocanteur['id'] ?>">
                            <button type="submit" name="delete_brocanteur" class="btn btn-danger">
                                🗑️ Supprimer définitivement
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="delete-warning">
                            ⚠️ Impossible de supprimer : emplacement attribué
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
