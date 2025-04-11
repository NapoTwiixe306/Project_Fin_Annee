<?php
global $pdo;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once 'bdd.php';

if (empty($_SESSION['brocanteur_id'])) {
    header("Location: login.php");
    exit();
}

$brocanteur_id = $_SESSION['brocanteur_id'];
$error = "";
$success = "";

// Fonction pour récupérer les catégories
function getCategories(PDO $pdo): array {
    $stmt = $pdo->query("SELECT id, nom FROM categories ORDER BY nom ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour ajouter un objet
function ajouterObjet(PDO $pdo, int $brocanteur_id, string $titre, string $description, int $categorie_id): string {
    // Vérifie que la catégorie existe
    $stmt = $pdo->prepare("SELECT nom FROM categories WHERE id = ?");
    $stmt->execute([$categorie_id]);
    $nom_categorie = $stmt->fetchColumn();

    if (!$nom_categorie) {
        return "La catégorie sélectionnée n'existe pas.";
    }

    // Insertion de l'objet
    $sql = "INSERT INTO objets (brocanteur_id, titre, description, categorie, categorie_id, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([$brocanteur_id, $titre, $description, $nom_categorie, $categorie_id]);

    return $success ? "Objet ajouté avec succès." : "Erreur lors de l'ajout de l'objet.";
}

// Fonction pour supprimer un objet
function supprimerObjet(PDO $pdo, int $objet_id, int $brocanteur_id): string {
    $stmt = $pdo->prepare("DELETE FROM objets WHERE id = ? AND brocanteur_id = ?");
    return $stmt->execute([$objet_id, $brocanteur_id])
        ? "Objet supprimé avec succès."
        : "Erreur lors de la suppression de l'objet.";
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['ajouter'])) {
        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $categorie_id = intval($_POST['categorie_id'] ?? 0);

        if (empty($titre) || empty($description) || empty($categorie_id)) {
            $error = "Tous les champs sont obligatoires.";
        } else {
            $success = ajouterObjet($pdo, $brocanteur_id, $titre, $description, $categorie_id);
        }
    }

    if (isset($_POST['supprimer'])) {
        $objet_id = intval($_POST['objet_id'] ?? 0);
        $success = supprimerObjet($pdo, $objet_id, $brocanteur_id);
    }
}

// Récupération des données
$categories = getCategories($pdo);
$stmt = $pdo->prepare("SELECT * FROM objets WHERE brocanteur_id = ? ORDER BY created_at DESC");
$stmt->execute([$brocanteur_id]);
$objets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un objet</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h1>Ajouter un objet</h1>
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    <form action="" method="POST">
        <label for="titre">Titre :</label>
        <input type="text" name="titre" id="titre" required>

        <label for="description">Description :</label>
        <textarea name="description" id="description" required></textarea>

        <label for="categorie">Catégorie :</label>
        <select name="categorie_id" id="categorie" required>
            <option value="">Sélectionnez une catégorie</option>
            <?php foreach ($categories as $categorie): ?>
                <option value="<?= $categorie['id'] ?>"><?= htmlspecialchars($categorie['nom']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="ajouter">Ajouter</button>
    </form>

    <h2>Mes objets</h2>
    <ul class="objets-liste">
        <?php foreach ($objets as $objet): ?>
            <li class="objet-item">
                <div class="objet-details">
                    <h3><?= htmlspecialchars($objet['titre']) ?></h3>
                    <p><?= htmlspecialchars($objet['description']) ?></p>
                    <span class="categorie">Catégorie : <?= htmlspecialchars($objet['categorie']) ?></span>
                </div>
                <form action="" method="POST" class="supprimer-form">
                    <input type="hidden" name="objet_id" value="<?= $objet['id'] ?>">
                    <button type="submit" name="supprimer" class="supprimer-btn">Supprimer</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<style>
    .objets-liste {
        list-style: none;
        padding: 0;
    }
    .objet-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #f9f9f9;
    }
    .objet-details {
        flex: 1;
    }
    .categorie {
        font-size: 0.9em;
        color: #666;
    }
    .supprimer-form {
        margin-left: 15px;
    }
    .supprimer-btn {
        background-color: #ff4d4d;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 5px;
        cursor: pointer;
    }
    .supprimer-btn:hover {
        background-color: #cc0000;
    }
</style>
</body>
</html>