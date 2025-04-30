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

// Attribuer / Annuler un emplacement
if (isset($_POST['assign_emplacement'])) {
    $brocanteur_id = intval($_POST['brocanteur_id']);
    $emplacement_id = intval($_POST['emplacement_id']);

    if ($emplacement_id > 0) {
        $stmt = $pdo->prepare("UPDATE brocanteurs SET emplacement_id = ? WHERE id = ?");
        $stmt->execute([$emplacement_id, $brocanteur_id]);
        $message = "Emplacement attribué avec succès.";
    } else {
        // Annuler l'emplacement
        $stmt = $pdo->prepare("UPDATE brocanteurs SET emplacement_id = NULL WHERE id = ?");
        $stmt->execute([$brocanteur_id]);
        $message = "Emplacement annulé avec succès.";
    }
}

// Supprimer un brocanteur
if (isset($_POST['delete_brocanteur'])) {
    $brocanteur_id = intval($_POST['brocanteur_id']);

    // Vérifier qu'il n'a PAS d'emplacement
    $stmt = $pdo->prepare("SELECT emplacement_id FROM brocanteurs WHERE id = ?");
    $stmt->execute([$brocanteur_id]);
    $brocanteur = $stmt->fetch();

    if ($brocanteur && $brocanteur['emplacement_id'] === null) {
        // Supprimer tous ses objets d'abord
        $stmt = $pdo->prepare("DELETE FROM objets WHERE brocanteur_id = ?");
        $stmt->execute([$brocanteur_id]);
        // Puis supprimer le brocanteur
        $stmt = $pdo->prepare("DELETE FROM brocanteurs WHERE id = ?");
        $stmt->execute([$brocanteur_id]);
        $message = "Brocanteur supprimé avec succès.";
    } else {
        $error = "Impossible de supprimer : un emplacement est encore attribué.";
    }
}

$stmt = $pdo->query("SELECT * FROM brocanteurs ORDER BY nom");
$brocanteurs = $stmt->fetchAll();


$stmt = $pdo->query("SELECT id, numero FROM emplacements ORDER BY numero")->fetchAll(PDO::FETCH_ASSOC);
$emplacements = $pdo->query("SELECT id, numero FROM emplacements ORDER BY numero")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Gestion des Brocanteurs</title>
    <style>
        h1 { margin-bottom: 20px; }
        .message { color: green; }
        .error { color: red; }
        .brocanteur { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; }
        form { margin-top: 10px; }
        select, button { padding: 5px; margin-top: 5px; }
    </style>
</head>
<body>

<h1>Gestion des Brocanteurs</h1>

<?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>
<?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

<?php foreach ($brocanteurs as $brocanteur): ?>
    <div class="brocanteur">
        <h3><?= htmlspecialchars($brocanteur['prenom']) ?> <?= htmlspecialchars($brocanteur['nom']) ?></h3>
        <p><strong>Email :</strong> <?= htmlspecialchars($brocanteur['email']) ?></p>
        <p><strong>Emplacement actuel :</strong>
            <?= $brocanteur['emplacement_id'] ? htmlspecialchars($brocanteur['emplacement_id']) : 'Aucun' ?>
        </p>

        <!-- Formulaire d'attribution d'emplacement -->
        <form method="post">
            <input type="hidden" name="brocanteur_id" value="<?= $brocanteur['id'] ?>">
            <label>Attribuer / Modifier emplacement :</label><br>
            <select name="emplacement_id">
                <option value="">-- Aucun emplacement --</option>
                <?php foreach ($emplacements as $emp): ?>
                    <option value="<?= $emp['id'] ?>" 
                        <?= $brocanteur['emplacement_id'] == $emp['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($emp['numero']) ?>
                    </option>
                <?php endforeach; ?>
            </select><br>
            <button type="submit" name="assign_emplacement">Attribuer / Annuler</button>
        </form>

        <!-- Formulaire de suppression -->
        <form method="post" onsubmit="return confirm('Confirmer la suppression de ce brocanteur ?')">
            <input type="hidden" name="brocanteur_id" value="<?= $brocanteur['id'] ?>">
            <button type="submit" name="delete_brocanteur" style="background-color: red; color: white; margin-top: 10px;">
                Supprimer définitivement
            </button>
        </form>
    </div>
<?php endforeach; ?>

</body>
</html>
