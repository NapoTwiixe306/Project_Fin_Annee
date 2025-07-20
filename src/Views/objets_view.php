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
                <input type="text" name="search" placeholder="Chercher un objet..." value="<?= htmlspecialchars($data['filters']['search']) ?>">
                <select name="filter" id="filter">
                    <option value="">Toutes les catégories</option>
                    <?php foreach ($data['categories'] as $category): ?>
                        <option value="<?= htmlspecialchars($category['nom']) ?>" <?= ($data['filters']['filter'] === $category['nom']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Filtrer</button>
            </form>
        </section>

        <section class="card_brocanteurs">
            <?php foreach ($data['objets'] as $objet): ?>
                <article class="card">
                    <section class="infos">
                        <?php
                        $photo = !empty($objet['photo_profil']) && file_exists('uploads/' . $objet['photo_profil'])
                            ? 'uploads/' . $objet['photo_profil']
                            : 'https://avatar.iran.liara.run/public/12';
                        ?>
                        <img src="<?= htmlspecialchars($photo) ?>" alt="Photo de profil" width="50" height="50"/>
                        <div class="text">
                            <p class="title"><?= htmlspecialchars($objet['brocanteur_prenom'] . " " . $objet['brocanteur_nom']) ?></p>
                            <p><?= htmlspecialchars($objet['categorie']) ?></p>
                        </div>
                    </section>
                    <section class="description">
                        <h3><?= htmlspecialchars($objet['titre']) ?></h3>
                        <p><?= htmlspecialchars(substr($objet['description'], 0, 60)) ?>...</p>
                    </section>
                    <section class="button">
                        <a href="#objet-<?= $objet['id'] ?>">Voir les détails</a>
                    </section>
                </article>
            <?php endforeach; ?>
        </section>

        <!-- MODALS -->
        <?php foreach ($data['objets'] as $objet): ?>
            <div id="objet-<?= $objet['id'] ?>" class="modal">
                <div class="modal-content">
                    <h2><?= htmlspecialchars($objet['titre']) ?></h2>
                    <p><strong>Catégorie :</strong> <?= htmlspecialchars($objet['categorie']) ?></p>
                    <p><strong>Vendeur :</strong> <?= htmlspecialchars($objet['brocanteur_prenom'] . " " . $objet['brocanteur_nom']) ?></p>
                    <p><strong>Description complète :</strong></p>
                    <p><?= nl2br(htmlspecialchars($objet['description'])) ?></p>

                    <?php
                    $photo_objet = !empty($objet['photo_objet']) && file_exists('uploads/' . $objet['photo_objet'])
                        ? 'uploads/' . $objet['photo_objet']
                        : 'https://placehold.jp/300x300.png';
                    ?>
                    <img src="<?= htmlspecialchars($photo_objet) ?>" alt="Photo de l'objet" width="300" height="300"/>

                    <a href="#" class="close">Fermer</a>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
</main>

</body>
</html>
