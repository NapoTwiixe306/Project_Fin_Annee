<?php
require_once 'src/autoload.php';
use Controllers\BrocanteurController;

$controller = new BrocanteurController();

// Get brocanteur ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: brocanteurs.php');
    exit();
}

$data = $controller->show($id);

if (!$data) {
    header('Location: brocanteurs.php');
    exit();
}

$brocanteur = $data['brocanteur'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/styles.css">
    <title>FAP - <?= htmlspecialchars($brocanteur['prenom'] . ' ' . $brocanteur['nom']) ?></title>
</head>
<body>
    <header>
        <?php include './inc/navbar.inc.php'; ?>
    </header>
    <main class="main">
        <section class="brocanteur-details">
            <div class="back-button">
                <a href="brocanteurs.php">← Retour aux brocanteurs</a>
            </div>
            
            <div class="brocanteur-header">
                <div class="brocanteur-photo">
                    <?php
                    $photo = !empty($brocanteur['photo_profil']) && file_exists('uploads/' . $brocanteur['photo_profil'])
                        ? 'uploads/' . $brocanteur['photo_profil']
                        : 'https://avatar.iran.liara.run/public/12';
                    ?>
                    <img src="<?= htmlspecialchars($photo) ?>" alt="Photo de profil" class="profile-photo">
                </div>
                
                <div class="brocanteur-info">
                    <h1><?= htmlspecialchars($brocanteur['prenom'] . ' ' . $brocanteur['nom']) ?></h1>
                    
                    <div class="location-info">
                        <?php if (!empty($brocanteur['emplacement'])): ?>
                            <div class="emplacement">
                                <strong>Emplacement:</strong> <?= htmlspecialchars($brocanteur['emplacement']) ?>
                                <?php if (!empty($brocanteur['zone'])): ?>
                                    <span class="zone-badge">Zone <?= htmlspecialchars($brocanteur['zone']) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="emplacement">
                                <strong>Emplacement:</strong> <span class="not-assigned">Pas encore attribué</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($brocanteur['description'])): ?>
                        <div class="description">
                            <h3>Description</h3>
                            <p><?= nl2br(htmlspecialchars($brocanteur['description'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="brocanteur-objects">
                <h2>Objets proposés</h2>
                
                <?php if (!empty($brocanteur['objects'])): ?>
                    <div class="objects-grid">
                        <?php foreach ($brocanteur['objects'] as $object): ?>
                            <div class="object-card">
                                <div class="object-image">
                                    <?php
                                    $objectPhoto = !empty($object['photo_objet']) && file_exists($object['photo_objet'])
                                        ? $object['photo_objet']
                                        : 'https://placehold.co/300x200?text=Aucune+image';
                                    ?>
                                    <img src="<?= htmlspecialchars($objectPhoto) ?>" alt="<?= htmlspecialchars($object['titre']) ?>" class="object-img">
                                </div>
                                <div class="object-info">
                                    <h3><?= htmlspecialchars($object['titre']) ?></h3>
                                    <p class="object-description"><?= htmlspecialchars(substr($object['description'], 0, 100)) ?><?= strlen($object['description']) > 100 ? '...' : '' ?></p>
                                    <?php if (!empty($object['categorie'])): ?>
                                        <span class="category-badge"><?= htmlspecialchars($object['categorie']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-objects">
                        <p>Aucun objet proposé pour le moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <?php include './inc/footer.inc.php'; ?>
</body>
</html>
