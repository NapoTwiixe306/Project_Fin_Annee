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
        <!-- Formulaire d'attribution / annulation d'emplacement -->
        <form method="post">
            <input type="hidden" name="brocanteur_id" value="<?= $brocanteur['id'] ?>">
            <label>Attribuer un emplacement :</label><br>
            <select name="emplacement_id">
                <option value="">-- Aucun emplacement --</option>
                <?php foreach ($emplacements as $emp): ?>
                    <option value="<?= $emp['id'] ?>" 
                        <?= $brocanteur['emplacement_id'] == $emp['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($emp['numero']) ?>
                    </option>
                <?php endforeach; ?>
            </select><br>

            <!-- Bouton pour attribuer -->
            <button type="submit" name="assign_emplacement">Attribuer</button>

            <!-- Bouton pour annuler l'emplacement -->
            <button type="submit" name="cancel_emplacement" style="margin-left: 10px;">Annuler l'emplacement</button>
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
