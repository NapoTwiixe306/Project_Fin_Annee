<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

global $pdo;
require_once __DIR__ . "/inc/config.php";
require_once __DIR__ . "/bdd.php";

// Initialisation de la variable $search si le formulaire est soumis
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Récupérer les objets depuis la base de données avec filtrage basé sur la recherche
$sql = "SELECT objets.id, objets.titre, objets.description, categories.nom AS categorie, 
               brocanteurs.nom AS brocanteur_nom, brocanteurs.prenom AS brocanteur_prenom
        FROM objets
        JOIN categories ON objets.categorie_id = categories.id
        JOIN brocanteurs ON objets.brocanteur_id = brocanteurs.id
        WHERE objets.titre LIKE :search OR categories.nom LIKE :search
        ORDER BY objets.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':search', '%' . $search . '%'); // Recherche partielle dans le titre et la catégorie
$stmt->execute();
$objets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Objets en vente</title>
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body>
<?php include './inc/navbar.inc.php'; ?>

<main>
    <section class="brocanteurs">
        <h2>Objets en vente</h2>

        <section class="form">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Chercher un objet..." value="<?= htmlspecialchars($search) ?>">
                <select name="filter" id="filter">
                    <option value="">Toutes les catégories</option>
                    <?php foreach ($objets as $objet): ?>
                        <option value="<?= htmlspecialchars($objet['categorie']) ?>" <?= (isset($_GET['filter']) && $_GET['filter'] === $objet['categorie']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($objet['categorie']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </section>

        <section class="card_brocanteurs">
            <?php foreach ($objets as $objet): ?>
                <article class="card">
                    <section class="infos">
                        <img src=https://placehold.jp/50x50.png/<?= $objet['brocanteur_nom'][0] ?>" alt="" width="50" height="50"/>
                        <div class="text">
                            <p class="title"><?= htmlspecialchars($objet['brocanteur_prenom'] . " " . $objet['brocanteur_nom']) ?></p>
                            <p><?= htmlspecialchars($objet['categorie']) ?></p>
                        </div>
                    </section>
                    <section class="badge">
                    </section>
                    <section class="description">
                        <h3><?= htmlspecialchars($objet['titre']) ?></h3>
                        <p><?= htmlspecialchars($objet['description']) ?></p>
                    </section>
                    <section class="button">
                        <button>Voir les détails</button>
                    </section>
                </article>
            <?php endforeach; ?>
        </section>
    </section>
</main>

</body>
</html>
