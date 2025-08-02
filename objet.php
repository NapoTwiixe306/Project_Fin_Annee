<?php
require_once 'src/autoload.php';
use Controllers\ObjetController;

$controller = new ObjetController();
$data = $controller->index();
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
            <?php if (empty($data['objets'])): ?>
                <div class="no-results" style="text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 8px; margin: 1rem 0;">
                    <h3 style="color: #6c757d; margin-bottom: 1rem;">Aucun objet trouvé</h3>
                    <?php if (!empty($data['filters']['search']) || !empty($data['filters']['filter'])): ?>
                        <p style="color: #6c757d; margin-bottom: 1rem;">
                            Aucun objet ne correspond à vos critères de recherche.
                        </p>
                        <p style="color: #6c757d; font-size: 0.9rem;">
                            Essayez de modifier vos filtres ou de supprimer certains critères.
                        </p>
                        <a href="objet.php" style="display: inline-block; margin-top: 1rem; padding: 0.5rem 1rem; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
                            Voir tous les objets
                        </a>
                    <?php else: ?>
                        <p style="color: #6c757d;">
                            Aucun objet n'est actuellement disponible à la vente.
                        </p>
                        <p style="color: #6c757d; font-size: 0.9rem;">
                            Revenez plus tard pour découvrir de nouveaux objets.
                        </p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
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
            <?php endif; ?>
        </section>

        <!-- MODALS -->
        <?php if (!empty($data['objets'])): ?>
            <?php foreach ($data['objets'] as $objet): ?>
                <div id="objet-<?= $objet['id'] ?>" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2><?= htmlspecialchars($objet['titre']) ?></h2>
                        </div>
                        
                        <div class="modal-body">
                            <div class="objet-info">
                                <div class="objet-image">
                                    <?php
                                    $photo_objet = !empty($objet['photo_objet']) && file_exists($objet['photo_objet'])
                                        ? $objet['photo_objet']
                                        : 'https://placehold.co/400x300?text=Aucune+image';
                                    ?>
                                    <img src="<?= htmlspecialchars($photo_objet) ?>" alt="Photo de l'objet" class="objet-photo"/>
                                </div>
                                
                                <div class="objet-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Catégorie :</span>
                                        <span class="detail-value"><?= htmlspecialchars($objet['categorie']) ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="detail-label">Vendeur :</span>
                                        <span class="detail-value"><?= htmlspecialchars($objet['brocanteur_prenom'] . " " . $objet['brocanteur_nom']) ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="detail-label">Date :</span>
                                        <span class="detail-value"><?= date('d/m/Y', strtotime($objet['created_at'])) ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="objet-description">
                                <h3>Description complète</h3>
                                <p><?= nl2br(htmlspecialchars($objet['description'])) ?></p>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <a href="#" class="close">Fermer</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

</body>
</html>
