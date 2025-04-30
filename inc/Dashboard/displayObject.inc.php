<?php
global $pdo;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once 'bdd.php';

if (empty($_SESSION['brocanteur_id'])) {
    header("Location: login.php");
    exit();
}

$brocanteur_id = $_SESSION['brocanteur_id'];
$objets = [];
$objet_a_modifier = null;
$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Demande d'ouverture du "modal"
    if (isset($_POST['modifier_objet'])) {
        $objet_id = intval($_POST['objet_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM objets WHERE id = ? AND brocanteur_id = ?");
        $stmt->execute([$objet_id, $brocanteur_id]);
        $objet_a_modifier = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Traitement de la modification
    if (isset($_POST['valider_modification'])) {
        $objet_id = intval($_POST['objet_id'] ?? 0);
        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($titre && $description) {
            $stmt = $pdo->prepare("UPDATE objets SET titre = ?, description = ? WHERE id = ? AND brocanteur_id = ?");
            $stmt->execute([$titre, $description, $objet_id, $brocanteur_id]);
            $success = "Objet mis à jour.";
        } else {
            $error = "Tous les champs doivent être remplis.";
        }
    }
}
// Traitement de la suppression
if (isset($_POST['supprimer'])) {
    $objet_id = intval($_POST['objet_id'] ?? 0);

    // Vérifie que l'objet appartient bien au brocanteur connecté
    $stmt = $pdo->prepare("DELETE FROM objets WHERE id = ? AND brocanteur_id = ?");
    $stmt->execute([$objet_id, $brocanteur_id]);

    if ($stmt->rowCount() > 0) {
        $success = "Objet supprimé avec succès.";
    } else {
        $error = "Échec de la suppression de l'objet.";
    }
}


// Récupérer les objets après modification
$stmt = $pdo->prepare("SELECT * FROM objets WHERE brocanteur_id = ? ORDER BY created_at DESC");
$stmt->execute([$brocanteur_id]);
$objets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($success): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>
<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h2>Mes objets</h2>
<ul class="objets-liste">
    <?php foreach ($objets as $objet): ?>
        <li class="objet-item">
            <div class="objet-details">
                <h3><?= htmlspecialchars($objet['titre']) ?></h3>
                <p><?= htmlspecialchars($objet['description']) ?></p>
                <span class="categorie">Catégorie : <?= htmlspecialchars($objet['categorie']) ?></span>
            </div>
            <form action="" method="POST" style="display:inline;">
                <input type="hidden" name="objet_id" value="<?= $objet['id'] ?>">
                <button type="submit" name="modifier_objet">Modifier</button>
            </form>
            <form action="" method="POST" class="supprimer-form">
                <input type="hidden" name="objet_id" value="<?= $objet['id'] ?>">
                <button type="submit" name="supprimer" class="supprimer-btn">Supprimer</button>
            </form>
        </li>
    <?php endforeach; ?>
</ul>

<?php if ($objet_a_modifier): ?>
    <div class="modal">
        <h3>Modifier l’objet</h3>
        <form method="POST" action="">
            <input type="hidden" name="objet_id" value="<?= $objet_a_modifier['id'] ?>">

            <label for="titre">Titre :</label>
            <input type="text" name="titre" id="titre" value="<?= htmlspecialchars($objet_a_modifier['titre']) ?>" required>

            <label for="description">Description :</label>
            <textarea name="description" id="description" required><?= htmlspecialchars($objet_a_modifier['description']) ?></textarea>

            <button type="submit" name="valider_modification">Enregistrer</button>
        </form>
    </div>
<?php endif; ?>

<style>
    
</style>
